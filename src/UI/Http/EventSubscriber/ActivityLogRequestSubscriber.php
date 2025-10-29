<?php

declare(strict_types=1);
/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\UI\Http\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Domain\Log\Enum\ActivityAction;
use App\Domain\User\Model\BaseUserInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Log\Command\ActivityLogCommand;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Application\Security\SecurityContextInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use App\Application\Queue\AsyncMethodDispatcherInterface;
use App\Domain\User\Exception\UnresolvableUserTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Application\UseCase\CommandHandler\ActivityLogCommandHandler;

/**
 * Subscriber pour l'enregistrement automatique des activités utilisateurs.
 * 
 * Capture toutes les requêtes HTTP et enregistre :
 * - Les actions utilisateur (consultation, création, modification, suppression)
 * - Le contexte de la requête (IP, user agent, route, etc.)
 * - Les informations utilisateur (si authentifié)
 * 
 * Le traitement est asynchrone (via queue) pour ne pas impacter les performances.
 * Utilise KernelEvents::TERMINATE pour s'exécuter après l'envoi de la réponse.
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
final class ActivityLogRequestSubscriber implements EventSubscriberInterface
{
    /**
     * Routes à ignorer pour le logging (debug, healthcheck, assets, etc.)
     */
    private const IGNORED_ROUTE_PREFIXES = [
        '_',           // Routes Symfony internes
        'healthcheck', // Healthcheck
        'api_doc',     // Documentation API
        '_wdt',        // Web Debug Toolbar
        '_profiler',   // Profiler
    ];

    public function __construct(
        private readonly AsyncMethodDispatcherInterface $enqueue,
        private readonly SecurityContextInterface $security,
        private readonly ?LoggerInterface $logger = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité 0 = exécuté après la réponse envoyée
            KernelEvents::TERMINATE => ['onKernelTerminate', 0],
        ];
    }

    /**
     * Enregistre l'activité utilisateur après l'envoi de la réponse.
     * 
     * S'exécute de manière asynchrone pour ne pas ralentir la réponse.
     *
     * @param TerminateEvent $event L'événement de terminaison de requête
     * 
     * @return void
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route') ?? 'N/A';

        // 2. Ignorer les routes système et debug
        if ($this->shouldIgnoreRoute($route)) {
            return;
        }

        try {
            // 3. Extraire le contexte utilisateur
            $userContext = $this->extractUserContext();
            // 4. Déterminer l'action selon la méthode HTTP et la route
            $action = $this->determineAction($request);
            // 5. Construire la commande de log
            $activityCommand = new ActivityLogCommand(
                userContext: $userContext,
                ipAddress: $request->getClientIp() ?? 'unknown',
                action: $action,
                route: $route,
                method: $request->getMethod(),
                context: [
                    'uri' => $request->getUri(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'is_ajax' => $request->isXmlHttpRequest(),
                    'route_params' => $request->attributes->get('_route_params', []),
                    'referer' => $request->headers->get('Referer'),
                    'status_code' => $event->getResponse()->getStatusCode(),
                    'request_data' => $this->sanitizeRequestData($request),
                ]
            );

            // 6. Envoyer dans la queue pour traitement asynchrone
            $this->enqueue->dispatch(
                ActivityLogCommandHandler::class,
                'handle',
                [$activityCommand]
            );

        } catch (\Exception $e) {
            // Ne jamais faire crasher l'application à cause du logging
            $this->logger?->error('Échec de l\'enregistrement d\'activité', [
                'error' => $e->getMessage(),
                'route' => $route,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Vérifie si une route doit être ignorée pour le logging.
     *
     * @param string $route Le nom de la route
     * 
     * @return bool True si la route doit être ignorée
     */
    private function shouldIgnoreRoute(string $route): bool
    {
        foreach (self::IGNORED_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une route est une route de connexion.
     *
     * @param string $route Le nom de la route
     * 
     * @return bool True si c'est une route de login
     */
    private function isLoginRoute(string $route): bool
    {
        return str_contains($route, 'login')
            || str_contains($route, 'signin')
            || str_contains($route, 'authenticate');
    }

    /**
     * Détermine l'action effectuée selon la méthode HTTP et le contexte.
     *
     * @param Request $request La requête HTTP
     * 
     * @return ActivityAction L'action correspondante
     */
    private function determineAction(Request $request): ActivityAction
    {
        $method = $request->getMethod();
        $route = $request->attributes->get('_route') ?? '';

        // Détection basée sur la méthode HTTP
        return match ($method) {
            'GET' => ActivityAction::PAGE_VIEW,
            'POST' => $this->isLoginRoute($route) ? ActivityAction::LOGIN : ActivityAction::ENTITY_CREATE,
            'PUT', 'PATCH' => ActivityAction::ENTITY_UPDATE,
            'DELETE' => ActivityAction::ENTITY_DELETE,
            default => ActivityAction::OTHER,
        };
    }

    /**
     * Extrait le contexte de l'utilisateur connecté.
     *
     * @return array<string, mixed> Les informations utilisateur
     */
    private function extractUserContext(): array
    {
        $user = $this->security->getCurrentUser();

        // Utilisateur non authentifié
        if (!$user instanceof BaseUserInterface) {
            return [
                'id' => null,
                'email' => 'anonymous',
                'username' => 'anonymous',
                'role' => 'ROLE_ANONYMOUS',
                'type' => 'guest',
            ];
        }

        // Utilisateur authentifié
        try {
            $userType = UserTypeResolver::resolveFromUser($user);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => $userType->value,
            ];
        } catch (UnresolvableUserTypeException $e) {
            $this->logger?->warning('Impossible de résoudre le type utilisateur pour le log', [
                'user_class' => $user::class,
                'error' => $e->getMessage(),
            ]);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => 'unknown',
            ];
        }
    }

    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->request->all();

        // Supprimer les champs sensibles
        $sensitiveFields = ['password', 'plainPassword', 'token', 'credit_card'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}