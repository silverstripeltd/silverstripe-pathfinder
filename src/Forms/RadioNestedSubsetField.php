<?php

namespace CodeCraft\Pathfinder\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\View\Requirements;

/**
 * A subset of fields displayed in a {@see RadioNestedSetField}
 */
class RadioNestedSubsetField extends CompositeField
{

    /**
     * Should include the `.js` in the filename
     *
     * @var string
     */
    private static $require_js = 'vendor/codecraft/silverstripe-pathfinder/client/dist/js/RadioNestedSubsetField.js';

    /**
     * @var int
     */
    protected $subsetID;

    /**
     * @var bool
     */
    protected $checked = false;

    /**
     * @param string $name
     * @param array|FieldList|null $children
     */
    public function __construct($name, $children = null)
    {
        parent::__construct($children);

        $this->setName($name);

        $requireJs = $this->config()->get('require_js');
        if ($requireJs) {
            Requirements::javascript($requireJs);
        }
    }

    /**
     * @param $id
     * @return RadioNestedSubsetField $this
     */
    public function setSubsetID($id)
    {
        $this->subsetID = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubsetID()
    {
        return $this->subsetID;
    }

    /**
     * {@inheritDoc}
     */
    public function ID()
    {
        return sprintf('%s_%s', parent::ID(), $this->getSubsetID());
    }

    /**
     * @param bool $bool
     */
    public function setChecked($bool) {
        $this->checked = $bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }
}
