<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Answer;
use CodeCraft\Pathfinder\Model\Flow;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use CodeCraft\Pathfinder\Model\Question;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\UnsavedRelationList;

/**
 * Testing a pathfinder question's answer
 */
class AnswerTest extends SapphireTest
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
        $answer = Answer::create();

        $this->assertCount(
            1,
            $answer->Choices(),
            'Should populate a default choice'
        );
    }

    /**
     * @return void
     */
    public function testGetPathfinder()
    {
        $answer = Answer::create();

        $this->assertInstanceOf(
            Pathfinder::class,
            $answer->getPathfinder(),
            'Should scaffold a pathfinder'
        );

        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $question = Question::create([
            'PathfinderID' => $page->Pathfinder()->ID,
        ]);
        $question->write();

        $answer->QuestionID = $question->ID;

        $this->assertSame(
            $page->Pathfinder()->ID,
            $answer->getPathfinder()->ID,
            'Should access related question\'s pathfinder'
        );
    }

    /**
     * @return void
     */
    public function testGetGoesToSummary()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        // Create this here instead of the fixture to take advantage of the auto-created pathfinder
        $nextQuestion = Question::create([
            'PathfinderID' => $page->Pathfinder()->ID,
        ]);
        $nextQuestion->write();

        $answer = Answer::create();

        $this->assertSame(
            Answer::singleton()->getGoToResultsTitle(),
            $answer->getGoesToSummary(),
            'Should indicate the answer goes to the results title'
        );

        $answer->NextQuestions()->add($nextQuestion);

        $this->assertSame(
            $nextQuestion->getCMSTitle(),
            $answer->getGoesToSummary(),
            'Should indicate the answer goes to the next question'
        );
    }

    /**
     * @return void
     */
    public function testGetGoesToField()
    {
        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');
        $pathfinder = $page->Pathfinder();

        // Create these here instead of the fixture to take advantage of the auto-created pathfinder
        $expectedFlowTitle1 = 'Flow 1';
        $flow = Flow::create([
            'Title' => $expectedFlowTitle1,
            'PathfinderID' => $pathfinder->ID,
        ]);
        $flow->write();

        $expectedText1 = 'Question 1';
        $question1 = Question::create([
            'QuestionText' => $expectedText1,
            'PathfinderID' => $pathfinder->ID,
        ]);
        $question1->write();

        $expectedText2 = 'Question 2';
        $question2 = Question::create([
            'QuestionText' => $expectedText2,
            'PathfinderID' => $pathfinder->ID,
            'FlowID' => $flow->ID,
        ]);
        $question2->write();

        $answer = Answer::create([
            'QuestionID' => $question1->ID,
        ]);

        $field = $answer->getGoesToField();

        $this->assertInstanceOf(
            DropdownField::class,
            $field,
            'Should produce a field'
        );

        $this->assertSame(
            $answer->getGoToResultsTitle(),
            $field->getEmptyString(),
            'Field should have results title as empty string'
        );

        $this->assertSame(
            [
                'Flow_Default' => 'Default flow',
                3 => 'Question 1 (Q#3, in Flow: Flow 1)',
                'Flow_1' => 'Flow 1',
                4 => 'Question 2 (Q#4, in Flow: Flow 1)',
            ],
            $field->getSource(),
            'Field should have a range of options based on flows and questions'
        );
    }

    /**
     * @return void
     */
    public function testSaveNextQuestionID()
    {
        $question = Question::create();
        $question->write();

        $answer = Answer::create();

        $expected = false;
        try {
            $answer->saveNextQuestionID(999);
        } catch (\Silverstripe\ORM\ValidationException $e) {
            $expected = true;

            $this->assertContains(
                'Question not found with',
                $e->getMessage()
            );
        }

        $this->assertTrue(
            $expected,
            'Should have caught an exception'
        );

        $answer->saveNextQuestionID($question->ID);

        $this->assertContains(
            $question->ID,
            $answer->NextQuestions()->column(),
            'Should add related question to answer'
        );
    }

    /**
     * @return void
     */
    public function testSmoke()
    {
        $answer = Answer::create();

        $this->assertInstanceOf(
            Question::class,
            $answer->Question(),
            'Should be able to smoke test Question()'
        );

        $this->assertInstanceOf(
            UnsavedRelationList::class,
            $answer->Choices(),
            'Should be able to smoke test Choices()'
        );

        $this->assertInstanceOf(
            UnsavedRelationList::class,
            $answer->NextQuestions(),
            'Should be able to smoke test NextQuestions()'
        );

        $this->assertInstanceOf(
            FieldList::class,
            $answer->getCMSFields(),
            'Should be able to smoke test getCMSFields()'
        );

        $this->assertNotEmpty(
            $answer->getTitle(),
            'Should be able to smoke test getTitle()'
        );

        $this->assertFalse(
            $answer->getNextQuestion(),
            'Should be able to smoke test getNextQuestion()'
        );

        $this->assertNotEmpty(
            $answer->getAnswerSummary(),
            'Should be able to smoke test getAnswerSummary()'
        );

        $this->assertNotEmpty(
            $answer->getGoToResultsTitle(),
            'Should be able to smoke test getGoToResultsTitle()'
        );

        $answer->write();
        $answer->delete();

        $this->assertFalse(
            $answer->isInDB(),
            'Should be able to smoke test onAfterDelete()'
        );
    }

}
