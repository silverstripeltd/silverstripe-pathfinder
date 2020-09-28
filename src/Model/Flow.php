<?php


namespace CodeCraft\Pathfinder\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * A flow to give structure to question sequencing in a Pathfinder
 *
 * @property string Title
 * @method Pathfinder|null Pathfinder()
 * @method HasManyList|Question[] Questions()
 */
class Flow extends DataObject
{

    /**
     * @var string
     */
    private static $table_name = 'PathfinderFlow';

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Pathfinder' => Pathfinder::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Questions' => Question::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function getCMSFields()
    {
        // Manipulate fields ahead of extension manipulations (such as Fluent)
        $this->beforeUpdateCMSFields(function (FieldList $fields) {

            $fields->removeByName([
                'PathfinderID',
            ]);

            // Title field
            $titleField = $fields->dataFieldByName('Title');

            if ($titleField) {
                $titleField->setDescription('Only shown in the CMS');
            }
        });

        return parent::getCMSFields();
    }

    /**
     * {@inheritDoc}
     */
    public function populateDefaults()
    {
        // Come up with a helpful title
        if ($this->Pathfinder()) {
            $this->Title = sprintf('Flow %s', $this->Pathfinder()->Flows()->count() + 1);
        }

        return parent::populateDefaults();
    }
}
