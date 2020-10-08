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
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Versioned\VersionedGridFieldState\VersionedGridFieldState;
use SilverStripe\View\ViewableData;

/**
 * The fundamental object for a Pathfinder
 *
 * @property string Title
 * @property string StartContent
 * @property string StartButtonText
 * @property string ResultsFoundContent
 * @property string ResultsNotFoundContent
 * @property string SupportContent
 * @method HasManyList|Question[] Questions()
 * @method HasManyList|Flow[] Flows()
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
        // Let's assume that the current controller is the page as a controller
        /** @var PathfinderPage $page */
        $page = Controller::curr()->data();

        if (!$page) {
            return $content;
        }

        return sprintf(
            '<a href="%s" title="%s">%s</a>',
            $page->Link(Controller::join_links(
                PathfinderPageController::config()->get('pathfinder_url_segment'),
                'reset'
            )),
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
                        'QuestionText' => function($record, $column, $grid) {
                            return TextField::create($column);
                        },
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
    public function forTemplate()
    {
        return $this->renderWith($this->getViewerTemplates());
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
}
