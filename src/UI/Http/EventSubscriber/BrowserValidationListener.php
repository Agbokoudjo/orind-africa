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

use App\Infrastructure\Security\BrowserValidation\BrowserRequestValidator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

/**
 * Event Listener pour valider que les requêtes proviennent d'un navigateur
 * 
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 1000)]
final readonly class BrowserValidationListener
{
    public function __construct(
        private BrowserRequestValidator $validator, 
        private ?LoggerInterface $logger = null
    ) {}


    /**
     * Intercepte les requêtes et valide qu'elles proviennent d'un navigateur
     */
    public function __invoke(RequestEvent $event): void
    {
        // 1. Ignorer les requêtes non-HTTP (console, tests, etc)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        // 2. Ne valider que les requêtes vers l'API ou routes spécifiques
        if (!$this->shouldValidatePath($request->getPathInfo())) {
            return;
        }

        // 3. Valider que c'est une requête de navigateur
        if (!$this->validator->isValidBrowserRequest($request)) {
            $reason = $this->validator->getBlockReasonMessage($request);
            // Logger l'incident
            $this->logBlockedRequest($request, $reason);

            // Retourner une réponse JSON 403
            $event->setResponse(
                new JsonResponse(
                    [
                        'status' => 'error',
                        'code' => Response::HTTP_FORBIDDEN,
                        'message' => $reason,
                        'timestamp' => date('Y-m-d H:i:s'),
                    ],
                    Response::HTTP_NOT_EXTENDED
                )
            );
        }
    }

    /**
     * Détermine si le chemin doit être validé
     * 
     * Retourne true pour les routes API, admin, etc.
     * Retourne false pour les assets, les webhooks, etc.
     */
    private function shouldValidatePath(string $path): bool
    {
        // Ignorer les chemins à exclure
        $excludedPaths = [
            '/health',
            '/webhook',
            '/payment/callback',
            '/bundles/',
            '/assets/',
            '/.well-known/',
        ];

        foreach ($excludedPaths as $excludedPath) {
            if (str_starts_with($path, $excludedPath)) {
                return false;
            }
        }

        // Valider les chemins sensibles
        $sensitivePatterns = [
            '/admin',
            '/api',
            '/dashboard',
            '/toggle_enabled_user_account',
            '/user',
        ];

        foreach ($sensitivePatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        // Par défaut, ne pas valider
        return false;
    }

    /**
     * Log les requêtes bloquées
     */
    private function logBlockedRequest(mixed $request, string $reason): void
    {
        $userAgent = $request->headers->get('User-Agent', 'Non fourni');
        $ip = $request->getClientIp();

        $this->logger?->warning(
            'Requête non-navigateur bloquée',
            [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'method' => $request->getMethod(),
                'path' => $request->getPathInfo(),
                'reason' => $reason,
            ]
        );
    }
}