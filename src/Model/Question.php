<?php

namespace CodeCraft\Pathfinder\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * A question used as a step in a Pathfinder
 *
 * @property string Question
 * @property int Sort
 * @method Pathfinder|null Pathfinder()
 * @method Flow Flow()
 * @method HasManyList|Answer[] Answers()
 * @method ManyManyList|Answer[] FromAnswers()
 */
class Question extends DataObject
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
    private static $table_name = 'PathfinderQuestion';

    /**
     * @var array
     */
    private static $db = [
        'Question' => 'Text',
        'Sort' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Pathfinder' => Pathfinder::class,
        'Flow' => Flow::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Answers' => Answer::class,
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = [
        'FromAnswers' => Answer::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Answers',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Question' => 'Question',
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

        if (!$this->Answers()->count()) {
            // Create a default answer, to avoid an empty view in the CMS
            $option = Answer::create();
            $this->Answers()->add($option);
        }
    }
}
