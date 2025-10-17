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

use App\Domain\User\Enum\RoleUser;
use libphonenumber\PhoneNumberFormat;
use App\Domain\User\BaseUserInterface;
use App\Domain\User\AdminUserInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bundle\SecurityBundle\Security;
use App\Infrastructure\Admin\WlindablaAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Infrastructure\Security\Voter\UserProfileEditVoter;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Infrastructure\Admin\User\DataTransformer\RoleArrayTransformer;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 */
abstract class AbstractUserAdmin extends WlindablaAdmin
{
    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = ['Default'];

        if (!$this->hasSubject() || null === $this->getSubject()->getId()) {
            $formOptions['validation_groups'][] = 'Registration';
        } else {
            $formOptions['validation_groups'][] = 'Profile';
        }
    }

    final protected function preUpdate(object $user):void{

        if(!$user instanceof BaseUserInterface){
            return  ;
        }

        if(!$this->getSymfonySecurity()->isGranted(UserProfileEditVoter::PERMISSION_EDITOR_USER_PROFILE, $user)){

            // Comparer avec l'ancien objet pour voir si username/email/phone a changé
            $changes = $this->getModelUserManager()
                            ->getUnitOfWork()
                            ->getEntityChangeSet($user);
            foreach (['username', 'email', 'phone'] as $field) {
                if (array_key_exists($field, $changes)) {
                    throw new AccessDeniedException('Modification interdite de ' . $field);
                }
            }
        }

    }
     
    public function configureRoutes(RouteCollectionInterface $collectionRoutes):void{
        // if(!$this->hasAccess('list',$this->getSubject())){
        //     $collectionRoutes->remove('add');
        // }
        // dump($this->getSubject());
        // dump($this->isGranted('ROLE_ADMIN'));
    }
    protected function configureListFields(ListMapper $list): void
    {
        // if ($this->getRequest()->isXmlHttpRequest()) {
        //     parent::configureListFields($list);
        // }
        $list
            ->add('id', FieldDescriptionInterface::TYPE_INTEGER, [
                'label' => 'Id',
            ])
            ->addIdentifier('username', FieldDescriptionInterface::TYPE_STRING, [
            'label' => 'Nom & Prénom(s)',
            ])
            ->add('email', FieldDescriptionInterface::TYPE_EMAIL)
            ->add('enabled',  FieldDescriptionInterface::TYPE_BOOLEAN, [
                'label'=>'Status',
                'template'=> "bundles/SonataAdminBundle/CRUD/list_status.html.twig",
                'editable' => true
                ])
            ->add('phone', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'Téléphone',
                'template' => 'bundles/SonataAdminBundle/CRUD/list_phone_number.html.twig'
            ])
            ->add('createdAt', FieldDescriptionInterface::TYPE_DATE, [
                'label' => 'DATE',
                'inline' => true,
                'display' => 'both',
                'format' => 'd-m-Y'
            ]);

        // if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
        //     $list
        //         ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
        //             'virtual_field' => true,
        //             'template' => '@SonataUser/Admin/Field/impersonating.html.twig',
        //         ]);
        // }

        $list->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
            'translation_domain' => 'SonataAdminBundle',
            'actions' => [
                'edit' => [],
                'show' => [],
                'delete' => [],
            ],
            'label' => 'Actions'
        ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', StringFilter::class, [
                'label' => 'Email',
                'field_type' => TextType::class,
                'force_case_insensitivity' => true,
                'field_options' => [
                    'attr' => [
                        'placeholder' => 'Filtre par email. Eg:internationaleswebservices@gmail.com',
                    ]
                ]
            ])
            ->add('username',StringFilter::class, [
            'label' => 'Nom & Prénom(s)',
            'field_type' => TextType::class,
            'force_case_insensitivity' => true,
            'field_options' => [
                'attr' => [
                    'placeholder' => 'Filtre par Noms & Prénoms (s)',
                ]
            ]
        ])
           ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('username')
            ->add('email')
            ->add('roles',FieldDescriptionInterface::TYPE_ARRAY)
            ;
    }

    protected function configureFormFieldsParent(FormMapper $form): FormMapper
    { 
        $form
            ->tab('infos_generale',[
                    'translation_domain'=>'user',
                    'label'=> 'forms.label_tab_infos_generale'
                 ]) 
                ->with('genrale',[
                    'label'=>'forms.with_generale',
                     'translation_domain' => 'user',
                    'class'=>'col-md-6',
                    'box_class' => 'box box-solid box-solid-with'
                    ])
                    ->add('username',TextType::class,[
                        'label'=>'forms.label_username',
                        'label_attr' => ['class' => 'form-label'],
                        'attr' => [
                            'placeholder' => 'Ex: WLINDABABLA Empedocle Brondelle',
                            'autocomplete' => 'on', // corrigé
                            'minlength' => 6,       // corrigé
                            'maxlength' => 255,     // corrigé
                            'data-pattern' => '^[\p{L}\p{M}\s\']+$',
                            'data-eg-await' => 'WLINDABABLA Empedocle Brondelle',
                            'data-escapestrip-html-and-php-tags' => true,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            'class' => 'firstname',
                            'disabled' => $this->isGrantedUser(UserProfileEditVoter::PERMISSION_EDITOR_USERNAME)  === true ? false : true
                        ]
                    ])
                    ->add('email',EmailType::class,[
                        'label' => 'forms.label_email',
                        'label_attr' => ['class' => 'form-label'],
                        'attr' => [
                            'placeholder' => 'Ex: your.email@gmail.com',
                            'autocomplete' => 'on',
                            'data-escapestrip-html-and-php-tags' => false,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            'data-eg-await' => 'franckagbokoudjo301@gmail.com',
                            'data-type' => 'email',
                            'disabled'=> $this->isGrantedUser(UserProfileEditVoter::PERMISSION_EDITOR_EMAIL)  === true ? false : true
                        ]
                    ])
                ->end()
                ->with('phone_and_country', [
                        'label' => 'forms.label_geolocalisation',
                        'translation_domain' => 'user',
                        'label_attr' => ['class' => 'form-label'],
                        'class' => 'col-md-6',
                       'box_class' => 'box box-solid box-solid-with'
                    ])
                    ->add('phone',  PhoneNumberType::class, [
                        'label' => 'forms.label_phone',
                        'required' => true,
                        'label_attr' => ['class' => 'form-label'],
                        'format' => PhoneNumberFormat::INTERNATIONAL,
                        'attr' => [
                            'placeholder' => 'Ex: +33 XX XX XX XX',
                            'data-escapestrip-html-and-php-tags' => true,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            "data-eg-await" => '+229 XX XX XX XX',
                            'autocomplete' => 'on',
                            'minlength' => 8,
                            'maxlength' => 80,
                            'data-type'  => 'tel',
                            'disabled'=> $this->isGrantedUser(UserProfileEditVoter::PERMISSION_EDITOR_PHONE)  === true ? false : true
                        ]
                    ], [])
                    ->add('country', CountryType::class, [
                        'label' => 'forms.country.placeholder',
                        'label_attr' => ['class' => 'form-label'],
                        'required' => true,
                        "alpha3" => true,
                        'choice_translation_domain' => false,
                        'attr' => [
                            'data-escapestrip-html-and-php-tags' => true,
                            'data-event-validate-change' => 'change',
                            'data-event-validate-blur' => 'blur',
                            'autocomplete' => 'on', // corrigé
                            'minlength' => 3,      // corrigé
                            'maxlength' => 150,     // corrigé,
                            'data-pattern' => '^[\p{L}\p{M}\s\'-]+$',
                            'class' => 'form-control select2 form-select form-select-lg',
                            'data-minimumInputLength' => 3
                        ]
                    ])
                ->end()
                ->with('mission_profil',[
                    'label' => 'forms.mission_profil',
                    'translation_domain' => 'user',
                    'label_attr' => ['class' => 'form-label'],
                    'class' => 'col-md-12',
                    'box_class' => 'box box-solid box-solid-with mt-4'
                ])
                    ->add('profile',TextType::class,[
                        'label' => 'forms.label_profile',
                        'label_attr' => ['class' => 'form-label'],
                        'required'=>false,
                         'translation_domain' => 'user',
                        'attr' => [
                            'placeholder' => 'forms.label_profile_placeholder',
                            'autocomplete' => 'on', // corrigé
                            'minlength' => 6,       // corrigé
                            'maxlength' => 200,     // corrigé
                            'data-pattern' => '^[\p{L}\p{M}-_\s &]+$',
                            'data-eg-await' => 'forms.label_profile_placeholder',
                            'data-escapestrip-html-and-php-tags' => true,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input'
                        ]
                    ])
                ->end()
            ->end()
            ->ifTrue($this->getSubject() instanceof AdminUserInterface)
                ->ifTrue($this->isGranted('ROLE_FOUNDER'))
                    ->tab('roles', [
                        'label' => 'forms.roles',
                        'translation_domain' => 'user',
                    ])
                        ->with('roles', [
                            'label' => false,
                            'translation_domain' => 'user',
                            'class' => 'col-md-12',
                            'collapsed' => true
                        ])
                            ->add('roles', ChoiceType::class, [
                                'label' => false,
                                'multiple'=>false,
                                'choices' => $this->roleUser(),
                                'choice_translation_domain'=>'role',
                                'required'=>true,
                                'expanded'=>true ,
                                'attr'=>['class'=>'d-flex flex-row']  
                            ],[
                            ]);

                            if($form->has('roles')){
                                $form->get('roles')
                                    ->addModelTransformer(new RoleArrayTransformer($this->getSubject()));
                            }
                        $form->end()
                    ->end()
                ->ifEnd()
            ->ifEnd()
            ->tab('DOCUMENTS')
                ->with('photo', [
                        'label' => 'forms.label_avatar',
                        'translation_domain' => 'user',
                        'label_attr' => ['class' => 'form-label'],
                        'class' => 'col-md-6',
                        'box_class' => 'box box-solid box-solid-with'
                    ])
                     ->add('avatarFile', DropzoneType::class, [
                            'label' => false,
                            'required' =>false,
                            'label_attr' => ['class' => 'form-label'],
                            'attr' => [
                                'data-event-validate-blur' => 'blur',
                                'data-event-validate-change' => 'change',
                                'data-media-type' => 'image',
                                'data-extentions' => 'jpg,png,jpeg',
                                'data-unity-max-size-file' => 'MiB',
                                'data-maxsize-file' => 5,
                                'data-allowed-mime-type-accept' => 'image/jpg,image/png,image/jpeg',
                                'data-min-width' => 50,
                                'data-max-width' => 800,
                                'data-min-height' => 80,
                                'data-max-height' => 800,
                                'accept' => 'image/jpg,image/png,image/jpeg',
                                'placeholder' => 'Drag and drop a file or click to browse'
                            ]
                            ],[
                                'role'=> 'ROLE_SONATA_ADMIN_USER_ADMIN_EDIT'
                            ])
                ->end()
                ->with('identity_document', [
                        'label' => 'forms.label_identitydocument',
                        'translation_domain' => 'user',
                        'label_attr' => ['class' => 'form-label'],
                        'class' => 'col-md-6',
                        'box_class' => 'box box-solid box-solid-with'
                    ])
                    ->add('documentfile', DropzoneType::class, [
                        'label' => false,
                        'required' => false,
                        'label_attr' => ['class' => 'form-label fw-bold'],
                        'attr' => [
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-change' => 'change',
                            'data-media-type' => 'document',
                            'data-extentions' => 'pdf',
                            'data-unity-max-size-file' => 'MiB',
                            'data-maxsize-file' => '8',
                            'data-allowed-mime-type-accept' => 'application/x-pdf,application/pdf',
                            'accept' => 'application/x-pdf,application/pdf',
                            'placeholder' => 'Drag and drop a file or click to browse'
                        ]
                        ],[
                            'role' => 'ROLE_SONATA_ADMIN_USER_ADMIN_EDIT'
                        ])
                ->end()
            ->end()
            ;
            return $form ;
    }

    protected function configureExportFields(): array
    {
        // Avoid sensitive properties to be exported.
        return array_filter(
            parent::configureExportFields(),
            static fn(string $v): bool => !\in_array($v, ['password', 'salt'], true)
        );
    }

    final protected function isGrantedUser(string $attribute = 'PERMISSION_EDITOR_USER_PROFILE'):bool{

        return $this->getSymfonySecurity()->isGranted($attribute, $this->getSubject());
    }

    
    abstract protected function getSymfonySecurity(): Security;

    abstract protected function getModelUserManager(): EntityManagerInterface;

    /**
     * Traduit tous les cas (cases) de l'énumération RoleUser pour les utiliser dans un ChoiceType.
     * * @return array<string, string> Les clés sont les libellés traduits, les valeurs sont les valeurs des rôles (ex: ROLE_MINISTER).
     */
    private function roleUser(): array
    {
        $choices_roles = [];

        foreach (RoleUser::cases() as $roleCase) {

            $choices_roles[$roleCase->name] = $roleCase->value;
        }
    
        return $choices_roles;
    }

    final protected function isFormEdit(): bool
    {
        return $this->hasSubject() && $this->getSubject()->getId() !== null;
    }
}
