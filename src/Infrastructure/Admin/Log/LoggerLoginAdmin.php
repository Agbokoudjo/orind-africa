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

namespace App\Infrastructure\Admin\Log;

use App\Domain\User\Enum\UserClass;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimeRangeType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use App\Infrastructure\Security\Voter\LoggerLoginVoter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use App\UI\Http\Controller\Admin\Log\LoggerLoginCRUDController;
use App\Infrastructure\Doctrine\Entity\User\LoggerLoginUserEntity;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use App\Infrastructure\Security\Handler\LoggerLoginSecurityHandler;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.logger.login',
        'code' => "sonata.admin.logger.login",
        'admin_code' => "sonata.admin.logger.login",
        'model_class' => LoggerLoginUserEntity::class,
        'manager_type' => 'orm',
        'controller' => LoggerLoginCRUDController::class,
        'group' => 'sonata.admin.logger',
        'group_code' => 'sonata.admin.logger',
        'label' => 'sonata.admin.logger.login',
        'label' => 'sonata.admin.logger.login',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_AUDITOR_LOGIN'],
        'security_handler' => LoggerLoginSecurityHandler::class
    ]
)]
final class LoggerLoginAdmin extends WlindablaAdmin{

    public function __construct()
    {
        parent::__construct("label_list_logger_login", "label_show_logger_login");
    }

    protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $translationDomain = 'logger';

        // ----------------------------------------------------
        // 1. Filtres d'Identité
        // ----------------------------------------------------
        $datagrid
            // Filtre par Nom d'Utilisateur
            ->add('username', StringFilter::class, [
                'label' => 'login.username',
            'field_type' => TextType::class,
                'translation_domain' => $translationDomain,
            'show_filter' => true,
            ])

            // Filtre par Adresse Email
            ->add('email', StringFilter::class, [
                'label' => 'login.email',
            'field_type' => TextType::class,
                'translation_domain' => $translationDomain,
            'show_filter' => true,
            ])

