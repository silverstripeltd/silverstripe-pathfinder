<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Control\PathfinderPageController;
use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Core\Extension;

/**
 * Extend a controller to support an interactive Pathfinder
 *
 * Let's also typehint the model, because SS doesn't that inherently yet
 * @property Controller|PathfinderDataExtension owner
 */
class PathfinderControllerExtension extends Extension
{

    /**
     * @var array
     */
    private static $allowed_actions = [
        'path',
    ];

    /**
     * @var string
     */
    private static $pathfinder_url_segment = 'path';

    /**
     * @return Pathfinder
     */
    public function path()
    {
        return $this->owner->Pathfinder();
    }
}
