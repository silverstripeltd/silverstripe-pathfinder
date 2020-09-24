<?php

namespace CodeCraft\Pathfinder\Tests\Units;

use CodeCraft\Pathfinder\Model\Answer;
use CodeCraft\Pathfinder\Model\Choice;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\PathfinderPage;
use CodeCraft\Pathfinder\Model\Question;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Taxonomy\TaxonomyTerm;

/**
 * Testing a pathfinder question answer's choice
 */
class ChoiceTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/fixture-pathfinder.yml';

    /**
     * @return void
     */
    public function testGetPathfinder()
    {
        $choice = Choice::create();

        $this->assertInstanceOf(
            Pathfinder::class,
            $choice->getPathfinder(),
            'Should scaffold a pathfinder'
        );

        /** @var PathfinderPage $page */
        $page = $this->objFromFixture(PathfinderPage::class, 'page');

        $question = Question::create([
            'PathfinderID' => $page->Pathfinder()->ID,
        ]);
        $question->write();

        $answer = Answer::create([
            'QuestionID' => $question->ID,
        ]);
        $answer->write();

        $choice->AnswerID = $answer->ID;

        $this->assertSame(
            $page->Pathfinder()->ID,
            $choice->getPathfinder()->ID,
            'Should access related answer\'s pathfinder'
        );
    }

    /**
     * @return void
     */
    public function testGetTermsSummary()
    {
        $choice = Choice::create();

        $this->assertSame(
            '(None)',
            $choice->getTermsSummary(),
            'Should indicate there are no terms'
        );

        $term1 = TaxonomyTerm::create([
            'Name' => 'Foo',
        ]);

        $choice->Terms()->add($term1);

        $term2 = TaxonomyTerm::create([
            'Name' => 'Bar',
        ]);

        $choice->Terms()->add($term2);

        $this->assertSame(
            'Foo, Bar',
            $choice->getTermsSummary(),
            'Should indicate there are no terms'
        );
    }

    /**
     * @return void
     */
    public function testSmoke()
    {
        $choice = Choice::create([
            'ChoiceText' => 'Foo bar',
        ]);

        $this->assertNotEmpty(
            $choice->getTitle(),
            'Should be able to smoke test getTitle()'
        );
    }
}
