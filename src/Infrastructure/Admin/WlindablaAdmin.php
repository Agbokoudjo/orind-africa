<?php
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

// src/Infrastructure/Admin/WlindablaAdmin.php
namespace App\Infrastructure\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Bundle\SecurityBundle\Security;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 * Classe de base pour tous les administrateurs d'entités Doctrine.
 */
abstract class WlindablaAdmin extends AbstractAdmin
{
    protected string $labelShow = 'show';

    protected string $labelEdit = 'Edit';

    protected string $labelList = 'List';

    protected string $labelCreate="Create";

    public function __construct(
        private readonly ?string $label_list_id = null,
        private readonly ?string $label_show_id = null,
        private readonly ?string $label_create_id=null
    ) {
        // Dans les versions récentes, le constructeur de AbstractAdmin est géré
        // par Sonata et n'a pas besoin d'être appelé avec les anciens arguments.
        // On se contente d'initialiser nos propres propriétés.
    }

    public function configure(): void
    {
        // La méthode configure() est un bon endroit pour les initialisations
        // qui dépendent du conteneur de services, comme le ModelClass.
        // Le model_class est maintenant défini via la configuration des services
        // de Symfony, et il est automatiquement injecté.
    }

    protected function preValidate(object $object): void
    {
        // ... (votre code original)
        if (!$this->hasRequest()) {
            return;
        }

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest() && $this->isDynamicFormRequest($request)) {
            return;
        }

        $this->setTranslationDomain('validators');
    }

    public function isDynamicFormRequest(): bool
    {
        return (bool) $this->getRequest()->get('dynamic_form') === true;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDefaultFilterValues(array &$filterValues): void
    {
        $filterValues['_page'] = 1; // La page par défaut
        $filterValues['_per_page'] = 10; // Le nombre d'éléments par page
    }

    /**
     * @phpstan-param ListMapper<T> $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        // $list->add(ListMapper::NAME_BATCH, ListMapper::TYPE_BATCH, [
        //     'label' => 'batch',
        //     'sortable' => false,
        //     'virtual_field' => true,
        //     'template' => $this->getTemplateRegistry()->getTemplate('batch'),
        // ]);
    }

    /**
     * Get the value of labelShow
     */
    public function getLabelShow(): string
    {
        return $this->labelShow;
    }

    /**
     * Set the value of labelShow
     */
    public function setLabelShow(string $labelShow): void
    {
        $this->labelShow = $labelShow;

    }

    /**
     * Get the value of labelEdit
     */
    public function getLabelEdit(): string
    {
        return $this->labelEdit;
    }

    /**
     * Set the value of labelEdit
     */
    public function setLabelEdit(string $labelEdit): self
    {
        $this->labelEdit = $labelEdit;

        return $this;
    }

    /**
     * Get the value of labelList
     */
    final public function getLabelList(): string
    {
        // 1. On vérifie si un identifiant de traduction (label_list_id) a été fourni
        if ($this->label_list_id) {
            // 2. Si oui, on utilise le service de traduction (Translator) pour traduire
            //    cet identifiant.
            $this->labelList = $this->getTranslator()
                ->trans($this->label_list_id, [], 'label_list', $this->getRequest()->getLocale());
        }
        // 3. On retourne le label qui a été traduit ou le label par défaut si aucun
        //    identifiant de traduction n'a été fourni.
        return $this->labelList;
    }

    
    /**
     * Set the value of labelList
     */
    final public function setLabelList(string $labelList): void
    {
        $this->labelList = $labelList;

    }

    /**
     * Get the value of labelCreate
     */
    public function getLabelCreate(): string
    {
        if($this->label_create_id){
            $this->labelCreate = $this->getTranslator()
                ->trans($this->label_create_id, [], 'label_create', $this->getRequest()->getLocale());
        }
        
        return $this->labelCreate;
    }

    /**
     * Set the value of labelCreate
     */
    public function setLabelCreate(string $labelCreate): void
    {
        $this->labelCreate = $labelCreate;

    }

}