<?php

namespace CodeCraft\Pathfinder\Model;

use CodeCraft\Pathfinder\Control\PathfinderPageController;
use CodeCraft\Pathfinder\Extension\PathfinderDataExtension;
use Page;

/**
 * A page with a Pathfinder
 *
 * @mixin PathfinderDataExtension
 */
class PathfinderPage extends Page
{

    /**
     * @var string
     */
    private static $controller_name = PathfinderPageController::class;

    /**
     * @var string
     */
    private static $table_name = 'PathfinderPage';

    /**
     * {@see DataObject::$singular_name}
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Find a Path Page';

    /**
     * {@see DataObject::$plural_name}
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Find a Path Pages';

    /**
     * @var string
     */
    private static $description = 'A page with a Pathfinder to lead users to content';

    /**
     * @var string
     */
    private static $extensions = [
        PathfinderDataExtension::class,
    ];

    /**
     * {@see SiteTree::$icon_class}
     */
    private static $icon_class = 'font-icon-tree';
}
