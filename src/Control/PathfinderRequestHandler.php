<?php

namespace CodeCraft\Pathfinder\Control;

use CodeCraft\Pathfinder\Extension\PathfinderControllerExtension;
use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;

/**
 * A handler for the contiguous requests of a user proceeding through a Pathfinder
 */
class PathfinderRequestHandler extends RequestHandler
{
    /**
     * @var Pathfinder
     */
    protected $dataRecord;

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * Setup the pathfinder handler
     *
     * @param Pathfinder $pathfinder
     * @param Controller $controller
     */
    public function __construct(Pathfinder $pathfinder, Controller $controller)
    {
        $this->dataRecord = $pathfinder;

        if (!$controller->hasExtension(PathfinderControllerExtension::class)) {
            throw new \Exception(sprintf('Controller must have the "%s"', PathfinderControllerExtension::class));
        }

        $this->controller = $controller;

        parent::__construct();
    }

    /**
     * Returns the associated database record. Borrows this convention from
     * {@see ContentController}
     */
    public function data()
    {
        return $this->dataRecord;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }
}