            // 2. Filtre de Rôle (UserClass)
            // Utilise ChoiceFilter pour permettre la sélection parmi les valeurs de l'Enum
            ->add('userClass', ChoiceFilter::class, [
                'label' => 'login.userClass',
                'translation_domain' => $translationDomain,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getUserClassChoices(), 
                'choice_translation_domain' => 'user',
                     'translation_domain'=>'user'
                ],
            'show_filter' => true,
            ],[
            'role'=>LoggerLoginVoter::PERMISSION_AUDITOR
            ])

            // 3. Filtre de Contexte (IP)
            ->add('lastLoginIp', StringFilter::class, [
                'label' => 'login.ip_address',
            'field_type' => TextType::class,
                'translation_domain' => $translationDomain,
                'show_filter' => false, 
            ],[
            'role' => LoggerLoginVoter::PERMISSION_AUDITOR
            ])

            // 4. Filtre Temporel (Critique pour l'Audit)
            // Permet de filtrer par une plage de dates (ex: tentatives d'hier)
            ->add('createdAt', DateTimeRangeFilter::class, [
                'label' => 'login.createdAt',
                'field_type'=> DateTimeRangeType::class,
                'translation_domain' => $translationDomain,
                'field_options' => [
                    // Configure le type de champ pour la date/heure
                    'field_options_start' => [
                        'widget' => 'single_text',
                        // 'format' => 'yyyy-MM-dd HH:mm',
                    ],
                    'field_options_end' => [
                        'widget' => 'single_text',
                        // 'format' => 'yyyy-MM-dd HH:mm',
                    ],
                ],
            ])
        ;
    }
    
    // Les logs doivent être immuables (une fois écrits, ils ne bougent plus)
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    protected function configureListFields(ListMapper $list): void
    {
        // 1. Informations de Base (ID et Heure)
        $list
            // L'ID est l'identifiant unique (non modifiable)
            ->add('id', FieldDescriptionInterface::TYPE_IDENTIFIER, [
                'label' => 'login.id',
                'header_style' => 'width: 5%',
                'translation_domain'=>'logger'
            ])

            // 2. Utilisateur et Rôle (Informations Clés)
            ->add('username', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'login.username',
                'translation_domain' => 'logger'
            ])
            ->add('email', FieldDescriptionInterface::TYPE_EMAIL, [
                'label' => 'login.email',
            'translation_domain' => 'logger'
            ])

            // Le champ userClass est une Enum, nous l'affichons comme 'string' (ou 'choice' si l'affichage est complexe)
            ->add('userClass', FieldDescriptionInterface::TYPE_ENUM, [
                'label' => 'login.user_class',
                'header_style' => 'width: 12%; text-align: center',
                'row_align' => 'center',
                'translation_domain' => 'logger',
                'template'=> 'bundles/SonataAdminBundle/CRUD/list_user_class.html.twig'
            ])

            // 3. Contexte d'Accès
            ->add('lastLoginIp', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'login.lastLoginIp',
                'header_style' => 'width: 15%',
                'translation_domain' => 'logger'
            ])

            // 4. Horodatage (Critique pour l'Audit)
            ->add('createdAt', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'login.createdAt',
                'format' => 'd/m/Y H:i:s', // Format lisible pour l'audit
                'header_style' => 'width: 18%; text-align: right',
                'row_align' => 'right',
                'translation_domain' => 'logger'
            ])

            // 5. Actions (Pour la Vue Détail)
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'login.actions',
            'translation_domain' => 'logger',
                'actions' => [
                    'show' => [], // Permet de voir les détails (si configuré)
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        // Définir le domaine de traduction une seule fois pour éviter la répétition
        $translationDomain = 'logger';

        // ----------------------------------------------------
        // I. IDENTIFIANT DE L'AUDIT (Non Modifiable)
        // ----------------------------------------------------
        $show
            ->with('Audit Info', ['class' => 'col-md-4',
            'label' => 'login.audit_info',
            'translation_domain' => $translationDomain])
            ->add('id', FieldDescriptionInterface::TYPE_INTEGER, [
                'label' => 'login.id',
                'translation_domain' => $translationDomain,
            ])
            ->add('createdAt', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'login.createdAt',
                'translation_domain' => $translationDomain,
                'format' => 'd/m/Y H:i:s',
            ])
            ->end()

            // ----------------------------------------------------
            // II. INFORMATIONS D'IDENTITÉ
            // ----------------------------------------------------
            ->with('Informations Utilisateur', [
                'class' => 'col-md-8',
                'label' => 'login.info_user',
                'translation_domain' => $translationDomain
                ])
            // Utilisateur
            ->add('username', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'login.username',
                'translation_domain' => $translationDomain,
            ])
            // Email
            ->add('email', FieldDescriptionInterface::TYPE_EMAIL, [
                'label' => 'login.email',
                'translation_domain' => $translationDomain,
            ])
            // Classe/Rôle de l'utilisateur
            // Note: Affiché comme string car la valeur de l'Enum est stockée en BDD
            ->add('userClass', FieldDescriptionInterface::TYPE_ENUM, [
                'label' => 'login.user_class',
                'translation_domain' => $translationDomain,
            'template' => 'bundles/SonataAdminBundle/CRUD/show_user_class.html.twig'
            ])
            ->end()

            // ----------------------------------------------------
            // III. CONTEXTE DE LA REQUÊTE
            // ----------------------------------------------------
            ->with('Contexte Technique', [
                'class' => 'col-md-12',
            'label' => 'login.context',
            'translation_domain' => $translationDomain])
            // Adresse IP
            ->add('lastLoginIp', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'login.lastLoginIp',
                'translation_domain' => $translationDomain,
            ])
            ->end()
        ;
    }
    
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_logger_login_user';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'logger_login_user';
    }

    /**
     * Construit le tableau des choix pour l'Enum UserClass.
     * @return array<string, string>
     */
    private function getUserClassChoices(): array
    {
        $choices = [];
        // Itere sur tous les cas de l'Enum et utilise la valeur comme clé et valeur.
        foreach (UserClass::cases() as $case) {
            $choices[$case->name] = $case->value;
        }
        return $choices;
    }
}
