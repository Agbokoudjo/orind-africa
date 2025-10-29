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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bundle\SecurityBundle\Security;
use App\Infrastructure\Admin\WlindablaAdmin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\UI\Http\Controller\Admin\GroupActionCRUDController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Infrastructure\Doctrine\Entity\Action\GroupActionEntity;
use App\Infrastructure\Security\Handler\GroupActionSecurityHandler;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use App\Infrastructure\Doctrine\Entity\Action\DomainActionMinisterEntity;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;
use App\Infrastructure\Doctrine\Entity\User\Repository\MemberUserRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.group.action',
        'code' => "sonata.admin.group.action",
        'admin_code' => "sonata.admin.group.action",
        'model_class' => GroupActionEntity::class,
        'manager_type' => 'orm',
        'controller' => GroupActionCRUDController::class,
        'group' => 'app.admin.group.domain_group.action',
        'group_code' => 'app.admin.group.domain_group.action',
        'label' => 'sonata.admin.group.action',
        'label' => 'sonata.admin.group.action',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_ADMIN'],
        'security_handler'=>GroupActionSecurityHandler::class
    ]
)]
final class GroupActionAdmin extends WlindablaAdmin
{
    public function __construct(
        private readonly Security $security,
        private readonly MemberUserRepository $memberUserModel
        )
    {
        parent::__construct("label_list_group_action",null,'label_create_group_action');
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_group_action_minister';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'group_action_minister';
    }

    
   
    protected function configureListFields(ListMapper $list): void{
        
    }

    protected function configureFormFields(FormMapper $form): void
    {

        $form

            ->with('genrale', [
                'label' => 'forms.with_generale',
                'translation_domain' => 'action',
                'class' => 'col-md-6',
                'box_class' => 'box box-solid box-solid-with'
            ])
            ->add('name', TextType::class, [
                'label' => 'forms.label_group_name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => [
                    'placeholder' => 'Ex: MAQUETTE=UX\UI DESIGN...',
                    'autocomplete' => 'on', // corrigé
                    'minlength' => 6,       // corrigé
                    'maxlength' => 255,     // corrigé
                    'data-pattern' => '^[\p{L}\p{M}\s\'\\\(\)]+$',
                    'data-eg-await' => 'Communication',
                    'data-escapestrip-html-and-php-tags' => 'true',
                    'data-event-validate-blur' => 'blur',
                    'data-event-validate-input' => 'input',
                    'class' => 'firstname',
                ]
            ])

            ->add('domain', EntityType::class, [
                'label' => 'forms.label_group_domain_action',
                'label_attr' => ['class' => 'mt-3 form-label'],
                'class' => DomainActionMinisterEntity::class,
                'choice_label' => 'name',
                'multiple' => false,
                'required'=>true,
                'choices' => $this->getMinisterUserDomains(),
                'attr' => [
                    'class' => 'h-50 select2 form-select',
                    'data-sonata-select2' => 'true',
                    'data-sonata-select2-minimumResultsForSearch' => '6',
                    'data-sonata-select2-allow-clear' => 'true',
                ],
            ])
            ->end()
            ->with('handler', [
                'label' => 'forms.label_group_description',
                'translation_domain' => 'action',
                'class' => 'col-md-6',
                'box_class' => 'box box-solid box-solid-with'
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'label_attr' => ['class' => 'form-label'],
                'row_attr' => ['class' => 'mt-3'],
                'attr' => [
                    'autocomplete' => 'on', // 'true' n’est pas une valeur valide ici
                    'minlength' => 20, // corriger la faute de frappe (min-lenght)
                    'maxlength' => 20000, // corriger la faute de frappe (max-lenght)
                    'data-escapestrip-html-and-php-tags' => 'true', // custom attribute (JS)
                    'data-event-validate-blur' => 'blur',
                    'data-event-validate-input' => 'input',
                    'data-pattern' => "^[\p{L}\p{M}\p{N}\s.,;:!?\"'’()\[\]\-–—_€$%°\n\r]+$", // Corrigé
                    'rows' => 6
                ]
            ])
            ->end()
            ->with('member',[
                'label' => 'forms.label_group_members',
                'translation_domain' => 'action',
                'class' => 'col-md-12',
                'box_class' => 'box box-solid box-solid-with mt-3'
                ])
                ->add('members',EntityType::class,[
                        'label' => false,
                        'class' => MemberUser::class,
                        'choice_label' => 'username',
                        'multiple' => true,
                        'required' => true,
                        'choices' => $this->memberUserModel->findByForUsers(),
                        'attr' => [
                            'class' => 'h-50 select2 form-select',
                            'data-sonata-select2' => 'true',
                            'data-sonata-select2-minimumResultsForSearch' => '6',
                            'data-sonata-select2-allow-clear' => 'true',
                        ],
                    ])
            ->end()
        ;
    }

    // Renommée pour être plus claire : 'get' indique qu'on retourne la donnée
    private function getMinisterUserDomains(): array
    {
        // 1. Récupérer l'utilisateur connecté via le service Security
        $user = $this->security->getUser();

        // 2. Vérifier si l'utilisateur est connecté ET s'il est du bon type
        if (!$user instanceof AdminUser) {
            return [];
        }

        if ($user->hasRole('ROLE_MINISTER')) {

            // Si le rôle est vérifié, retourner la liste de domaines
            return $user->getDomains()->toArray();
        }

        // 4. Retourner un tableau vide par défaut si les conditions ne sont pas remplies
        return [];
    }
}