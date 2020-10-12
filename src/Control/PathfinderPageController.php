<?php

namespace CodeCraft\Pathfinder\Control;

use CodeCraft\Pathfinder\Extension\PathfinderControllerExtension;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use PageController;

/**
 * The controller for {@see PathfinderPage}
 *
 * @mixin PathfinderControllerExtension
 */
class PathfinderPageController extends PageController
{
    /**
     * @var string
     */
    private static $extensions = [
        PathfinderControllerExtension::class,
    ];
}
