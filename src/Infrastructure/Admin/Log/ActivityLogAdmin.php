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

use Sonata\Form\Type\DateRangeType;
use App\Domain\Log\Enum\ActivityAction;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Infrastructure\Doctrine\Entity\Log\ActivityLogEntity;
use App\UI\Http\Controller\Admin\Log\ActivityLogCRUDController;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use App\Infrastructure\Security\Handler\ActivityLogSecurityHandler;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(
    name: 'sonata.admin',
    attributes: [
        'id' => 'sonata.admin.logger.activity.log',
        'code' => "sonata.admin.logger.activity.log",
        'admin_code' => "sonata.admin.logger.activity.log",
        'model_class' => ActivityLogEntity::class,
        'manager_type' => 'orm',
        'controller' => ActivityLogCRUDController::class,
        'group' => 'sonata.admin.logger',
        'group_code' => 'sonata.admin.logger',
        'label' => 'sonata.admin.logger.activity.log',
        'label' => 'sonata.admin.logger.activity.log',
        'translation_domain' => 'Admin',
        'show_in_roles_matrix' => true,
        'roles' => ['ROLE_AUDITOR_ACTIVITY_LOG'],
        'security_handler' => ActivityLogSecurityHandler::class,
        'pager_type' => 'simple'
    ]
)]
final class ActivityLogAdmin extends WlindablaAdmin
{
    // Définir le domaine de traduction pour toute la classe Admin
    protected string $translationDomain = 'audit';

    public function __construct()
    {
        parent::__construct("label_list_activity_log", "label_show_activity_log");
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            // Qui (Utilisateur)
            ->add('userContext', FieldDescriptionInterface::TYPE_ARRAY, [
                'label' => 'audit.user',
                'template' => 'bundles/SonataAdminBundle/CRUD/list_user_context.html.twig',
                 'translation_domain' => 'logger',
            ])

            // Quoi (Action)
            ->add('action', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'audit.action',
                'header_style' => 'width: 15%; text-align: center',
                'row_align' => 'center',
                'translation_domain' => 'logger',
                'template'=> "bundles/SonataAdminBundle/CRUD/list_activity_action.html.twig"
            ])

