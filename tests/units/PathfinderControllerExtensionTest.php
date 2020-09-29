<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Dev\FunctionalTest;

/**
 * Testing the extension of controllers associated with models with a Pathfinder
 */
class PathfinderControllerExtensionTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/fixture-pathfinder.yml';

    /**
     * @return void
     */
    public function testPath()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        // Let's assume PathfinderPage has auto-created a Pathfinder
        $controller = ModelAsController::controller_for($page);

        $this->assertInstanceOf(
            Pathfinder::class,
            $controller->path()
        );
    }
}
