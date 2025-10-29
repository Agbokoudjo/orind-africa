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

namespace App\Infrastructure\Admin;

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\UI\Http\Controller\Admin\DomainActionMinisterCRUDController;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use App\Infrastructure\Doctrine\Entity\Action\DomainActionMinisterEntity;
use App\Infrastructure\Doctrine\Entity\User\Repository\AdminUserRepository;
use App\Infrastructure\Security\Handler\DomainActionSecurityHandler;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.domain.action',
        'code' => "sonata.admin.domain.action",
        'admin_code' => "sonata.admin.domain.action",
        'model_class' => DomainActionMinisterEntity::class,
        'manager_type' => 'orm',
        'controller' => DomainActionMinisterCRUDController::class,
        'group' => 'app.admin.group.domain_group.action',
        'group_code' => 'app.admin.group.domain_group.action',
        'label' => 'sonata.admin.domain.action',
        'label' => 'sonata.admin.domain.action',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_ADMIN'],
        'security_handler'=>DomainActionSecurityHandler::class
    ]
)]
final class DomainActionMinisterAdmin extends WlindablaAdmin
{
    public function __construct()
    {
        parent::__construct("label_list_domain_action",null,'label_create_domain_action');
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_domain_action_minister';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'domain_action_minister';
    }

    protected function configureListFields(ListMapper $list): void
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            parent::configureListFields($list);
        }
    }
    protected function configureFormFields(FormMapper $form): void{
    
        $form

            ->with('genrale',[
                    'label'=>'forms.with_generale',
                     'translation_domain' => 'action',
                    'class'=>'col-md-6',
                    'box_class' => 'box box-solid box-solid-with'
                    ])
                ->add('name',TextType::class,[
                    'label'=> 'forms.label_domain_name',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => [
                        'placeholder' => 'Ex: Communication...',
                        'autocomplete' => 'on', 
                        'minlength' => 6,       
                        'maxlength' => 255,     
                        'data-pattern' => '^[\p{L}\p{M}\s\']+$',
                        'data-eg-await' => 'Communication',
                        'data-escapestrip-html-and-php-tags' => 'true',
                        'data-event-validate-blur' => 'blur',
                        'data-event-validate-input' => 'input',
                        'class' => 'firstname',
                    ]
                ])
                
                ->add('owner', EntityType::class, [
                    'label' => 'forms.label_minister_handler',
                    'label_attr' => ['class' => 'mt-3 form-label'],
                    'class' => AdminUser::class,
                    'choice_label' => 'username',
                    'multiple' => false,
                    'query_builder' => function (AdminUserRepository $model): QueryBuilder {
                        return $model->createQueryBuilderForUsersWithRole() ; 
                    },
                    'attr' => [
                        'class' => 'h-50 select2 form-select',
                        'data-sonata-select2' => 'true',
                        'data-sonata-select2-minimumResultsForSearch' => '6',
                        'data-sonata-select2-allow-clear' => 'true',
                    ],
                ])
            ->end()
            ->with('handler',[
                'label' => 'forms.label_domain_description',
                'translation_domain' => 'action',
                'class' => 'col-md-6',
                'box_class'=>'box box-solid box-solid-with'
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mt-3'],
                'attr' => [
                    'autocomplete' => 'on', 
                    'minlength' => 20, 
                    'maxlength' => 20000, 
                    'data-escapestrip-html-and-php-tags' => 'true', 
                    'data-event-validate-blur' => 'blur',
                    'data-event-validate-input' => 'input',
                    'data-pattern' => "^[\p{L}\p{M}\p{N}\s\p{P}\n\r]+$", // Corrigé
                    'rows' => 6
                ]
            ])
            ->end()            
                ;
    }
    
}
