<?php

namespace CodeCraft\Pathfinder\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\Taxonomy\TaxonomyTerm;
use SilverStripe\Versioned\Versioned;

/**
 * The choices for a Pathfinder's answer. One or more of these makes an answer single or multi-choice
 *
 * @property string ChoiceText
 * @property int Sort
 * @method Answer|null Answer()
 * @method ManyManyList|TaxonomyTerm[] Terms()
 */
class Choice extends DataObject
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
    private static $table_name = 'PathfinderChoice';

    /**
     * @var array
     */
    private static $db = [
        'ChoiceText' => 'Text',
        'Sort' => 'Int',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Answer' => Answer::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Terms' => TaxonomyTerm::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'ChoiceText' => 'Choice',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Sort';

    /**
     * @return Pathfinder|null
     */
    public function getPathfinder()
    {
        return $this->Answer() ? $this->Answer()->getPathfinder() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getCMSFields()
    {
        // Manipulate fields ahead of extension manipulations (such as Fluent)
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'AnswerID',
                'Sort',
                'Terms',
            ]);

            // Terms field
            $fields->insertAfter(
                'ChoiceText',
                TreeMultiselectField::create(
                    'Terms',
                    _t(__CLASS__ . '.Terms', 'Terms'),
                    TaxonomyTerm::class
                )
                    ->setDescription(_t(__CLASS__ . '.TermsDescription', 'Click to search for additional terms'))
                    ->setRightTitle('(Optional)')
            );
        });

        return parent::getCMSFields();
    }

    /**
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return RequiredFields::create([
            'ChoiceText',
        ]);
    }

    /**
     * A string representation of the terms for this Choice
     *
     * @return string
     */
    public function getTermsSummary()
    {
        if (!$this->Terms()->count()) {
            return '(None)';
        }

        return implode(', ', $this->Terms()->column('Name'));
    }

    /**
     * A title for the CMS to use
     *
     * @return string
     */
    public function getTitle()
    {
        /** @var DBText $field */
        $field = DBField::create_field('Text', $this->ChoiceText);

        return $field->LimitCharactersToClosestWord(30);
    }
}
