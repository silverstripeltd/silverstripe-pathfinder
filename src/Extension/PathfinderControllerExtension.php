<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Control\PathfinderPageController;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\Question;
use CodeCraft\Pathfinder\Model\Store\ProgressEntry;
use CodeCraft\Pathfinder\Model\Store\ProgressStore;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

/**
 * Extend a controller to support an interactive Pathfinder
 *
 * Let's also typehint the model, because SS doesn't that inherently yet
 *
 * @property \Controller|PathfinderDataExtension owner
 */
class PathfinderControllerExtension extends Extension
{

    /**
     * Because {@see Pathfinder} is has a request handler, we can allow requests
     * directly to the page's Pathfinder relation data object for it to handle
     * itself (similar to a {@see Form})
     *
     * @var array
     */
    private static $allowed_actions = [
        'Pathfinder',
    ];
}