            // Où (Route)
            ->add('route', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'audit.route',
                'translation_domain' => 'logger',
            ])

            // Comment (Méthode)
            ->add('method', FieldDescriptionInterface::TYPE_STRING, [
                'label' => 'audit.method',
                'header_style' => 'width: 8%; text-align: center',
                'row_align' => 'center',
            'translation_domain' => 'logger',
            'template'=> 'bundles/SonataAdminBundle/CRUD/list_field_method.html.twig'
            ])
            ->add(
                'ipAddress',
                FieldDescriptionInterface::TYPE_STRING,
                [
                    'label' => 'audit.ip_address',
                    'translation_domain' => 'logger',
                ]
            )
            // Quand (Date)
            ->add('createdAt', FieldDescriptionInterface::TYPE_DATETIME, [
                'label' => 'audit.created_at',
                'format' => 'd/m/Y H:i:s',
                'header_style' => 'width: 18%; text-align: right',
                'row_align' => 'right',
            'translation_domain' => 'logger',
            ])
            // Actions
            ->add(ListMapper::NAME_ACTIONS, null, [
                'label' => 'global.audit',
            'translation_domain' => 'logger',
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Identité de l\'Action', ['class' => 'col-md-5'])
                ->add('id', FieldDescriptionInterface::TYPE_INTEGER, 
                     ['label' => 'audit.id', 
                     'translation_domain' => 'logger']
                )
                ->add('createdAt', FieldDescriptionInterface::TYPE_DATETIME, 
                    ['label' => 'audit.created_at', 
                    'format' => 'd/m/Y H:i:s',
                    'translation_domain' => 'logger',
                    ])
                ->add('action', FieldDescriptionInterface::TYPE_STRING, [
                    'label' => 'audit.action',
                    'translation_domain' => 'logger',
                    'template' => "bundles/SonataAdminBundle/CRUD/show_activity_action.html.twig"
                ])
                ->add('method', FieldDescriptionInterface::TYPE_STRING, [
                    'label' => 'audit.method',
                    'translation_domain' => 'logger',
                'template' => 'bundles/SonataAdminBundle/CRUD/show_field_method.html.twig'
                ])
            ->end()

            ->with('Auteur', ['class' => 'col-md-7'])
                ->add('userContext', FieldDescriptionInterface::TYPE_ARRAY, [
                    'label' => 'audit.user',
                    'template' => 'bundles/SonataAdminBundle/CRUD/show_user_context.html.twig',
                    'translation_domain' => 'logger',
                ])
                ->add('ipAddress', FieldDescriptionInterface::TYPE_STRING, 
                    ['label' => 'audit.ip_address',
                    'translation_domain' => 'logger',
                ])
            ->end()

            ->with('Contexte Technique & Changements', ['class' => 'col-md-12'])
                ->add('route', FieldDescriptionInterface::TYPE_STRING, [
                    'label' => 'audit.route',
                    'translation_domain' => 'logger',
                ])
            // Le Contexte (données supplémentaires, user-agent, route params, changes)
                ->add('context', FieldDescriptionInterface::TYPE_ARRAY, [
                    'label' => 'audit.context_data',
                    'translation_domain' => 'logger',
                    'template' => 'bundles/SonataAdminBundle/CRUD/show_user_context_requete.html.twig',
                ])
            ->end()
        ;
    }

   protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('createdAt', DateRangeFilter::class, [
            'label' => 'audit.created_at',
                'field_type' => DateRangeType::class,
                'translation_domain' => 'logger',
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
            // Filtre d'Action (par Enum)
            ->add('action', ChoiceFilter::class, [
                'label' => 'audit.action',
                'translation_domain' => 'logger',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getActionChoices(),
                    'choice_translation_domain' => 'activity_log', 
                ],
            'show_filter' => true,
            ])

            // Filtre IP (pour les investigations de sécurité)
            ->add('ipAddress', StringFilter::class, [
                'label' => 'audit.ip_address',
                'translation_domain' => 'logger',
                'field_type' => TextType::class,
            ])

            // Filtre Route
            ->add('route', StringFilter::class, [
                'label' => 'audit.route',
                'translation_domain' => 'logger',
                'field_type' => TextType::class,
            ])

            // Filtre Méthode
            ->add('method', ChoiceFilter::class, [
                'label' => 'audit.method',
                'translation_domain' => 'logger',
                'field_type' => ChoiceType::class,
                'show_filter' => true,
                'field_options' => [
                    'choices' => [
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'DELETE' => 'DELETE',
                        'PATCH' => 'PATCH',
                        'HEAD' => 'HEAD',
                        'OPTIONS' => 'OPTIONS',
                        'TRACE' => 'TRACE',
                        'CONNECT' => 'CONNECT',
                        'PURGE' => 'PURGE'
                    ],
                    'choice_translation_domain' => 'httpMethod'
                ],
            ])
           
            // // Filtre par Contexte Utilisateur (via ID, si connu)
            // // Nécessite une configuration ORM spéciale ou un filtre personnalisé pour rechercher dans le JSONB
            // ->add('userContext.id', StringFilter::class, [
            //     'label' => 'audit.user_id',
            //     'global_search' => true,
            //     // NOTE: La recherche dans JSONB par Doctrine peut être complexe,
            //     // ce filtre fonctionne si Doctrine est correctement configuré (avec DQL).
            // ])
        ;
    }

    /**
     * @return array<string, string>
     */
    private function getActionChoices(): array
    {
        $choices = [];
        // Clé => Valeur stockée
        foreach (ActivityAction::cases() as $case) {
            $choices[$case->name] = $case->value;
        }
        return $choices;
    }

    // Les logs doivent être immuables (une fois écrits, ils ne bougent plus)
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'sonata_admin_logger_activity_log_user';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'logger_activity_log_user';
    }
}
