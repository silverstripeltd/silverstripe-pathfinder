<?php

namespace MSD\App\Admins;

use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\LiteralField;

/**
 * A quick reference for all existing Pathfinders in the site tree
 */
class PathfinderModelAdmin extends ModelAdmin
{

    /**
     * @var array
     */
    private static $managed_models = [
        Pathfinder::class,
    ];

    /**
     * @var string
     */
    private static $url_segment = 'pathfinders';

    /**
     * @var string
     */
    private static $menu_title = 'Pathfinders';

    /**
     * @var int
     */
    private static $menu_priority = 3;

    /**
     * @param null $id
     * @param null $fields
     * @return \SilverStripe\Forms\Form
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        // Pathfinder field
        $pathfinderGridField = $form->Fields()->fieldByName($this->sanitiseClassName(Pathfinder::class));

        if ($pathfinderGridField) {
            /** @var GridFieldConfig $gridFieldConfig */
            $gridFieldConfig = $pathfinderGridField->getConfig();

            $gridFieldConfig->removeComponentsByType([
                GridFieldAddNewButton::class
            ]);

            // Create Pathfinder Message
            $form->Fields()->insertAfter(
                $this->sanitiseClassName(Pathfinder::class),
                LiteralField::create(
                    'CreatePathfinderMsg',
                    '<div class="alert alert-info">' .
                        'Pathfinders are created when you author a Pathfinder' .
                    '</div>'
                )
            );
        }

        return $form;
    }
}
