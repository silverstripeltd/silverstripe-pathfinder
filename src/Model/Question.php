<?php

namespace CodeCraft\Pathfinder\Model;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBString;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Versioned\Versioned;

/**
 * A question used as a step in a Pathfinder
 *
 * @property string QID
 * @property string QuestionText
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
        'QID' => 'Varchar',
        'QuestionText' => 'Text',
        'Description' => 'HTMLText',
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
        'QuestionText' => 'Question',
        'Flow.Title' => 'Flow',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Sort';

    /**
     * {@inheritDoc}
     */
    public function onAfterDelete()
    {
        // Establish whether we should clean up
        $cleanUp = true;

        // Allow implementors to apply alternative scenarios
        $this->invokeWithExtensions('updateCleanUp', $cleanUp);

        if ($cleanUp) {
            $this->Answers()->removeAll();
        }

        parent::onAfterDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        if (!$this->Answers()->count()) {
            // Create a default answer, to avoid an empty view in the CMS
            $answer = Answer::create();
            $this->Answers()->add($answer);
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
                'PathfinderID',
                'QuestionID',
                'Sort',
                'FromAnswers',
            ]);

            if (!Flow::get()->count()) {
                $flowField = $fields->fieldByName('Root.Main.FlowID');

                if ($flowField) {
                    $flowField->setDisabled(true);
                }
            }

            // Question identifider field
            $qidField = $fields->dataFieldByName('QID');

            $qidField
                ->setTitle('Question identifider')
                ->setAttribute('placeholder', $this->getDefaultQID())
                ->setDescription('Leave blank to use default');

            // Quesiton text field
            $fields->replaceField(
                'QuestionText',
                TextField::create('QuestionText')
            );

            // Description field
            /** @var HTMLEditorField $descriptionField */
            $descriptionField = $fields->dataFieldByName('Description');

            if ($descriptionField) {
                $descriptionField
                    ->setRows(5)
                    ->setRightTitle('(Optional)');
            }

            // Flow field
            /** @var DropdownField $flowField */
            $flowField = $fields->dataFieldByName('FlowID');

            if ($flowField) {
                // Restrict the range to only Flows for this question's Pathfinder
                $flowField
                    ->setSource($this->Pathfinder()->Flows())
                    ->setEmptyString('Use default Flow');
            }

            // Answers field
            /** @var GridField $answersField */
            $answersField = $fields->dataFieldByName('Answers');

            if ($answersField) {
                $config = GridFieldConfig::create()
                    ->addComponents([
                        new GridFieldToolbarHeader(),
                        new GridFieldSortableHeader(),
                        new GridFieldDataColumns(),
                        new GridFieldEditButton(),
                        new GridFieldDeleteAction(true),
                        new GridField_ActionMenu(),
                        new GridFieldDetailForm(),
                        new GridFieldButtonRow('after'),
                        new GridFieldAddNewButton('buttons-after-left'),
                    ]);

                if (class_exists('Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                    $config->addComponents([
                            new \Symbiote\GridFieldExtensions\GridFieldOrderableRows()
                        ]);
                }

                $answersField->setConfig($config);

                $fields->removeByName('Answers');

                $fields->insertAfter('FlowID', $answersField);
            }

            if (!$this->isInDB()) {
                // No options message
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'NoRecordMsg',
                        '<div class="alert alert-info">' .
                        'Answers can be added once this Question is created.' .
                        '</div>'
                    )
                );
            }

            if (!$this->Answers()->count()) {
                // No answers message
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'NoAnswersMsg',
                        '<div class="alert alert-warning">' .
                        'This Question will not be displayed until it has at least one Answer' .
                        '</div>'
                    )
                );
            }
        });

        return parent::getCMSFields();
    }

    /**
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return RequiredFields::create([
            'QuestionText',
        ]);
    }

    /**
     * A title for the CMS to use
     *
     * @return string
     */
    public function getTitle()
    {
        /** @var DBText $field */
        $field = DBField::create_field('Text', $this->getCMSTitle());

        return $field->LimitCharactersToClosestWord(50);
    }

    /**
     * Establish all questions that preceeded this question, including this question
     *
     * @return DataList
     */
    public function recursivePrecedentIDs($precedents = [])
    {
        $precedents[] = $this->ID;

        if ($this->FromAnswers()->count()) {
            foreach ($this->FromAnswers() as $answer) {
                // Continue the walk
                $precedents = array_merge($precedents, $answer->Question()->recursivePrecedentIDs($precedents));
            }
        }

        return array_unique($precedents);
    }

    /**
     * @return string
     */
    public function getFlowTitle()
    {
        return $this->Flow()->exists() ? $this->Flow()->Title : 'Default';
    }

    /**
     * A title that's helpful for CMS users
     */
    public function getCMSTitle()
    {
        $parts = [
            $this->QuestionText,
            ' (Q: ',
            $this->QID ?: $this->getDefaultQID()
        ];

        if ($this->Flow()) {
            $parts[] = sprintf(', in Flow: %s', $this->Flow()->Title);
        }

        $parts[] = ')';

        return implode('', $parts);
    }

    /**
     * Useful for identifying this question, (e.g {@see Pathfinder::getCMSFields()})
     *
     * @return string
     */
    public function getCMSID()
    {
        return sprintf('Q: %s', $this->getDefaultQID());
    }

    /**
     * @return string
     */
    public function getDefaultQID()
    {
        return $this->ID;
    }
}
