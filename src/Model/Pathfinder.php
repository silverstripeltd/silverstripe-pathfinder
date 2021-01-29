<?php

namespace CodeCraft\Pathfinder\Model;

use CodeCraft\Pathfinder\Control\PathfinderPageController;
use CodeCraft\Pathfinder\Control\PathfinderRequestHandler;
use CodeCraft\Pathfinder\GridField\GridFieldConfig_CustomRelationEditor;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HasRequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ViewableData;

/**
 * The fundamental object for a Pathfinder
 *
 * @property string Title
 * @property string StartContent
 * @property string StartButtonText
 * @property string ContinueButtonText
 * @property string ResultsFoundContent
 * @property string ResultsNotFoundContent
 * @property string SupportContent
 * @method HasManyList|Question[] Questions()
 * @method HasManyList|Flow[] Flows()
 * @method ManyManyList ExcludedPages()
 */
class Pathfinder extends DataObject implements HasRequestHandler
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
    private static $table_name = 'Pathfinder';

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'StartContent' => 'HTMLText',
        'StartButtonText' => 'Varchar(255)',
        'ContinueButtonText' => 'Varchar(255)',
        'ResultsFoundContent' => 'HTMLText',
        'ResultsNotFoundContent' => 'HTMLText',
        'SupportContent' => 'HTMLText',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Questions' => Question::class,
        'Flows' => Flow::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'ExcludedPages' => SiteTree::class,
    ];

    /**
     * @var array
     */
    private static $owns = [
        'Questions',
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title' => 'Title',
        'PageTitleSummary' => 'Page',
    ];

    /**
     * @var PathfinderRequestHandler
     */
    protected $requestHandler;

    /**
     * The [reset_link][/reset_link] shortcode
     *
     * @param array $arguments
     * @param string|null $content
     *
     * @return string
     */
    public static function reset_link($arguments, $content = null)
    {
        $controller = Controller::curr();

        if (!Controller::curr()->hasMethod('data')) {
            return $content;
        }

        // Let's assume that the current controller is the page as a controller
        /** @var PathfinderPage $page */
        $page = $controller->data();

        if (!$page) {
            return $content;
        }

        return sprintf(
            '<a href="%s" title="%s">%s</a>',
            $page->Pathfinder()->getRequestHandler()->getResetLink(),
            $content,
            $content
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onAfterDelete()
    {
        $this->Questions()->removeAll();
        $this->Flows()->removeAll();

        parent::onAfterDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function getCMSFields()
    {
        // Manipulate fields ahead of extension manipulations (such as Fluent)
        $this->beforeUpdateCMSFields(function (FieldList $fields) {

            $fields->removeByName([
                'ExcludedPages',
            ]);

            // Questions field
            /** @var GridField $questionsField */
            $questionsField = $fields->dataFieldByName('Questions');

            if ($questionsField) {
                $config = GridFieldConfig_CustomRelationEditor::create('Add Question', true)
                    ->setDisplayFields([
                        'QID' => [
                            'title' => 'Question Identifider',
                            'callback' => function($record, $column, $grid) {
                                $placeholder = 'Leave blank to use default';

                                if ($record->ID) {
                                    $placeholder = sprintf('%s (%s)', $record->ID, $placeholder);
                                }

                                return TextField::create($column)
                                    ->setAttribute('placeholder', $placeholder);
                            },
                        ],
                        'QuestionText' => [
                            'title' => 'Question',
                            'field' => TextField::class,
                        ],
                        'FlowTitle' => [
                            'title' => 'Flow',
                            'field' => ReadonlyField::class,
                        ],
                    ]);

                $questionsField->setConfig($config);
            }

            // Flows field
            /** @var GridField $flows */
            $flows = $fields->dataFieldByName('Flows');

            if ($flows) {
                $flows->setConfig(GridFieldConfig_CustomRelationEditor::create('Add Flow'));
            }

            // Start header
            $startHeader = HeaderField::create('StartHeader', 'Start (Introduction)');

            $fields->insertBefore('StartContent', $startHeader);

            // Start content field
            /** @var HTMLEditorField $startContentField */
            $startContentField = $fields->dataFieldByName('StartContent');

            if ($startContentField) {
                $startContentField->setDescription('Displayed as an introduction when the user starts');
            }

            // Button text Field
            /** @var TextField $startButtonTextField */
            $startButtonTextField = $fields->dataFieldByName('StartButtonText');

            if ($startButtonTextField) {
                $startButtonTextField
                    ->setAttribute('placeholder', 'Start')
                    ->setDescription('Leave blank to use default');
            }

            // Button text Field
            /** @var TextField $continueButtonTextField */
            $continueButtonTextField = $fields->dataFieldByName('ContinueButtonText');

            if ($continueButtonTextField) {
                $continueButtonTextField
                    ->setAttribute('placeholder', 'Continue')
                    ->setDescription('Displayed on the start screen when the user has existing progress. Leave blank to use default');
            }

            // Results found header
            $resultsFoundHeader = HeaderField::create('ResultsFoundHeader', 'Results found');

            $fields->insertBefore('ResultsFoundContent', $resultsFoundHeader);

            // Results found message field
            /** @var HTMLEditorField $resultsContentField */
            $resultsContentField = $fields->dataFieldByName('ResultsFoundContent');

            if ($resultsContentField) {
                $resultsContentField
                    ->setDescription(
                        'Displayed when the Pathfinder matches content for the user'
                    )
                    ->setRows(5);
            }

            // Results not found header
            $resultsNotFoundHeader = HeaderField::create('ResultsNotFoundHeader', 'Results not found');

            $fields->insertBefore('ResultsNotFoundContent', $resultsNotFoundHeader);

            // Results not found message field
            /** @var HTMLEditorField $resultsNotFoundContentField */
            $resultsNotFoundContentField = $fields->dataFieldByName('ResultsNotFoundContent');

            if ($resultsNotFoundContentField) {
                $resultsNotFoundContentField
                    ->setDescription(
                        'Displayed when the Pathfinder <strong>does not</strong> match any  content for the user'
                    )
                    ->setRows(5);
            }

            // Support header
            $supportHeader = HeaderField::create('SupportHeader', 'Support');

            $fields->insertBefore('SupportContent', $supportHeader);

            // Support content field
            /** @var HTMLEditorField $supportContentField */
            $supportContentField = $fields->dataFieldByName('SupportContent');

            if ($supportContentField) {
                $supportContentField
                    ->setDescription(
                        'A helpful message alongside the Pathfinder results'
                    )
                    ->setRows(5);
            }

            if (!$this->Questions()->count()) {
                // No questions yet message
                $fields->insertBefore(
                    'Title',
                    LiteralField::create(
                        'NoQuestionsMsg',
                        '<div class="alert alert-warning">' .
                        'Pathfinder is unusable until it has at least one Question' .
                        '</div>'
                    )
                );
            }

            // Excluded pages field
            $excludedField = TreeMultiselectField::create('ExcludedPages', null, SiteTree::class);

            $fields->findOrMakeTab(
                'Root.Results',
                'Results / Suggestions'
            );

            $fields->addFieldsToTab(
                'Root.Results',
                $excludedField
            );
        });

        return parent::getCMSFields();
    }

    /**
     * @param null $member
     * @return bool
     */
    public function canDelete($member = null)
    {
        if ($this->getPage()) {
            return false;
        }

        return parent::canDelete($member);
    }

    /**
     * @param null $member
     * @return bool
     */
    public function canArchive($member = null)
    {
        if (!$this->canDelete()) {
            return false;
        }

        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Get the page this Pathfinder is on. To avoid a two way has_one, we're using
     * a reverse lookup here instead, so that we only need to manage the relation
     * in one place
     *
     * @return ViewableData|null
     */
    public function getPage()
    {
        /** @var PathfinderPage|null $page */
        $page = PathfinderPage::get()->filter(['PathfinderID' => $this->ID])->first();

        return $page;
    }

    /**
     * @return string
     */
    public function getPageTitleSummary()
    {
        $page = $this->getPage();

        if (!$page) {
            return 'Orphaned';
        }

        return $page->Title;
    }

    /**
     * @return string
     */
    public function getStartButtonText()
    {
        return $this->getField('StartButtonText') ?: _t(self::class . '.START_BUTTON_TEXT', 'Start');
    }

    /**
     * @return string
     */
    public function getContinueButtonText()
    {
        return $this->getField('ContinueButtonText') ?: _t(self::class . '.CONTINUE_BUTTON_TEXT', 'Continue');
    }

    /**
     * @return string
     */
    public function forTemplate()
    {
        $variant = '';

        if ($this->getRequestHandler()->getCurrentQuestion()) {
            $variant = '_question';
        }

        if ($this->getRequestHandler()->isComplete()) {
            $variant = '_results';
        }

        // We can render the request handler because it has this model as its
        // failover {@see ViewableData::getFailover()}
        return $this->getRequestHandler()->renderWith($this->getViewerTemplates($variant));
    }

    /**
     * Get request handler for this form
     *
     * @return PathfinderRequestHandler
     */
    public function getRequestHandler()
    {
        if (!$this->requestHandler) {
            $this->requestHandler = PathfinderRequestHandler::create($this, Controller::curr());
        }
        return $this->requestHandler;
    }

    /**
     * @return string
     */
    public function Link()
    {
        return $this->getRequestHandler()->Link();
    }
}
