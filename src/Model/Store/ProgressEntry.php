<?php

namespace CodeCraft\Pathfinder\Model\Store;

use SilverStripe\View\ViewableData;

/**
 * A structure for progress entires in a {@see ProgressStore}
 *
 * @property int QuestionID
 * @property int AnswerID
 * @property array ChoiceIDs
 */
class ProgressEntry extends ViewableData
{

    /**
     * Declare a "scaffold" for what fields should used. Useful for {@see toArray()}
     *
     * @var array
     */
    private static $data_fields = [
        'QuestionID',
        'AnswerID',
        'ChoiceIDs'
    ];

    public function __construct($data = [])
    {
        parent::__construct();

        if (is_array($data) && count($data)) {
            foreach($data as $field => $value) {
                $this->setField($field, $value);
            }
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];

        foreach ($this->config()->get('data_fields') as $field) {
            $array[$field] = $this->getField($field);
        }

        return $array;
    }
}
