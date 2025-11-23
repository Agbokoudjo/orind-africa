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

namespace App\UI\Http\Controller\Admin;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Enum\AccountStatus;
use App\Domain\User\Model\BaseUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Exception\InvalidTokenException;
use App\Domain\User\Message\UpdatePasswordUserCommand;
use App\Application\Queue\AsyncMethodDispatcherInterface;
use App\Application\UseCase\User\ToggleUserAccountUseCase;
use App\Domain\User\Exception\UserManagerNotFoundException;
use App\Application\Service\GenerateTemporaryPasswordService ;
use App\Domain\User\Service\Security\EmailVerificationInterface;
use App\Application\UseCase\User\UpdatePasswordUserCommandHandler;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class WlindablaAdminCRUDController extends CRUDController {

    /**
     * Active ou désactive un compte utilisateur.
     * 
     * Le UserType est automatiquement résolu et décodé par UserTypeArgumentValueResolver.
     * Pas besoin de le décoder manuellement.
     *
     * @throws BadHttpRequestException Si les données ne sont pas valides
     */
    public function toggleEnabledUserAccountAction(
        Request $request,
        string $slug,
        UserType $user_type,
        UserManagerRegistryInterface $userManagerRegistry,
        AsyncMethodDispatcherInterface $dispatchMessage
    ): JsonResponse {
        
        if (!$this->isXmlHttpRequest($request) || !$request->isMethod('PATCH')) {
            return $this->createFlashJsonResponse(
                type:"danger",
                title: 'Error',
                message: '⚠️ Accès non autorisé. Veuillez réessayer.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        try {
            // Validation du slug
            if (!$slug || empty(trim($slug))) {
                throw new BadRequestHttpException('🔴 Le slug de l\'utilisateur est manquant.');
            }

            $userManager = $userManagerRegistry->getByUserType($user_type);

            /** @var BaseUserInterface|null */
            $user = $userManager->findUserBySlug($slug);

            if (!$user instanceof BaseUserInterface) {
                return $this->createFlashJsonResponse(
                    type:"warning",
                    title: 'Warning',
                    message: sprintf('❌ Aucun utilisateur trouvé avec le slug "%s".', $slug),
                    statusCode: Response::HTTP_NOT_FOUND
                );
            }

            if (!$user->isEmailVerified()) {
                return $this->json([
                        'title'=>'Action Bloquée : Email Non Vérifié',
                        'message'=>\sprintf(
                            'L\'adresse Email "%s" associée au compte "%s" n\'a pas été vérifiée. Cette action est donc bloquée. Veuillez cliquer sur le bouton "Renvoyer l\'email de vérification" pour procéder.',
                            $user->getEmail(),
                            $user->getUsername() 
                        )
                        ],
                    Response::HTTP_FORBIDDEN
                );
            }
            
            // Récupérer et valider le nouveau statut
            $isEnabled = $this->getStatusFromRequest($request);
            $accountStatus = $isEnabled ? AccountStatus::ACTIVE : AccountStatus::INACTIVE;
           
            $user->setEnabled($isEnabled);

            $this->dispatchToggleUserAccountTask($user, $user_type, $accountStatus, $dispatchMessage);

            $message = $this->buildSuccessMessage($user, $isEnabled);

            return $this->createFlashJsonResponse(
                 type:"success",
                title: 'Opération réussie',
                message: $message,
                statusCode: Response::HTTP_OK
            );
        } catch (BadRequestHttpException $e) {
            throw $e;
        }catch (UserManagerNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->createFlashJsonResponse(
                type:"danger",
                title: 'Error',
                message: '🔴 Une erreur est survenue lors de la mise à jour du statut.Veuillez actualiser et réessayer.',
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function resendEmailVerifyAction(
        Request $request,
        string $slug,
        UserType $user_type,
        EmailVerificationInterface $verificationService
    ):JsonResponse{

        if (!$this->isXmlHttpRequest($request) || !$request->isMethod('GET')) {
            return $this->createFlashJsonResponse(
                type: "danger",
                title: 'Error',
                message: '⚠️ Accès non autorisé. Veuillez réessayer.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        try{
            // Validation du slug
            if (!$slug || empty(trim($slug))) {
                throw new BadRequestHttpException('🔴 Le slug de l\'utilisateur est manquant.');
            }

            $verificationService->resendVerificationEmail($slug, $user_type);
            $type = "success";
            $title = "Email de Vérification Envoyé !";
            $message = \sprintf(
                "Le lien de vérification a été renvoyé avec succès. Veuillez informer l'utilisateur de consulter sa boîte de réception et ses spams pour confirmer son compte.",
            );
            $statusHttp = Response::HTTP_OK;
        }catch(BadRequestHttpException |InvalidTokenException |\RuntimeException|\Exception $e){
            $type = "danger";
            $title = "Échec de l'Envoi"; 
            $message = $e->getMessage();
            $statusHttp = Response::HTTP_BAD_REQUEST;
        }

        return $this->createFlashJsonResponse(
            type: $type,
            title: $title,
            message: $message,
            statusCode: $statusHttp
        );
    }

    public function regenerateTemporyPasswordUserAction(
        Request $request,
        int|string $id,
        UserType $user_type,
        GenerateTemporaryPasswordService $generateTemporatyPasswordService,
        AsyncMethodDispatcherInterface $dispatchMessageUpdatePassword
       ):JsonResponse{

        if (!$this->isXmlHttpRequest($request) || !$request->isMethod('PATCH')) {
            return $this->createFlashJsonResponse(
                type: "danger",
                title: 'Error',
                message: '⚠️ Requête non autorisée. Veuillez utiliser le canal PATCH/AJAX approprié.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

         // Validation du id
        if (!$id || empty(trim($id))) {
            return $this->createFlashJsonResponse(
                type: "error",
                title: 'Donnée Manquante',
                message: '🔴 L\'identifiant utilisateur (ID) est requis et ne peut être vide.',
                statusCode: Response::HTTP_BAD_REQUEST
            );
        }

        try {
           $plainTemporyPasswordUser=$generateTemporatyPasswordService->generateTemporaryPassword() ;
           $commandUpdatePasssword=new UpdatePasswordUserCommand($id,$user_type,$plainTemporyPasswordUser) ;
           $dispatchMessageUpdatePassword->dispatch(
                UpdatePasswordUserCommandHandler::class,
                'handle',
                [$commandUpdatePasssword]
            );
            $type = "success";
            $title = "✅ Mot de Passe Temporaire Généré et Envoyé !";
            $message = "Un nouveau mot de passe temporaire a été généré avec succès. Une notification a été immédiatement envoyée à la boîte e-mail de l'utilisateur pour qu'il puisse se connecter et le modifier.";
            $statusHttp = Response::HTTP_RESET_CONTENT;
        } catch (\Exception $e) {
            $type = "danger";
            $title = "❌ Échec de la Réinitialisation";
            $message = "Une erreur est survenue lors du processus de régeneration du mot de passe : " . $e->getMessage();
            $statusHttp = Response::HTTP_BAD_REQUEST;
        }

        return $this->createFlashJsonResponse(
            type: $type,
            title: $title,
            message: $message,
            statusCode: $statusHttp
        );
    }

    /**
     * Construit le message de succès percutant
     */
    private function buildSuccessMessage(BaseUserInterface $user, bool $isEnabled): string
    {
        $action = $isEnabled
            ? '✅ Compte activé avec succès'
            : '🔒 Compte désactivé avec succès';

        return sprintf(
            '%s - %s (ID: %s)',
            $action,
            $user->getUsername(),
            $user->getId()
        );
    }

    /**
     * Dispatch la tâche async de basculement de compte
     */
    private function dispatchToggleUserAccountTask(
        BaseUserInterface $user,
        UserType $userType,
        AccountStatus $accountStatus,
        AsyncMethodDispatcherInterface $dispatchMessage
    ): void {
        $dispatchMessage->dispatch(
            ToggleUserAccountUseCase::class,
            'handle',
            [$userType, $user->getEmail(), $accountStatus]
        );
    } 

    /**
     * Crée une réponse JSON avec flash message
     */
    final protected function createFlashJsonResponse(
        string  $type,
        string $title,
        string $message,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {

        $this->addFlash($type, $message);
        
        return $this->json(
            [
                'title' => $title,
                'type' => $type,
                'message' => $this->renderView('bundles/SonataTwigBundle/FlashMessage/render.html.twig'),
            ],
            $statusCode
        );
    }

    /**
     * Récupère et valide le statut de la requête
     * 
     * @throws BadHttpRequestException Si le statut est invalide
     */
    private function getStatusFromRequest(Request $request): bool{

        $status=$request->getPayload()->getBoolean('status');

        if ($status === null) {
            throw new BadRequestHttpException('🔴 Le paramètre "status" est manquant.');
        }

        if (is_bool($status)) { return $status;}

        if (is_string($status)) {
            $status = strtolower($status);

            if (in_array($status, ['true', '1', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($status, ['false', '0', 'off', 'no'], true)) {
                return false;
            }

        }

        throw new BadRequestHttpException(
            sprintf('🔴 Statut invalide : "%s". Utiliser "true" ou "false".', $status)
        );
    }
}
