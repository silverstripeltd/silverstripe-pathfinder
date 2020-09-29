<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Flow;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\UnsavedRelationList;

/**
 * Testing a Pathfinder's flow model
 */
class FlowTest extends SapphireTest
{

    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/fixture-pathfinder.yml';

    /**
     * @return void
     */
    public function testPopulateDefaults()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $flow = Flow::create([
            // We assign the ID in the way, because the only way to create Flow
            // in the real world is via a GridField
            'PathfinderID' => $page->Pathfinder()->ID,
        ]);

        $this->assertSame(
            'Flow 1',
            $flow->Title
        );
    }

    /**
     * @return void
     */
    public function testSmoke()
    {
        $flow = Flow::create();

        $this->assertInstanceOf(
            Pathfinder::class,
            $flow->Pathfinder(),
            'Should be able to smoke test Pathfinder()'
        );

        $this->assertInstanceOf(
            UnsavedRelationList::class,
            $flow->Questions(),
            'Should be able to smoke test Questions()'
        );

        $this->assertInstanceOf(
            FieldList::class,
            $flow->getCMSFields(),
            'Should be able to smoke test getCMSFields()'
        );
    }
}
