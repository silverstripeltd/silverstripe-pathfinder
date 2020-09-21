<?php

namespace CodeCraft\Pathfinder\Model;

use CodeCraft\Pathfinder\Control\PathfinderRequestHandler;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HasRequestHandler;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\ViewableData;

/**
 * The fundamental object for a Pathfinder
 *
 * @property string Title
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
    public function getCMSFields()
    {
        // TODO: CMS Fields

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
