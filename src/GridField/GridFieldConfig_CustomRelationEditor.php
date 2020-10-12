<?php

namespace CodeCraft\Pathfinder\GridField;

use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Versioned\VersionedGridFieldState\VersionedGridFieldState;
use SilverStripe\Versioned\VersionedGridFieldStateExtension;

/**
 * A customised relation editor config for managing Pathfinder content
 */
class GridFieldConfig_CustomRelationEditor extends GridFieldConfig
{
    /**
     * {@inheritDoc}
     */
    public function __construct($addButtonTitle = 'Add', $orderable = false)
    {
        parent::__construct();

        $this->removeComponentsByType([
            GridFieldAddExistingAutocompleter::class,
        ]);

        if (class_exists('Symbiote\GridFieldExtensions\GridFieldExtensions')) {
            $this->addComponents([
                    new GridFieldButtonRow('after'),
                    new GridFieldToolbarHeader(),
                    (new \Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton('buttons-after-left'))
                        ->setTitle($addButtonTitle),
                    new \Symbiote\GridFieldExtensions\GridFieldEditableColumns(),
                    new GridFieldEditButton(),
                    new GridFieldDeleteAction(),
                    new GridField_ActionMenu(),
                    new GridFieldDetailForm(),
                    (new VersionedGridFieldState())
                        ->setColumn('QuestionText')
                ]);

            if ($orderable) {
                $this->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows());
            }
        }

        $this->extend('updateConfig');
    }

    /**
     * {@see \Symbiote\GridFieldExtensions\GridFieldEditableColumns::setDisplayFields()}
     *
     * @param array
     * @return GridFieldConfig_CustomRelationEditor $this
     */
    public function setDisplayFields($fields)
    {
        if (!class_exists('Symbiote\GridFieldExtensions\GridFieldExtensions')) {
            return $this;
        }

        /** @var \Symbiote\GridFieldExtensions\GridFieldEditableColumns $editableColumns */
        $editableColumns = $this->getComponentByType(\Symbiote\GridFieldExtensions\GridFieldEditableColumns::class);

        if (!$editableColumns) {
            return $this;
        }

        $editableColumns->setDisplayFields($fields);

        return $this;
    }
}
