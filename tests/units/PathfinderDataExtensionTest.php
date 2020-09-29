<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

/**
 * Testing the Pathfinder data extension
 */
class PathfinderDataExtensionTest extends SapphireTest
{

    /**
     * @var bool
     */
    protected $usesDatabase = true;

    /**
     * @return void
     */
    public function testOnBeforeWrite()
    {
        $obj = PathfinderPage::create();
        $obj->write();

        $this->assertInstanceOf(
            Pathfinder::class,
            $obj->Pathfinder()
        );

        $currentCount = Pathfinder::get()->count();

        // Test indempotency
        $obj->write();

        $this->assertCount(
            $currentCount,
            Pathfinder::get(),
            'Only one Pathfinder should have been created (in the context of this test)'
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testOnAfterDelete()
    {
        $obj = PathfinderPage::create();
        $obj->write();

        $this->assertTrue(
            $obj->Pathfinder()->isInDB(),
            'A Pathfinder should exist, so we can baseline the test'
        );

        $obj->delete();

        $this->assertFalse(
            $obj->Pathfinder()->isInDB(),
            'Should delete dependant Pathfinder'
        );
    }

    /**
     * @return void
     */
    public function testSmoke()
    {
        $obj = PathfinderPage::create();
        $obj->write();

        $this->assertTrue(
            $obj->isInDB(),
            'Should be able to smoke test write()'
        );

        $this->assertInstanceOf(
            FieldList::class,
            $obj->getCMSFields()
        );

        $obj->delete();

        $this->assertFalse(
            $obj->isInDB(),
            'Should be able to smoke test delete()'
        );
    }
}
