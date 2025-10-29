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
use App\Infrastructure\Persistance\AdminUserManager;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Security\Voter\UserProfileEditVoter;
use App\UI\Http\Controller\Admin\User\AdminUserCRUDController;
use App\Infrastructure\Security\Handler\AdminUserRoleSecurityHandler;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.user.admin',
        'code'=> "sonata.admin.user.admin",
        'admin_code' => "sonata.admin.user.admin",
        'model_class' => AdminUser::class,
        'manager_type' => 'orm',
        'controller' => AdminUserCRUDController::class,
        'group' => 'app.admin.group.user',
        'group_code' => 'app.admin.group.user',
        'label' => 'sonata.admin.user.admin',
        'label' => 'sonata.admin.user.admin',
         'translation_domain'=> 'Admin',
        'show_in_roles_matrix'=>true,
        'roles'=>['ROLE_ADMIN'],
        'security_handler'=>AdminUserRoleSecurityHandler::class
    ]
)]
final class AdminUserAdmin extends AbstractUserAdmin
{
    public function __construct(
        private readonly Security $securityObject,
        private readonly AdminUserManager $adminUserManager)
    {
        parent::__construct("label_list_admin_user",null, 'label_create_admin_user');
    }

    protected function configureFormFields(FormMapper $form): void
    {

        if ($this->isFormEdit()) {

            if (
                $this->isGrantedUser('ROLE_FOUNDER') ||
                $this->isGrantedUser(UserProfileEditVoter::PERMISSION_CAN_EDIT_OWN_PROFILE)
            ) {

                $this->configureFormFieldsParent($form);
            }
        } else {
            $this->configureFormFieldsParent($form);
        }
    }
    
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_user';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'admin_user';
    }

    protected function getSymfonySecurity(): Security{

        return $this->securityObject ;
    }

    protected function getModelUserManager(): EntityManagerInterface
    {
        return $this->adminUserManager->getEntityManager();
    }
}
