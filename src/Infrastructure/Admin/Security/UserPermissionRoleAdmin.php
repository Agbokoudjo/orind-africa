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

namespace App\Infrastructure\Admin\Security;

use App\Domain\User\Enum\UserType;
use Sonata\AdminBundle\Form\FormMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Infrastructure\Doctrine\Entity\Security\PermissionRoleEntity;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use App\UI\Http\Controller\Admin\UserPermissionRoleAdminCRUDController;
use App\Infrastructure\Doctrine\Entity\Security\UserPermissionRoleEntity;
use App\Infrastructure\Security\Handler\UserPermissionRoleSecurityHandler;
use App\Infrastructure\Doctrine\Entity\User\Repository\AdminUserRepository;
use App\Infrastructure\Doctrine\Entity\User\Repository\MemberUserRepository;

/**
 * @inheritDoc Association dynamique : quel utilisateur (Admin, Ministre, Membre…) possède quel rôle.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.user.permission.role',
        'code' => "sonata.admin.user.permission.role",
        'admin_code' => "sonata.admin.user.permission.role",
        'model_class' => UserPermissionRoleEntity::class,
        'manager_type' => 'orm',
        'controller' => UserPermissionRoleAdminCRUDController::class,
        'group' => 'app.admin.group.permission.manager',
        'group_code' => 'app.admin.group.permission.role.manager',
        'label' => 'sonata.admin.user.permission.role',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_ADMIN'] ,
        'security_handler' => UserPermissionRoleSecurityHandler::class
    ]
)]
final class UserPermissionRoleAdmin extends WlindablaAdmin
{
    public function __construct(
        private readonly AdminUserRepository $adminUserRepository,
        private readonly MemberUserRepository $memberUserRepository)
    {
        parent::__construct('permission.user',null, 'permission.user');
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_user_permission_role';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'user_permission_role';
    }
    public function configureFormFields(FormMapper $form): void
    {
        $form
            ->with(
                'user_permission',
                [
                    'label' => false,
                    'class' => 'col-md-6',
                'translation_domain' => 'Permission',
                'box_class'   => 'box box-solid box-solid-with'
                ]
            )
            ->ifTrue($this->isGranted('ROLE_FOUNDER'))
                ->add('userId', ChoiceType::class, [
                    'choices' =>[
                     'ADMIN' => $this->adminUserRepository->findByUserWithExcludRole(['ROLE_FOUNDER']),
                    'MEMBER'=>$this->memberUserRepository->getMemberUserChoices()
                    ],
                    'choice_translation_domain'=>'user',
                    'translation_domain' => 'Permission',
                    'label' => 'forms.user_permission_user_list',
                    'placeholder' => 'forms.user_permission_user_list_placeholder',
                    'attr' => [
                        'data-user_permission_role-target' => 'userSelect',
                    ]
                ])
                ->add('userType', EnumType::class, [
                    'class' => UserType::class,
                    'expanded'=>true,
                    'choice_translation_domain' => 'user',
                    'label' => 'forms.user_permission_user_type',
                    'label_attr' => ['class' => 'mt-3'],
                    'attr' => [
                        'data-user_permission_role-target' => 'userTypeSelect',
                        'class' => 'd-flex flex-row'
                    ]
                ])
            ->ifEnd()
            ->ifTrue($this->isGranted('ROLE_MINISTER'))
                ->add('userId', ChoiceType::class, [
                    'choices' => $this->memberUserRepository->getMemberUserChoices(),
                    'choice_translation_domain' => 'user',
                    'translation_domain' => 'Permission',
                    'label' => 'forms.user_permission_user_list',
                    'placeholder' => 'forms.user_permission_user_list_placeholder',
                    'attr' => [
                        'data-user_permission_role-target' => 'userSelect'
                    ]
                ])
                ->add('userType', EnumType::class, [
                    'class' => UserType::class,
                    'choice_translation_domain' => 'user',
                    'empty_data'=> UserType::MEMBER,
                    'label' => 'forms.user_permission_user_type',
                    'disabled'=>true,
                    'data'=> UserType::MEMBER,
                    'label_attr' => ['class' => 'mt-3'],
                    'row_attr' => ['class' => 'd-none'],
                    'attr' => [
                        'data-user_permission_role-target' => 'userTypeSelect',
                        'class'=>'d-none'
                    ]
                ])
            ->ifEnd()
            ->end()
            ->with('roles',[
                    'label' => 'forms.user_permission_role',
                    'class' => 'col-md-6',
                     'translation_domain' => 'Permission',
                    'box_class'   => 'box box-solid box-solid-with',
                ])
                ->add('roles', EntityType::class, [
                        'class' => PermissionRoleEntity::class,
                        'choice_label' => 'name',
                        'translation_domain' => 'Permission',
                        'label' => false,
                        'attr' => [
                            'placeholder' => 'forms.user_permission_role_placeholder',
                        ]
                    ])
                    ->add('scope', TextType::class, [
                        'required' => false,
                        'label' => 'forms.user_permission_scope',
                        'label_attr' => ['class' => 'mt-3'],
                        'translation_domain' => 'Permission',
                        'attr' => [
                            'placeholder' => 'forms.user_permission_scope_placeholder',
                        ]
                    ])
            ->end()
            ;
    }

}

