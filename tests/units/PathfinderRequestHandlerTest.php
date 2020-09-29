<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Control\PathfinderPageController;
use CodeCraft\Pathfinder\Control\PathfinderRequestHandler;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;

/**
 * Testing request handling for a Pathfinder
 */
class PathfinderRequestHandlerTest extends SapphireTest
{

    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/fixture-pathfinder.yml';

    /**
     * @return void
     */
    public function testConstruct()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $expected = false;
        $pathfinder = $page->Pathfinder();

        try {
            $handler = PathfinderRequestHandler::create($pathfinder, Controller::create());
        } catch (\Exception $e) {
            $expected = true;
            $this->assertContains(
                'Controller must have the',
                $e->getMessage()
            );
        }

        $this->assertTrue(
            $expected,
            'Should have caught an expected Exception'
        );

        $this->assertInstanceOf(
            PathfinderRequestHandler::class,
            PathfinderRequestHandler::create($pathfinder, ModelAsController::controller_for($page)),
            'Should successfully instantiate'
        );
    }

    /**
     * @return void
     */
    public function testData()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');
        $pathfinder = $page->Pathfinder();
        $handler = PathfinderRequestHandler::create($pathfinder, ModelAsController::controller_for($page));

        $this->assertSame(
            $pathfinder,
            $handler->data(),
            'Should store Pathfinder as the handler\'s "data record"'
        );
    }

    /**
     * @return void
     */
    public function testGetController()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $controller = ModelAsController::controller_for($page);
        $handler = PathfinderRequestHandler::create(Pathfinder::create(), $controller);

        $this->assertSame(
            $controller,
            $handler->getController(),
            'Should store the Controller as the handler\'s "controller"'
        );
    }
}
