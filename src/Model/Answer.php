<?php

namespace CodeCraft\Pathfinder\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * An answer, presented to users to progress to the next step of a Pathfinder
 *
 * @property string Answer
 * @property int Sort
 * @method Question|null Question()
 * @method HasManyList|Option[] Options()
 * @method ManyManyList|Question[] NextQuestions()
 */
class Answer extends DataObject
{

    /**
     * @var array
     */
    private static $extensions = [
        Versioned::class,
    ];

    /**
     * @var string
     */
    private static $table_name = 'PathfinderAnswer';

    /**
     * @var array
     */
    private static $db = [
        'Sort' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Question' => Question::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Options' => Option::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'NextQuestions' => Question::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Options',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'AnswerSummary' => 'Answer',
        'Options.Count' => 'No. of options',
        'NextStepSummary' => 'Goes to',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Sort';

    /**
     * {@inheritDoc}
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        if (!$this->Options()->count()) {
            // Create a default option, to avoid an empty view in the CMS
            $option = Option::create();
            $this->Options()->add($option);
        }
    }
}
