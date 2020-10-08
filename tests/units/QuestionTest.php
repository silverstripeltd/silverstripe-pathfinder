<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Flow;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\Question;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\UnsavedRelationList;

/**
 * Testing a pathfinder's questions
 */
class QuestionTest extends SapphireTest
{

    /**
     * @return void
     */
    public function testPopulateDefaults()
    {
        $question = Question::create();

        $this->assertCount(
            1,
            $question->Answers(),
            'Should populate a default answer'
        );
    }

    /**
     * @return void
     */
    public function testSmoke()
    {
        $pathfinder = Pathfinder::create();
        $flow = Flow::create();

        $question = Question::create([
            'PathfinderID' => $pathfinder->ID,
            'FlowID' => $flow->ID,
            'QuestionText' => 'Foo bar?',
        ]);

        $this->assertInstanceOf(
            Pathfinder::class,
            $question->Pathfinder(),
            'Should be able to smoke test Pathfinder()'
        );

        $this->assertInstanceOf(
            Flow::class,
            $question->Flow(),
            'Should be able to smoke test Flow()'
        );

        $this->assertInstanceOf(
            UnsavedRelationList::class,
            $question->Answers(),
            'Should be able to smoke test Answers()'
        );

        $this->assertInstanceOf(
            UnsavedRelationList::class,
            $question->FromAnswers(),
            'Should be able to smoke test FromAnswers()'
        );

        $this->assertInstanceOf(
            FieldList::class,
            $question->getCMSFields(),
            'Should be able to smoke test getCMSFields()'
        );

        $this->assertInstanceOf(
            RequiredFields::class,
            $question->getCMSValidator(),
            'Should be able to smoke test getCMSValidator()'
        );

        $this->assertNotEmpty(
            $question->getTitle(),
            'Should be able to smoke test getTitle()'
        );

        $question->write();
        $question->delete();

        $this->assertFalse(
            $question->isInDB(),
            'Should be able to smoke test onAfterDelete()'
        );
    }
}
