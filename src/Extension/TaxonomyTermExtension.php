<?php

namespace CodeCraft\Pathfinder\Extension;

use CodeCraft\Pathfinder\Model\Choice;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Taxonomy\TaxonomyTerm;

/**
 * An extension for {@see TaxonomyTerm}
 *
 * @property TaxonomyTerm owner
 */
class TaxonomyTermExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'Choices' => Choice::class,
    ];
}
