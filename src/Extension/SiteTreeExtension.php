<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;

/**
 * Adds Pathfinder features to pages in the site tree
 *
 * @property bool HideFromPathfinders
 * @method SiteTree getOwner()
 */
class SiteTreeExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'HideFromPathfinders' => 'Boolean',
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = [
        'ExcludedFromPathfinders' => Pathfinder::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'ExcludedFromPathfinders',
        ]);
    }

    /**
     * {@see SiteTree::getSettingsFields()}
     *
     * @param FieldList $fields
     */
    public function updateSettingsFields($fields)
    {
        $hideField = CheckboxField::create('HideFromPathfinders', 'Hide from Pathfinders?');

        /** @var Tab $settings */
        $settings = $fields->fieldByName('Root.Settings');

        foreach ($settings->getChildren() as $field) {
            if ($field->Title() !== 'Visibility' || !$field instanceof FieldGroup) {
                continue;
            }

            // Add our field to the Visibility group
            $field->push($hideField);
        }
    }
}
