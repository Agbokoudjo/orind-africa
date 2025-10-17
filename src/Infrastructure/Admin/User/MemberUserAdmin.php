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

namespace App\Infrastructure\Admin\User;

use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Infrastructure\Admin\User\AbstractUserAdmin;
use App\Infrastructure\Persistance\MemberUserManager;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use App\Infrastructure\Doctrine\Entity\User\Repository\MemberUserRepository;
use App\UI\Http\Controller\Admin\User\MemberUserCRUDController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use App\Infrastructure\Security\Handler\MemberUserRoleSecurityHandler;
use App\Infrastructure\Security\Voter\UserProfileEditVoter;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.user.member',
        'code' => "sonata.admin.user.member",
        'admin_code' => "sonata.admin.user.member",
        'model_class' => MemberUser::class,
        'manager_type' => 'orm',
        'controller' => MemberUserCRUDController::class,
        'group' => 'app.admin.group.user',
        'group_code' => 'app.admin.group.user',
        'label' => 'sonata.admin.user.member',
        'label' => 'sonata.admin.user.member',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_ADMIN','ROLE_MEMBER'],
        'security_handler' => MemberUserRoleSecurityHandler::class
    ]
)]
final class MemberUserAdmin extends AbstractUserAdmin
{
    public function __construct(
        private readonly Security $securityObject,
        private readonly MemberUserManager $memberUserManager,
        private readonly MemberUserRepository $memberRepository
    ) {
        parent::__construct("label_list_member_user", null, 'label_create_member_user');
    }

    protected function postPersist(object $object): void
    {
        if(!$object instanceof MemberUser){
            return  ;
        }
        $this->memberRepository->invalidateTags();
    }

    protected function postUpdate(object $object): void
    {
        if (!$object instanceof MemberUser) {
            return;
        }
        $this->memberRepository->invalidateTags();
    }

    protected function configureFormFields(FormMapper $form): void
    {

        if ($this->isFormEdit()) {
            
            if($this->isGrantedUser('ROLE_FOUNDER') || 
              $this->isGrantedUser(UserProfileEditVoter::PERMISSION_CAN_EDIT_OWN_PROFILE)){

                $this->configureFormFieldsParent($form);
            }
                  
        } else {
            $this->configureFormFieldsParent($form);
        }
    }
    
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_member_user';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'member_user';
    }

    protected function getSymfonySecurity(): Security
    {

        return $this->securityObject;
    }

    protected function getModelUserManager(): EntityManagerInterface
    {
        return $this->memberUserManager->getEntityManager();
    }

    private function professionalCursus(FormMapper $form):void{

        
    }
}
