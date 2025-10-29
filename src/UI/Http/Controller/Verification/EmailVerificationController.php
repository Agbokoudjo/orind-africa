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

namespace App\UI\Http\Controller\Verification;

use Psr\Log\LoggerInterface;
use App\Domain\User\Enum\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Domain\User\Exception\InvalidTokenException;
use App\Infrastructure\Service\UserAccountRouteResolver;
use App\Domain\User\Service\Security\EmailVerificationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[Route(
    '/verify-email/{token}/{slug}/{user_type}',
    name: 'app_verify_email',
    requirements: [
        // Le type utilisateur est encodé en Base64 (lettres, chiffres, +, / et =)
        'user_type' => '[a-zA-Z0-9+/]+={0,2}',
        'token' => '[a-zA-Z0-9]{32,64}',
        'slug' => '[a-f0-9]{32,40}',
    ],
    methods: ['GET']
)]
final class EmailVerificationController extends AbstractController
{
    public function __construct(
        private EmailVerificationInterface $verificationService,
        private readonly ?LoggerInterface $logger = null
    ) {}

    public function __invoke(
        string $token,
        string $slug,
        UserType $userTypeEnum
    ):Response{
        $page_redirection_route_name="app.home";
        try {
            $page_redirection_route_name = UserAccountRouteResolver::resolveLoginRouteNameByType($userTypeEnum);


            $this->verificationService->verifyEmail($token, $slug, $userTypeEnum);
            $this->addFlash('success', '✅ Votre email a été vérifié avec succès ! Vous pouvez maintenant vous connecter.');

            return $this->render('/email/security/checkEmail.html.twig', [
                'page_login_url' => $page_redirection_route_name
            ]);
            
        } catch (InvalidTokenException $e) {
            return $this->handleInvalidToken($e, $slug, $userTypeEnum, $page_redirection_route_name);

        } catch (\Exception $e) {
            return $this->handleUnexpectedError($e);
        }

        return $this->redirectToRoute($page_redirection_route_name,[],Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Gère les différents cas d'erreur de token.
     *
     * @param InvalidTokenException $exception L'exception levée
     * @param string $slug Le slug de l'utilisateur
     * @param  UserType $userType Le type d'utilisateur
     * @param   string  $page_redirection_route_name
     * @return Response La route de redirection
     */
    private function handleInvalidToken(
        InvalidTokenException $e,
        string $slug,
        UserType $userType,
        string  $page_redirection_route_name
    ): Response{

        if ($e->isExpired()) {

            $this->addFlash(
                'warning',
                '⏰ Le lien de vérification a expiré. Un nouveau lien vous a été envoyé.'
            );

            // Renvoyer un email 
            $this->verificationService->resendVerificationEmail($slug, $userType);

        } elseif ($e->isAlreadyUsed()) {
            $this->addFlash(
                'info',
                'Votre email est déjà vérifié ! Vous pouvez vous connecter directement.'
            );

            return $this->render('/email/security/checkEmail.html.twig', [
                'page_login_url' => $page_redirection_route_name
            ]);

        } elseif ($e->isUserNotFound()) {

            $this->addFlash(
                'error',
                '❌' . $e->getMessage()
            );
        } else {
            $this->addFlash(
                'error',
                '❌ Le lien de vérification est invalide. Veuillez réessayer.'
            );
        }

        return $this->render('/email/security/checkEmail.html.twig', [
            'page_redirection_url' => 'app.home',
            'page_label' => 'Aller a la page d\'accueill'
        ]);
    }

    /**
     * Gère les erreurs inattendues.
     *
     * @param \Exception $exception L'exception levée
     * 
     * @return Response
     */
    private function handleUnexpectedError(\Exception $exception): Response
    {
        $this->logger?->critical('Erreur inattendue lors de la vérification d\'email', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->addFlash(
            'error',
            '⚠️ Une erreur est survenue. Veuillez réessayer plus tard ou contacter le support.'
        );

        return $this->render('/email/security/checkEmail.html.twig', [
            'page_redirection_url' => 'app.home',
            'page_label' => 'Aller a la page d\'accueill'
        ]);
    }

    /**
     * Gère le cas d'un type d'utilisateur invalide.
     *
     * @param string $invalidType Le type invalide fourni
     * @param \ValueError $exception L'exception levée
     * 
     * @return void
     */
    private function handleInvalidUserType(string $invalidType, \ValueError $exception): void
    {
        $this->logger?->error('Type d\'utilisateur invalide détecté', [
            'invalid_type' => $invalidType,
            'valid_types' => $this->getValidUserTypes(),
            'ip' => $this->getClientIp(),
            'error' => $exception->getMessage(),
        ]);

        $this->addFlash(
            'error',
            '❌ Le lien de vérification est invalide ou a été modifié. Veuillez demander un nouveau lien.'
        );
    }


    /**
     * Obtient la liste des types d'utilisateurs valides.
     *
     * @return array<string> Les valeurs valides
     */
    private function getValidUserTypes(): array
    {
        return array_map(
            fn(UserType $type) => $type->value,
            UserType::cases()
        );
    }

    /**
     * Obtient l'adresse IP du client.
     *
     * @return string L'IP du client
     */
    private function getClientIp(): string
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $request?->getClientIp() ?? 'unknown';
    }
}
