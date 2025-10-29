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

use App\Domain\Log\Enum\ActivityAction;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimeRangeType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use App\Infrastructure\Admin\WlindablaAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Infrastructure\Doctrine\Entity\Log\ActivityLogEntity;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
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
        'security_handler' => ActivityLogSecurityHandler::class
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

    // ----------------------------------------------------------------
    // 1. Vue Liste (ListMapper)
    // ----------------------------------------------------------------
    protected function configureListFields(ListMapper $list): void
    {
        $list
            // Audit/ID
            ->add('id', FieldDescriptionInterface::TYPE_IDENTIFIER, [
                'label' => 'audit.id',
            'translation_domain' => 'logger',
            ])

            // Qui (Utilisateur)
            ->add('userContext', FieldDescriptionInterface::TYPE_ARRAY, [
                'label' => 'audit.user',
                'template' => 'bundles/SonataAdminBundle/CRUD/list_user_context.html.twig',
                 'translation_domain' => 'logger',
            ])

            // Quoi (Action)
            // ->add('action', FieldDescriptionInterface::TYPE_STRING, [
            //     'label' => 'audit.action',
            //     'header_style' => 'width: 15%; text-align: center',
            //     'row_align' => 'center',
            // 'translation_domain' => 'logger',
            // ])

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
            ])

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

    // ----------------------------------------------------------------
    // 2. Vue Détail (ShowMapper)
    // ----------------------------------------------------------------

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Identité de l\'Action', ['class' => 'col-md-5'])
            ->add('id', FieldDescriptionInterface::TYPE_INTEGER, ['label' => 'audit.id'])
            ->add('createdAt', FieldDescriptionInterface::TYPE_DATETIME, ['label' => 'audit.created_at', 'format' => 'd/m/Y H:i:s'])
            ->add('action', FieldDescriptionInterface::TYPE_STRING, ['label' => 'audit.action'])
            ->add('method', FieldDescriptionInterface::TYPE_STRING, ['label' => 'audit.method'])
            ->end()

            ->with('Auteur', ['class' => 'col-md-7'])
            // Le Contexte Utilisateur est essentiel ici (qui a fait quoi)
            ->add('userContext', FieldDescriptionInterface::TYPE_ARRAY, [
                'label' => 'audit.user_details',
                // On peut aussi utiliser un template ici pour un rendu plus propre du JSON
            ])
            ->add('ipAddress', FieldDescriptionInterface::TYPE_STRING, ['label' => 'audit.ip_address'])
            ->end()

            ->with('Contexte Technique & Changements', ['class' => 'col-md-12'])
            ->add('route', FieldDescriptionInterface::TYPE_STRING, ['label' => 'audit.route'])
            // Le Contexte (données supplémentaires, user-agent, route params, changes)
            ->add('context', FieldDescriptionInterface::TYPE_ARRAY, [
                'label' => 'audit.context_data',
                // Rendre le JSON lisible avec des <pre>
                'template' => 'admin/activity_log/show_json_data.html.twig',
            ])
            ->end()
        ;
    }

    // ----------------------------------------------------------------
    // 3. Filtres Grille de Données (DatagridMapper)
    // ----------------------------------------------------------------

   /* protected function configureDatagridFilters(DatagridMapper $datagrid): void
    {
        $datagrid
            ->add('createdAt', DateTimeRangeFilter::class, [
            'label' => 'audit.created_at',
                'field_type' => DateTimeRangeType::class,
               
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
                'field_type' => ChoiceType::class,
                'field_options' => [
                    // Générer les choix à partir de l'Enum (valeurs de 'PAGE_VIEW', 'LOGIN_SUCCESS', etc.)
                    'choices' => $this->getActionChoices(),
                    'choice_translation_domain' => 'audit', // Traduire les clés d'actions si nécessaire
                ],
            ])

            // Filtre IP (pour les investigations de sécurité)
            ->add('ipAddress', StringFilter::class, [
                'label' => 'audit.ip_address',
            ])

            // Filtre Route
            ->add('route', StringFilter::class, [
                'label' => 'audit.route',
            ])

            // Filtre Méthode
            ->add('method', ChoiceFilter::class, [
                'label' => 'audit.method',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    // Les méthodes sont fixes (GET, POST, PUT, DELETE)
                    'choices' => ['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'],
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
    }*/

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
