<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Control\PathfinderRequestHandler;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\View\ViewableData;

/**
 * Testing the Pathfinder model
 */
class PathfinderTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/fixture-pathfinder.yml';

    /**
     * @return void
     */
    public function testResetLinkShortCode()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        // Put our own controller on the stack
        $pageController = ModelAsController::controller_for($page);
        $pageController->getRequest()->setSession($this->session());
        $pageController->pushCurrent();

        $this->assertSame(
            Pathfinder::reset_link([], 'Start again'),
            '<a href="/pathfinder-page/pathfinder/reset" title="Start again">Start again</a>',
            'Reset link should produce expected link attributes and content'
        );
    }

    /**
     * @return void
     */
    public function testGetPage()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $this->assertInstanceOf(
            PathfinderPage::class,
            $page->Pathfinder()->getPage(),
            'A pathfinder should be able to reference its page'
        );
    }

    /**
     * @return void
     */
    public function testGetPageTitleSummary()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');
        $linkedPathfinder = $page->Pathfinder();
        $lonePathfinder = Pathfinder::create();

        $this->assertSame(
            $page->Title,
            $linkedPathfinder->getPageTitleSummary(),
            'A linked pathfinder should know its page\'s title'
        );

        $this->assertSame(
            'Orphaned',
            $lonePathfinder->getPageTitleSummary(),
            'A non-linked pathfinder should indicate it is orphaned'
        );
    }

    /**
     * @return void
     */
    public function testGetRequestHandler()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');
        $pathfinder = $page->Pathfinder();

        // Put our own controller on the stack
        $pageController = ModelAsController::controller_for($page);
        $pageController->getRequest()->setSession($this->session());
        $pageController->pushCurrent();

        $this->assertInstanceOf(
            PathfinderRequestHandler::class,
            $pathfinder->getRequestHandler(),
            'Pathfinder should always have a request handler at the ready'
        );

        $this->assertSame(
            $pathfinder->getRequestHandler()->getController(),
            Controller::curr(),
            'Pathfinder should assign the current controller to its request handler'
        );
    }

    /**
     * Do a superficial run of all methods
     *
     * @return void
     */
    public function testSmoke()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');
        $pathfinder = $page->Pathfinder();

        // Put our own controller on the stack
        $pageController = ModelAsController::controller_for($page);
        $pageController->getRequest()->setSession($this->session());
        $pageController->pushCurrent();

        $this->assertNotEmpty(
            Pathfinder::reset_link([], 'Foo'),
            'Should be able to smoke test reset_link()'
        );

        $this->assertInstanceOf(
            HasManyList::class,
            $pathfinder->Questions(),
            'Should be able to smoke test Questions()'
        );

        $this->assertInstanceOf(
            HasManyList::class,
            $pathfinder->Flows(),
            'Should be able to smoke test Flows()'
        );

        $this->assertInstanceOf(
            FieldList::class,
            $pathfinder->getCMSFields(),
            'Should be able to smoke test getCMSFields()'
        );

        $this->assertFalse(
            $page->canDelete(),
            'Should be able to smoke test canDelete()'
        );

        $this->assertFalse(
            $page->canArchive(),
            'Should be able to smoke test canArchive()'
        );

        $this->assertInstanceOf(
            ViewableData::class,
            $pathfinder->getPage(),
            'Should be able to smoke test getPage()'
        );

        $this->assertNotEmpty(
            $pathfinder->getPageTitleSummary(),
            'Should be able to smoke test getPageTitleSummary()'
        );

        $this->assertNotEmpty(
            $pathfinder->forTemplate(),
            'Should be able to smoke test forTemplate()'
        );

        $this->assertInstanceOf(
            RequestHandler::class,
            $pathfinder->getRequestHandler()
        );

        // Cover on AfterDelete()
        $pathfinder->delete();

        $this->assertFalse(
            $pathfinder->isInDB(),
            'Should be able to smoke test delete()'
        );
    }
}
