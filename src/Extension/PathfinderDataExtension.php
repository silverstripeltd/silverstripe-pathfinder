<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;

/**
 * Extend a data objects (such as SiteTree) to have a Pathfinder
 *
 * @property DataObject owner
 * @method DataObject getOwner()
 * @method Pathfinder|null Pathfinder()
 */
class PathfinderDataExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_one = [
        'Pathfinder' => Pathfinder::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Pathfinder',
    ];

    /**
     * {@see DataObject::onAfterDelete()}
     */
    public function onAfterDelete()
    {
        if ($this->getOwner()->Pathfinder() && $this->getOwner()->Pathfinder()->ID) {
            // Pathfinder hasn't been deleted yet, so let's clean up
            $this->getOwner()->Pathfinder()->delete();
        }
    }

    /**
     * {@see DataObject::onBeforeWrite()}
     */
    public function onBeforeWrite()
    {
        if (!$this->getOwner()->PathfinderID) {
            // Ensure there is a pathfinder
            $pathfinder = Pathfinder::create();
            $pathfinder->Title = sprintf('%s\'s Pathfinder', $this->getOwner()->Title);
            $pathfinder->write();
            $this->getOwner()->PathfinderID = $pathfinder->ID;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'Pathfinder',
        ]);

        if (!$this->getOwner()->Pathfinder()) {
            // No Pathfinder message
            $fields->addFieldToTab(
                'Root.Pathfinder',
                LiteralField::create(
                    'NoPathfinderMsg',
                    '<div class="alert alert-warning">' .
                    'Pathfinder can be managed after this record is Saved' .
                    '</div>'
                )
            );
        } else {
            // Create a simplified GridField through which to access the Pathfinder
            $pathfinderField = GridField::create(
                'Pathfinder',
                null,
                Pathfinder::get()->byIDs([$this->getOwner()->PathfinderID])
            );
            $pathfinderField->getConfig()
                ->addComponents([
                    new GridFieldEditButton(),
                    new GridFieldDetailForm()
                ]);

            $fields->addFieldToTab('Root.Pathfinder', $pathfinderField);
        }
    }
}
