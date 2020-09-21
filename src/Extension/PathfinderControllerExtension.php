<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Core\Extension;

/**
 * Extend a controller to support an interactive Pathfinder
 *
 * @property PathfinderPageController owner
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
