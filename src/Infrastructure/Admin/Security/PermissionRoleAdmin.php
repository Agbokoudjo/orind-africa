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

use App\Domain\Security\ReservedRoles;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use App\Infrastructure\Validator\NotReservedRole;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use App\UI\Http\Controller\Admin\PermissionRoleAdminCRUDController ;
use App\Infrastructure\Doctrine\Entity\Security\PermissionRoleEntity;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use App\Infrastructure\Security\Handler\PermissionRoleSecurityHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 * @inheritDoc Définition des rôles disponibles (ex : « Manager de projet », « Modérateur »). 
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.permission.role',
        'code' => "sonata.admin.permission.role",
        'admin_code' => "sonata.admin.permission.role",
        'model_class' => PermissionRoleEntity::class,
        'manager_type' => 'orm',
        'controller' => PermissionRoleAdminCRUDController::class,
        'group' => 'app.admin.group.permission.manager',
        'group_code' => 'app.admin.group.permission.role.manager',
        'label' => 'sonata.admin.permission.role',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_ADMIN'],
        'security_handler'=>PermissionRoleSecurityHandler::class
    ]
)]
final class PermissionRoleAdmin extends WlindablaAdmin {

    public function __construct(private TokenStorageInterface $tokenStorage)
    {
        parent::__construct('permission.role',null, 'permission.role');
    }

    // protected function preValidate(object $object):void{
     
    //     if (!$object instanceof PermissionRoleEntity) {
    //         return;
    //     }
    //     $roleName = strtoupper($object->getName());
    //     if (in_array($roleName, ReservedRoles::RESERVED_ROLE_USER, true)) {

    //         throw new ModelManagerException(
    //             $this->getTranslator()->trans('reservedRoles',['{{ value }}' => $roleName], 'validation_errors'),
    //             422
    //         );
    //     }
    // }

    protected function prePersist(object $object): void {

        if (!$object instanceof PermissionRoleEntity) {
            return;
        }

        if($token =$this->tokenStorage->getToken()){

            $object->setCreatedBy($token->getUser());
        }
        
    }
    
    public function configureListFields(ListMapper  $list):void{
        
        $list
            ->add('name',FieldDescriptionInterface::TYPE_STRING,[
                  'label' => 'list.permission_role_name',
                  'translate_domain' => 'Permission'
            ])
            ->add('context', FieldDescriptionInterface::TYPE_STRING, [
                 'label' => 'list.permission_role_context',
                 'translate_domain' => 'Permission'
             ])
            ->add('description', FieldDescriptionInterface::TYPE_TEXTAREA, [
                'label' => 'list.permission_role_description',
                'translate_domain' => 'Permission'
            ])
            ;
    }
    public function configureFormFields(FormMapper $form):void{

        $form
            ->with('inform',
                    [   'label'=>'permission_role_general',
                        'class'=>'col-md-5',
                        'box_class'   => 'box box-solid box-solid-with',
                        'translation_domain'=>'Permission'])
                ->add('name',TextType::class ,[
                        'label'=>'forms.permission_role_name',
                        'label_attr' => ['class' => 'form-label'],
                        'attr'=>[
                            'placeholder' => 'forms.permission_role_name_placeholder',
                            'autocomplete' => 'on', // corrigé
                            'minlength' => 3,       // corrigé
                            'maxlength' => 100,     // corrigé
                            'data-pattern' => '^[A-Z_]+$',
                            'pattern' => '^[A-Z_]+$',
                            'data-eg-await' => 'PROJECT_MANAGER',
                            'data-escapestrip-html-and-php-tags' => 'true',
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            'data-input-reserved-roles-validate'=>'true',
                            'data-reserved-roles'=> json_encode(ReservedRoles::RESERVED_ROLE_USER) ,
                            'data-error-message-input'=> 'Le nom de rôle que vous avez saisi est réservé et ne peut pas être créé.',
                        ],
                        'translation_domain' => 'Permission',
                        'constraints'=>[
                            new NotReservedRole()
                        ]
                ],[])
                ->add('context', TextType::class, [
                        'label' => 'forms.permission_role_context',
                        'label_attr' => ['class' => 'form-label'],
                        'row_attr' => ['class' => 'mt-3'],
                        'attr'=>[
                             'placeholder' => 'forms.permission_role_context_placeholder',
                            'autocomplete' => 'on', // corrigé
                            'minlength' => 3,       // corrigé
                            'maxlength' => 200,     // corrigé
                            'data-pattern' => '^[\p{L}\p{M}\s\']+$',
                            'data-eg-await' => 'Domaine = UI/UX Design',
                            'data-escapestrip-html-and-php-tags' => 'true',
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                        ],
                        'translation_domain' => 'Permission'
                ], [])
            ->end()
            ->with(
                'Description',
                [
                    'label' => 'permission_role_description_general',
                    'class' => 'col-md-7',
                    'translation_domain' => 'Permission',
                    'box_class'   => 'box box-solid box-solid-with'
                ]
            )
                ->add('description', TextareaType::class, [
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                     'attr'=>[
                        'placeholder' => 'forms.permission_role_description_placeholder',
                        'autocomplete' => 'on', // 'true' n’est pas une valeur valide ici
                        'minlength' => 20, // corriger la faute de frappe (min-lenght)
                        'maxlength' => 20000, // corriger la faute de frappe (max-lenght)
                        'data-escapestrip-html-and-php-tags' => 'true', // custom attribute (JS)
                        'data-event-validate-blur' => 'blur',
                        'data-event-validate-input' => 'input',
                        'data-pattern' => "^[\p{L}\p{M}\p{N}\s.,;:!?\"'’()\[\]\-–—_€$%°\n\r]+$", // Corrigé
                        'rows' => 6
                     ],
                    'translation_domain' => 'Permission'
                ], [])
            ->end()
            ;
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_permission_role';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'permission_role';
    }
}
