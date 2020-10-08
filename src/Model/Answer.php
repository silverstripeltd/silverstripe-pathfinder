<?php

namespace CodeCraft\Pathfinder\Model;

use CodeCraft\Pathfinder\GridField\GridFieldConfig_CustomRelationEditor;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * An answer, presented to users to progress to the next step of a Pathfinder
 *
 * @property string Answer
 * @property int Sort
 * @method Question|null Question()
 * @method HasManyList|Choice[] Choices()
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
        'Choices' => Choice::class,
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
        'Choices',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'AnswerSummary' => 'Answer',
        'Choices.Count' => 'No. of choices',
        'GoesToSummary' => 'Goes to',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Sort';

    /**
     * @var string
     */
    private static $go_to_results_title = 'Results';

    /**
     * {@inheritDoc}
     */
    public function onAfterDelete()
    {
        $this->Choices()->removeAll();

        parent::onAfterDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        if (!$this->Choices()->count()) {
            // Create a default choice, to avoid an empty view in the CMS
            $choice = Choice::create();
            $choice->ChoiceText = 'None of the above';
            $this->Choices()->add($choice);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCMSFields()
    {
        // Manipulate fields ahead of extension manipulations (such as Fluent)
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'Sort',
                'QuestionID',
                'Terms',
                'NextQuestions',
            ]);

            // Options field
            /** @var GridField $choicesField */
            $choicesField = $fields->dataFieldByName('Choices');

            if ($choicesField) {
                $config = GridFieldConfig_CustomRelationEditor::create('Add Choice', true)
                    ->setDisplayFields([
                        'ChoiceText' => function($record, $column, $grid) {
                            return TextField::create($column);
                        },
                    ]);

                $choicesField->setConfig($config);

                // Remove the tab and existing field placement
                $fields->removeByName('Choices');
                // Re-add the field
                $fields->addFieldToTab('Root.Main', $choicesField);
            }

            // Goes to (next question) field
            $goesToField = $this->getGoesToField()
                ->setValue($this->getNextQuestion() ? $this->getNextQuestion()->ID : null);

            $fields->insertBefore('Choices', $goesToField);

            if (!$this->isInDB()) {
                // No options message
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'NoRecordMsg',
                        '<div class="alert alert-info">' .
                        'Answer must be created, before Choices can be added.' .
                        '</div>'
                    )
                );
            }

            if (!$this->Choices()->count()) {
                // No options message
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'NoAnswersMsg',
                        '<div class="alert alert-warning">' .
                        'This Answer will not be displayed until it has at least one Choices' .
                        '</div>'
                    )
                );
            }
        });

        return parent::getCMSFields();
    }

    /**
     * A title for the CMS to use
     *
     * @return string
     */
    public function getTitle()
    {
        /** @var DBText $field */
        $field = DBField::create_field('Text', "Answer: {$this->getAnswerSummary()}");

        return $field->LimitCharactersToClosestWord(30);
    }

    /**
     * @return Pathfinder|null
     */
    public function getPathfinder()
    {
        return $this->Question() ? $this->Question()->Pathfinder() : null;
    }

    /**
     * The first Question in the NextQuestions relation
     *
     * @return Question|null
     */
    public function getNextQuestion()
    {
        /** @var Question|null $question */
        $question = $this->NextQuestions()->first();

        return $question;
    }

    /**
     * @return string
     */
    public function getGoesToSummary()
    {
        return $this->NextQuestions()->count()
            ? $this->getNextQuestion()->getTitle()
            : $this->getGoToResultsTitle();
    }

    /**
     * @return DBField
     */
    public function getAnswerSummary()
    {
        $field = DBField::create_field(
            'Text',
            implode(' | ', $this->Choices()->column('ChoiceText'))
        );

        return $field->LimitCharactersToClosestWord(60);
    }

    /**
     * @return string
     */
    public function getGoToResultsTitle()
    {
        return $this->config()->get('go_to_results_title');
    }

    /**
     * @return DropdownField
     */
    public function getGoesToField()
    {
        $field = DropdownField::create(
            'NextQuestionID',
            'Goes to'
        );

        // Make it clear that having no next questions goes straight to results
        $field->setEmptyString($this->getGoToResultsTitle());

        $pathfinder = $this->getPathfinder();

        if (!$pathfinder || !$pathfinder->Flows()->count()) {
            // All questions are using the default flow
            $field->setDisabled(true);

            return $field;
        }

        $options = [];
        $disabledItems = [
            $this->Question()->ID,
        ];

        foreach ($pathfinder->Flows() as $flow) {
            $flowValue = sprintf('Flow_%s', $flow->ID);
            // Display the flow for easy reference, but disable it
            $options[$flowValue] = $flow->Title;
            $disabledItems[] = $flowValue;

            foreach ($flow->Questions()->map('ID', 'QuestionText') as $id => $text) {
                // Do this explicitly so keys are preserved
                $options[$id] = $text;
            }
        }

        return $field
            ->setSource($options)
            ->setDisabledItems($disabledItems);
    }

    /**
     * Capture the {@see getGoesToField()}'s input and handle it
     *
     * This method is invoked by {@see Form::saveInto()}
     *
     * @param mixed $value
     */
    public function saveNextQuestionID($value)
    {
        if (!is_int($value)) {
            // We're expecting Question IDs
            return;
        }

        if ($this->NextQuestions()->count()) {
            // We're limiting the many_many to one for now
            $this->NextQuestions()->removeAll();
        }

        if ($value == 0) {
            // Nothing else to do
            return;
        }

        $question = Question::get()->byID($value);

        if (!$question) {
            throw new \Silverstripe\ORM\ValidationException(sprintf('Question not found with ID "%s"', $value));
        }

        // Add the next question
        $this->NextQuestions()->add($question);
    }
}
