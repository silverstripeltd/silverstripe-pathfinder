<?php

namespace CodeCraft\Pathfinder\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;

/**
 * Displays fields, grouped beneath a set of radio fields, which the intention that only
 * one nested group can be used in the submission
 */
class RadioNestedSetField extends CompositeField
{
    /**
     * @param string $name
     * @param array|FieldList|null $children
     */
    public function __construct($name, $children = null)
    {
        parent::__construct($children);

        $this->setName($name);
    }
}
