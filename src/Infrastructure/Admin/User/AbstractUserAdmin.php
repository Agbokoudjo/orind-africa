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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bundle\SecurityBundle\Security;
use App\Domain\User\Model\BaseUserInterface;
use App\Infrastructure\Admin\WlindablaAdmin;
use App\Domain\User\Model\AdminUserInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use App\Infrastructure\Doctrine\CustomUserInterface;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Infrastructure\Security\Voter\UserProfileEditVoter;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use App\Domain\User\Exception\UnresolvableUserTypeException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Infrastructure\Admin\User\DataTransformer\RoleArrayTransformer;

/**
 * Admin de base pour tous les types d'utilisateurs
 * Utilise les hooks de Sonata (plus fiable que Doctrine Events)
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

        if(!$user instanceof CustomUserInterface){
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

        try {
            // Si le formulaire n'a pas mappé username/email, on les restaure
            $this->restoreCriticalFields($user);

            // Vérifier que les champs critiques ne sont pas vides
            if (empty($user->getUsername())) {
                throw new \InvalidArgumentException('❌ Username ne peut pas être vide');
            }

            if (empty($user->getEmail())) {
                throw new \InvalidArgumentException('❌ Email ne peut pas être vide');
            }
            
            if (empty($user->getPhone())) {
                throw new \InvalidArgumentException('❌ Phone ne peut pas être vide');
            }

            $user->preUpdate();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Hook AVANT la persistence (création)
     * 
     * ✅ Appelé par Sonata AVANT Doctrine
     */
    protected function prePersist(object $object): void
    {
        if (!$object instanceof BaseUserInterface) {
            return;
        }

        $object->prePersist();
    }

    protected function configureRoutes(RouteCollectionInterface $collectionRoutes): void
    {
        parent::configureRoutes($collectionRoutes);
        
         $collectionRoutes->add(
                name: 'toggle_enabled_user_account',
                pattern: 'toggle_enabled_user_account/{slug}/{user_type}',
                defaults: [
                    '_controller' => $this->getController() . '::toggleEnabledUserAccountAction',
                    '_sonata_admin' => $this->getUserAdminCode(),
                ],
                requirements: [ 
                    'user_type' => '[a-zA-Z0-9+/]+={0,2}',  // Base64 encodé
                    'slug' => '[a-f0-9]{32,40}',             // Slug hex (32-40 caractères)
                ],
                methods: ['PATCH'],
            );

        $collectionRoutes->add(
                name: 'resend_email_verify',
                pattern: 'resend_email_verify/{slug}/{user_type}',
                defaults: [
                    '_controller' => $this->getController() . '::resendEmailVerifyAction',
                    '_sonata_admin' => $this->getUserAdminCode(),
                ],
                requirements: [ 
                    'user_type' => '[a-zA-Z0-9+/]+={0,2}',  
                    'slug' => '[a-f0-9]{32,40}',           
                ],
                methods: ['GET'],
            );
          
        $collectionRoutes->add(
                name: 'regenerate_tempory_password_user',
                pattern: 'regenerate_tempory_password_user/{id}/{user_type}',
                defaults: [
                    '_controller' => $this->getController() . '::regenerateTemporyPasswordUserAction',
                    '_sonata_admin' => $this->getUserAdminCode(),
                ],
                requirements: [ 
                    'user_type' => '[a-zA-Z0-9+/]+={0,2}',  
                    'slug' => '[a-f0-9]{32,40}',           
                ],
                methods: ['PATCH'],
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        // if ($this->getRequest()->isXmlHttpRequest()) {
        //     parent::configureListFields($list);
        // }
        $list
            ->add('id', FieldDescriptionInterface::TYPE_INTEGER, [
                'label' => 'list.label_id', 
                'header_class' => 'text-center',
                'row_align' => 'center',
                'role'=>'ROLE_SUPER_ADMIN',
                'translation_domain' => 'SonataUserBundle',
            ])
            ->addIdentifier('username', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'list.label_username',
                'translation_domain' => 'SonataUserBundle',
            ])
            ->add('avatarFile',VichImageType::class,[
                'label'=>'PHOTO',
                'template'=>'bundles/SonataAdminBundle/CRUD/list_image.html.twig',
                'header_style' => 'width:5%',
        ])
            ->add('email', FieldDescriptionInterface::TYPE_EMAIL,[
                'label' => 'list.label_email',
                'translation_domain' => 'SonataUserBundle',
            ])
             ->add('emailVerified',FieldDescriptionInterface::TYPE_BOOLEAN, [
                'label' => 'list.label_email_verified', 
                'header_class' => 'text-center',
                'row_align' => 'center',
                 'translation_domain' => 'SonataUserBundle',
             ])
            ->add('enabled',  FieldDescriptionInterface::TYPE_BOOLEAN, [
                'template'=> "bundles/SonataAdminBundle/CRUD/list_status.html.twig",
                'editable' => true,
                'label' => 'list.label_enabled', 
                'header_class' => 'text-center',
                'row_align' => 'center',
                'translation_domain' => 'SonataUserBundle',
                ])
            ->add('lastLogin', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'list.label_last_login',
                'inline' => true,
                'display' => 'both',
                'format' => 'd/m/Y H:i:s',
                'sortable' => true,
                'translation_domain' => 'SonataUserBundle',
            ])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'translation_domain' => 'SonataAdminBundle',
                'actions' => [
                    'edit' => [],
                    'show' => [],
                    'delete' => [],
                    'toggle_enabled_user_account' => [
                        'template' => 'bundles/SonataAdminBundle/CRUD/list_action_toggle_enabled_user_account.html.twig',
                    ],
                    'resend_email_verify'=>[
                        'template' => 'bundles/SonataAdminBundle/CRUD/list_action_resend_email_verify.html.twig',
                    ],
                    'regenerate_tempory_password_user'=>[
                        'template'=> 'bundles/SonataAdminBundle/CRUD/list_action_regenerate_tempory_password_user.html.twig'
                    ]
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
                    ],
                'show_filter' => true,
            ])
            ->add('username',StringFilter::class, [
            'label' => 'Nom & Prénom(s)',
            'field_type' => TextType::class,
            'force_case_insensitivity' => true,
            'field_options' => [
                'attr' => [
                    'placeholder' => 'Filtre par Noms & Prénoms (s)',
                ]
                ],
            'show_filter' => true,
        ])
           ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        // --- 1. TAB : Informations Générales ---
    $show
        ->tab('tab_general_info', [
            'label' => 'show.tab_general_info',
            'translation_domain' => 'SonataUserBundle',
        ])
            ->with('group_account_details', [
                'label' => 'show.group_account_details',
                'tab' => false,
                'class' => 'col-md-6',
                'box_class' => 'box box-solid box-solid-with',
                'translation_domain' => 'SonataUserBundle',
            ])
                ->add('id', null, [
                    'label' => 'show.label_id',
                    'translation_domain' => 'SonataUserBundle',
                    ])
                ->add('username', null, 
                    ['label' => 'show.label_username',
                'translation_domain' => 'SonataUserBundle',
                ])
                ->add('usernameCanonical', null, [
                    'label' => 'show.label_username_canonical',
                    'translation_domain' => 'SonataUserBundle',
                    ])
                ->add('profile', null, [
                    'label' => 'show.label_profile',
                    'translation_domain' => 'SonataUserBundle'])

                // ->add('groups', null, ['label' => 'show.label_groups'])
            ->end()

            ->with('group_status_security', [
                'label' => 'show.group_status_security',
                'tab' => false,
                'class'=>'col-md-6',
                'box_class' => 'box box-solid box-solid-with',
                'translation_domain' => 'SonataUserBundle',
            ])
                ->add('enabled', 'boolean', [
                    'label' => 'show.label_enabled',
                    'translation_domain' => 'SonataUserBundle',
                    ])
                ->add('emailVerified', FieldDescriptionInterface::TYPE_BOOLEAN, [
                        'label' => 'show.label_email_verified',
                        'translation_domain' => 'SonataUserBundle',
                        ])
                ->add('emailVerifiedAt', FieldDescriptionInterface::TYPE_DATETIME, [
                    'label' => 'show.label_email_verified_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('roles', FieldDescriptionInterface::TYPE_ARRAY, [
                    'label' => 'show.label_roles',
                    'translation_domain' => 'SonataUserBundle',
                    'display' => 'values',
                    'value_translation_domain' =>'role'
                ])
                ->add('confirmationToken', null, [
                    'label' => 'show.label_confirmation_token',
                    'translation_domain' => 'SonataUserBundle',
                    ])
                ->add('tokenRequestedAt', FieldDescriptionInterface::TYPE_DATETIME, [
                    'label' => 'show.label_token_requested_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('salt', null, [
                    'label' => 'show.label_salt',
                    'translation_domain' => 'SonataUserBundle'])
            ->end()
        ->end() // Fin du Tab "Informations Générales"

        // --- 2. TAB : Coordonnées et Fichiers ---
        ->tab('tab_contact_files', [
            'label' => 'show.tab_contact_files',
            'translation_domain' => 'SonataUserBundle'
            ])
            ->with('group_contact_details', [
                'label' => 'show.group_contact_details',
                'tab' => false,
                 'class'=>'col-md-6',
                'box_class' => 'box box-solid box-solid-with',
                'translation_domain' => 'SonataUserBundle',
            ])
                ->add('email', null, [
                    'label' => 'show.label_email',
                    'translation_domain' => 'SonataUserBundle'
                    ])
                ->add('emailCanonical', null, [
                    'label' => 'show.label_email_canonical',
                    'translation_domain' => 'SonataUserBundle'
                    ])
                ->add('phone', null, [
                    'label' => 'show.label_phone',
                    'translation_domain' => 'SonataUserBundle',
                    'template' => 'bundles/SonataAdminBundle/CRUD/show_phone_number.html.twig'
                    ]) 
                ->add('country', null, [
                    'label' => 'show.label_country',
                    'translation_domain' => 'SonataUserBundle',
                     'template' => 'bundles/SonataAdminBundle/CRUD/show_country_name.html.twig'
                ])
            ->end()
            ->with('group_files', [
                'label' => 'show.group_files',
                'tab' => false,
                'class' => 'col-md-6',
                'box_class' => 'box box-solid box-solid-with',
                'translation_domain' => 'SonataUserBundle',
            ])
                ->add('avatarFile',VichImageType::class, [
                    'label' => 'show.label_avatar',
                    'template'=>'bundles/SonataAdminBundle/CRUD/show_image.html.twig',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('documentfile',VichFileType::class, [
                    'label' => 'show.label_document',
                    'template' => 'bundles/SonataAdminBundle/CRUD/show_document.html.twig',
                     'translation_domain' => 'SonataUserBundle',
                ])
            ->end()
        ->end() // Fin du Tab "Coordonnées et Fichiers"

        // --- 4. TAB : Dates et Historique ---
        ->tab('tab_dates_history', [
            'label' => 'show.tab_dates_history',
            'translation_domain' => 'SonataUserBundle',
        ])
            ->with('group_dates', [
                'label' => 'show.group_dates',
                'tab' => false,
                'class' => 'col-md-6',
                'box_class' => 'box box-solid box-solid-with',
                'translation_domain' => 'SonataUserBundle',
            ])
                ->add('createdAt', 'datetime', [
                    'label' => 'show.label_created_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('updatedAt', 'datetime', [
                    'label' => 'show.label_updated_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('lastLogin', 'datetime', [
                    'label' => 'show.label_last_login',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('avatarUpdatedAt', 'datetime', [
                    'label' => 'show.label_avatar_updated_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('documentUpdatedAt', 'datetime', [
                    'label' => 'show.label_document_updated_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
                ->add('passwordRequestedAt', 'datetime', [
                    'label' => 'show.label_password_requested_at',
                    'inline' => true,
                    'display' => 'both',
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'SonataUserBundle',
                ])
            ->end()
        ->end() // Fin du Tab "Dates et Historique"

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
                         'required' => true,
                        'attr' => [
                            'placeholder' => 'Ex: WLINDABABLA Empedocle Brondelle',
                            'autocomplete' => 'on', 
                            'minlength' => 6,      
                            'maxlength' => 255,   
                            'data-pattern' => '^[\p{L}\p{M}\s]+$',
                            'data-eg-await' => 'WLINDABABLA Empedocle Brondelle',
                            'data-escapestrip-html-and-php-tags' => true,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            'data-position-lastname'=>"left",
                            'class' => 'username',
                            'disabled' => $this->isGrantedUser(UserProfileEditVoter::PERMISSION_EDITOR_USERNAME)  === true ? false : true
                        ]
                    ])
                    ->add('email',EmailType::class,[
                        'label' => 'forms.label_email',
            'required' => true,
                        'label_attr' => ['class' => 'form-label'],
                        'attr' => [
                            'placeholder' => 'Ex: your.email@gmail.com',
                            'autocomplete' => 'on',
                            'data-escapestrip-html-and-php-tags' => false,
                            'data-event-validate-blur' => 'blur',
                            'data-event-validate-input' => 'input',
                            'data-type' => "email",
                            'minlength' => 6,
                            'maxlength' => 200,
                            'data-eg-await' => 'franckagbokoudjo301@gmail.com',
                            'data-allow-quoted-local' => 'false',
                            'data-host-whitelist'=> "gmail.com,yahoo.com",
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
                            'autocomplete' => 'on', 
                            'minlength' => 3,      
                            'maxlength' => 150,    
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
            'translation_domain' => 'user',
                            'attr' => [
                                'data-event-validate-blur' => 'blur',
                                'data-event-validate-change' => 'change',
                                'data-media-type' => 'image',
                                'data-type'=>'file',
                                'data-extentions' => 'jpg,png,jpeg',
                                'data-unity-max-size-file' => 'MiB',
                                'data-maxsize-file' => 5,
                                'data-allowed-mime-type-accept' => 'image/jpg,image/png,image/jpeg',
                                'data-min-width' => 50,
                                'data-max-width' => 800,
                                'data-min-height' => 80,
                                'data-max-height' => 800,
                                'accept' => 'image/jpg,image/png,image/jpeg',
                                'placeholder' => 'forms.uploader_placeholder_avatar'
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
                        'translation_domain' => 'user',
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
                            'placeholder' => 'forms.identity_document_placeholder'
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

    /**
     * Ajoute la route de basculement d'activation du compte utilisateur
     */
    private function addToggleUserAccountRoute(
        RouteCollectionInterface $collectionRoutes,
        string $slug,
        string $userTypeEncoded
    ): void {
        $pattern_route=\sprintf('toggle_enabled_user_account/%s/%s',$slug,$userTypeEncoded);
        $collectionRoutes->add(
            name: 'toggle_enabled_user_account',
            pattern: $pattern_route,
            defaults: [
                '_controller' => 'WlindablaAdminCRUDController:toggleEnabledUserAccount',
                '_sonata_admin' => $this->getUserAdminCode(),
            ],
            requirements: [
                'user_type' => '[a-zA-Z0-9+/]+={0,2}',  // Base64 encodé
                'slug' => '[a-f0-9]{32,40}',             // Slug hex (32-40 caractères)
            ],
            methods: ['PATCH'],
        );
    }

    abstract protected function getUserAdminCode():string ;

    /**
     * Encode le type utilisateur en Base64
     *
     * @throws UnresolvableUserTypeException Si le type utilisateur ne peut pas être résolu
     */
    private function getUserTypeBase64Encode(BaseUserInterface $userSubject): string
    {
        try {
            $userType = UserTypeResolver::resolveFromUser($userSubject);
            return base64_encode($userType->value);
        } catch (UnresolvableUserTypeException $e) {
            throw $e;
        }
    }


    /**
     * Crée la configuration de l'action toggle pour l'affichage
     *
     * @throws UnresolvableUserTypeException Si le type utilisateur ne peut pas être résolu
     */
    final public function createToggleEnabledAction(BaseUserInterface $object): array
    {
        if (!$object->getSlug()) {
            throw new \InvalidArgumentException(
                sprintf('L\'utilisateur "%s" n\'a pas de slug défini.', $object->getUsername())
            );
        }

        $isEnabled = $object->isEnabled();

        try {
            $userTypeEncoded = $this->getUserTypeBase64Encode($object);
        } catch (UnresolvableUserTypeException $e) {
            throw new \LogicException(
                sprintf('Impossible de créer l\'action toggle pour l\'utilisateur "%s".', $object->getUsername()),
                0,
                $e
            );
        }

        return [
            'label' => $isEnabled ? 'label_inactive':'label_active',
            'icon' => $isEnabled ? 'fas fa-lock' : 'fas fa-lock-open',
            'title' => $isEnabled ? 'link_title_inactive':'link_title_active',
            'url' => $this->generateUrl('toggle_enabled_user_account', [
                'slug' => $object->getSlug(),
                'user_type' => $userTypeEncoded,
            ]),
            'target' => '_self',  
            'confirm' => $isEnabled ? 'confirm_action_inactive' : 'confirm_action_active',
        ];
    }

    /**
     * Crée la configuration de l'action de renvoi d'email de vérification.
     * Le bouton n'est affiché que si l'email de l'utilisateur n'est PAS vérifié.
     *
     * @throws UnresolvableUserTypeException Si le type utilisateur ne peut pas être résolu
     */
    final public function createResendEmailVerificationAction(BaseUserInterface $object): ?array
    {
        // 1. Condition d'affichage : Si l'email est déjà vérifié, ne pas afficher le bouton.
        if ($object->isEmailVerified()) {
            return null;
        }

        if (!$object->getSlug()) {
            throw new \InvalidArgumentException(
                sprintf('L\'utilisateur "%s" n\'a pas de slug défini.', $object->getUsername())
            );
        }

        try {
            $userTypeEncoded = $this->getUserTypeBase64Encode($object);
        } catch (UnresolvableUserTypeException $e) {
            throw $e ;
        }

        // 2. Configuration pour le bouton (traduisible)
        return [
            'label' => 'label_resend_verification',
            'icon' => 'fas fa-envelope-open-text',
            'title' => 'link_title_resend_verification',
            'url' => $this->generateUrl('resend_email_verify', [ 
                'slug' => $object->getSlug(),
                'user_type' => $userTypeEncoded,
            ]),
            'target' => '_self',
            'confirm' => 'confirm_resend_verification',
        ];
    }

    /**
     * Crée la configuration (data) de l'action "Générer Mot de Passe Temporaire".
     *
     * @param BaseUserInterface $object L'entité utilisateur (BaseUserInterface)
     * @return array|null Le tableau de configuration pour le template, ou null si la condition n'est pas remplie.
     */
    final public function createActionGeneratePassword(BaseUserInterface $object): ?array
    {
        if(!$object->isEnabled()){
            return null ;
        }

        try {
            $userTypeEncoded = $this->getUserTypeBase64Encode($object);
        } catch (UnresolvableUserTypeException $e) {
            throw $e;
        }

        return [
            'label' => 'label_regenerate_password',
            'icon' => 'fas fa-key',
            'title' => 'link_title_regenerate_password',

            'url' => $this->generateUrl('regenerate_tempory_password_user', [
                'id' => $object->getId(),
                'user_type' => $userTypeEncoded,
            ]),

            'target' => '_self',
            'confirm' => 'confirm_regenerate_password',

            'extra_class' => 'js-regenerate-password-btn',
        ];
    }
    abstract protected function getController():string ;

    /**
     * Restaure les champs critiques depuis l'ancienne entité
     * 
     * ⚠️ Si le formulaire n'a pas mappé username/email, on les restaure
     * depuis la base de données
     */
    private function restoreCriticalFields(CustomUserInterface $object): void
    {        
           // ->getEntityChangeSet($user);
        // Récupérer les données originales
        $unitOfWork = $this->getModelUserManager()->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($object);

        // 1. Restaurer username s'il est vide
        if (empty($object->getUsername()) && isset($originalData['username'])) {
            $object->setUsername($originalData['username']);

        }

        // 2. Restaurer email s'il est vide
        if (empty($object->getEmail()) && isset($originalData['email'])) {
            $object->setEmail($originalData['email']);
        }

        if (empty($object->getPhone()) && isset($originalData['phone'])) {
            $object->setPhone($originalData['phone']);
        }
    }
}
