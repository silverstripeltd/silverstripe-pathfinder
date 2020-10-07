<?php

namespace CodeCraft\Pathfinder\Control;

use CodeCraft\Pathfinder\Extension\PathfinderControllerExtension;
use CodeCraft\Pathfinder\Forms\RadioNestedSetField;
use CodeCraft\Pathfinder\Forms\RadioNestedSubsetField;
use CodeCraft\Pathfinder\Model\Answer;
use CodeCraft\Pathfinder\Model\Choice;
use CodeCraft\Pathfinder\Model\Pathfinder;
use CodeCraft\Pathfinder\Model\Question;
use CodeCraft\Pathfinder\Model\Store\ProgressEntry;
use CodeCraft\Pathfinder\Model\Store\ProgressStore;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataList;
use SilverStripe\Taxonomy\TaxonomyTerm;
use Page;

/**
 * A handler for the contiguous requests of a user proceeding through a Pathfinder
 *
 * @mixin Pathfinder (Uses {@see Pathfinder} as failover data)
 */
class PathfinderRequestHandler extends RequestHandler
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'Form',
        'question',
        'reset',
        'suggestions',
    ];

    /**
     * The model with TaxonomyTerms that can be used for results
     *
     * @var string
     */
    private static $results_model;

    /**
     * @var Pathfinder
     */
    protected $dataRecord;

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var mixed
     */
    protected $results;

    /**
     * @var bool
     */
    protected $complete = false;

    /**
     * @var ProgressStore|null
     */
    protected $store;

    /**
     * Setup the pathfinder handler
     *
     * @param Pathfinder $pathfinder
     * @param Controller $controller
     */
    public function __construct(Pathfinder $pathfinder, Controller $controller)
    {
        $this->dataRecord = $pathfinder;

        if (!$controller->hasExtension(PathfinderControllerExtension::class)) {
            throw new \Exception(sprintf('Controller must have the "%s"', PathfinderControllerExtension::class));
        }

        $this->controller = $controller;

        parent::__construct();

        $this->setFailover($this->dataRecord);
        $this->getStore()->initAfterRequestHandler($this);
    }

    /**
     * Returns the associated database record. Borrows this convention from
     * {@see ContentController}
     */
    public function data()
    {
        return $this->dataRecord;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return ProgressStore
     */
    public function getStore()
    {
        if (!$this->store) {
            $this->store = Injector::inst()->get(ProgressStore::class);

            if (!$this->store) {
                throw new \Exception('Unable to control Pathfinders without a configured store for progress');
            }
        }

        return $this->store;
    }

    /**
     * @return Question|null
     */
    public function getCurrentQuestion()
    {
        $questionId = (int) $this->getRequest()->getVar('id');

        if (!$questionId) {
            return null;
        }

        $stepNum = $this->getCurrentStepNumber();

        if (!$stepNum) {
            return null;
        }

        $question = $this->Questions()->byId($questionId);

        if ($stepNum == 1) {
            // Check we're on the first question
            if ($question->ID !== $this->Questions()->first()->ID) {
                return null;
            }

            // This is the first question
            return $question;
        }

        $store = $this->getStore();

        if ($store->canValidateSequence()) {
            if (!$store->isInSequence($question, $stepNum)) {
                // The requested question doesn't seem like the right one
                return null;
            }
        }

        return $question;
    }

    /**
     * @return int
     */
    public function getCurrentStepNumber()
    {
        return $this->getRequest()->getVar('step');
    }

    /**
     * @return Form
     */
    public function Form()
    {
        $fields = FieldList::create(
            HiddenField::create(
                'CurrentQuestionID',
                null,
                $this->getCurrentQuestion() ? $this->getCurrentQuestion()->ID : null
            ),
            HiddenField::create('Step', null, $this->getCurrentStepNumber())
        );

        $actions = FieldList::create(
            FormAction::create('goBack', 'Previous')
                ->addExtraClass('action-next')
                ->setUseButtonTag(true)
                ->setDisabled(!$this->hasPreviousQuestion()),
            FormAction::create('doSubmitQuestion', 'Next')
                ->addExtraClass('action-prev')
                ->setUseButtonTag(true)
        );

        $form = Form::create($this, 'Form', $fields, $actions);

        $question = $this->getCurrentQuestion();

        if (!$question) {
            $fields->add(HeaderField::create('NoQuestionMessage', 'No question to display'));

            return $form;
        }

        $fields->add(HeaderField::create('Question', $question->QuestionText));

        $answers = $question->Answers();

        if (!$answers->count()) {
            $fields->add(HeaderField::create('NoQuestionMessage', 'No answers to display'));

            return $form;
        }

        $answersField = RadioNestedSetField::create('Answers')
            ->addExtraClass('pathfinder-answers-field');

        // Produce a useful set of fields for each answer
        foreach ($answers as $answer) {
            $choices = $answer->Choices();
            $subsetName = sprintf('Answer%s', $answer->ID);

            if (!$choices->count()) {
                // Nothing to add
                continue;
            }

            $subsetName = sprintf('Choices[%s]', $question->ID);

            $subsetField = RadioNestedSubsetField::create($subsetName)
                ->setSubsetID($answer->ID);

            if ($choices->count() == 1) {
                // Provide enough detail to display as a single-choice
                $choice = $choices->first();

                $subsetField
                    ->setTitle($choice->ChoiceText)
                    ->setValue($choice->ID);

                $answersField->push($subsetField);

                continue;
            }

            // Populate multi-choice checkbox set
            $subsetField->push(
                CheckboxSetField::create(
                    sprintf('%s[%s]', $subsetName, $answer->ID),
                    '',
                    $choices->map('ID', 'ChoiceText')
                )
                    ->setAttribute('data-role', 'multi-choice')
                    ->setAttribute('data-target', $subsetField->ID())
            );

            $answersField->push($subsetField);
        }

        $fields->add($answersField);

        $requiredFields = RequiredFields::create([
            'CurrentQuestionID',
            'Step',
        ]);

        $form->setValidator($requiredFields);

        // Populate from previous answer
        $entry = $this->getStore()->getByPos($this->getCurrentStepNumber());

        if ($entry) {
            foreach ($answersField->getChildren() as $subsetField) {
                if ($subsetField->getSubsetID() !== $entry->AnswerID) {
                    continue;
                }

                $subsetField->setChecked(true);

                foreach ($subsetField->getChildren() as $field) {
                    $field->setValue($entry->ChoiceIDs);
                }
            }
        }

        $form = $this->getStore()->updateForm($form);

        $this->extend('updateForm', $form);

        return $form;
    }

    /**
     * @param array $data
     * @param Form $form
     *
     * @return HTTPResponse
     */
    public function doSubmitQuestion($data, $form)
    {
        if (!array_key_exists('Choices', $data)) {
            // We can't submit anything without an choice being made
            $form->sessionError('Please choose an answer');

            return $this->getController()->redirectBack();
        }

        if (
            !count($data['Choices'])
            || !array_key_exists($data['CurrentQuestionID'], $data['Choices'])) {
            // The data is poorly shaped
            $form->sessionError('Something went wrong, please try again.');

            return $this->getController()->redirectBack();
        }

        // We only want to use the first "group" of choices
        $selected = array_shift($data['Choices']);

        if (is_array($selected)) {
            // Selected answer was multi-choice
            $choiceIds = array_keys(array_shift($selected));
        } else {
            // Selected answer was single choice
            $choiceIds = [(int) $selected];
        }

        $choices = Choice::get()->byIds($choiceIds);

        if (!$choices->count()) {
            $form->sessionError('Something went wrong. The pathfinder was unable to use the answer you ' .
                'chose with the existing choices in the path.');

            return $this->getController()->redirectBack();
        }

        // We can clear the messages and stored data
        $form->clearFormState();

        // We'll need the store
        $store = $this->getStore();

        /** @var Answer $answer */
        $answer = $choices->first()->Answer(); // All choices should be for the same answer
        $stepNum = $data['Step'];

        if ($store->getByPos($stepNum)) {
            // Clear previously stored answers (including and after this question)
            $store->clearAfterPos($stepNum - 1);
        }

        $this->getStore()->addProgress(
            Question::get()->byId($data['CurrentQuestionID']),
            $answer,
            $choices
        );

        $nextQuestion = $this->getNextQuestion();

        if ($nextQuestion) {
            // Send them to the next question!
            $url = $store->augmentURL(
                $this->Link(sprintf(
                    'question?id=%s&step=%s',
                    $nextQuestion->ID,
                    $stepNum + 1
                )),
                $form
            );

            return $this->redirect($url);
        }

        // Time for results!
        return $this->redirect($store->augmentURL(
            $this->Link('suggestions?complete=1') // Adding the ?complete to make adding quwery vars easier
        ));
    }

    /**
     * @param array $data
     * @param Form $form
     * @return HTTPResponse|void
     */
    public function goBack($data, $form)
    {
        $store = $this->getStore();

        $prevStep = $data['Step'] - 1;
        $prevEntry = $store->getByPos($prevStep);

        // We can clear the messages and stored data
        $form->clearFormState();

        if (!$prevEntry) {
            return $this->redirect($this->Link('reset'));
        }

        $url = $store->augmentURL(
            $this->Link(sprintf(
                'question?id=%s&step=%s',
                $prevEntry->QuestionID,
                $prevStep
            ))
        );

        return $this->redirect($url);
    }

    /**
     * Get the terms gathered from the user's stored choices
     *
     * @return DataList
     */
    public function getGatheredTerms()
    {
        $store = $this->getStore();

        if (!$store->count()) {
            // No terms gathered
            return TaxonomyTerm::get()->byIDs([0]);
        }

        $termIds = [];

        foreach ($this->getStore()->get() as $entry) {
            $termIds = array_merge($termIds, $entry->ChoiceIDs);
        }

        $terms = TaxonomyTerm::get()->filter(['Choices.ID' => $termIds]);

        $this->extend('updateGatheredTerms', $terms, $termIds,$store);

        return $terms;
    }

    /**
     * @return DataList
     */
    public function getResults()
    {
        if ($this->results) {
            return $this->results;
        }

        $gatheredTerms = $this->getGatheredTerms();

        $model = $this->config()->get('results_model');

        if (!ClassInfo::exists($model)) {
            throw new \Exception(sprintf('"%s" must have have \'results_model\' configured.', self::class));
        }

        // Setup a default
        $results = $model::get()->byIds([0]);

        if ($gatheredTerms->count()) {
            $results = $model::get()->filter(['Terms.ID' => $gatheredTerms->column('ID')]);
        }

        $this->extend('updateResults', $results, $gatheredTerms);

        $this->results = $results;

        return $results;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * {@inheritDoc}
     */
    public function Link($action = null)
    {
        return $this->getController()->Link(Controller::join_links('pathfinder', $action, '/'));
    }

    /**
     * The URL to the first step int he pathfinder
     *
     * @return string
     */
    public function getStartLink()
    {
        $questions = $this->Questions();

        if (!$questions->count()) {
            return $this->Link('?questions-missing=1');
        }

        return $this->Link(sprintf('question?id=%s&step=1&start=1', $this->Questions()->first()->ID));
    }

    /**
     * @return string
     */
    public function getRestartLink()
    {
        return $this->Link('reset?restart=1');
    }

    /**
     * @return bool
     */
    public function hasPreviousQuestion()
    {
        $step = $this->getRequest()->getVar('step');

        return (bool) $this->getStore()->getByPos($step - 1);
    }

    /**
     * @return Question|null
     * @throws \Exception
     */
    public function getNextQuestion()
    {
        $store = $this->getStore();
        $last = $store->last();

        if (!$last) {
            return null;
        }

        /** @var Answer $answer */
        $answer = Answer::get()->byID($last->AnswerID);

        if (!$answer) {
            return null;
        }

        return $answer->getNextQuestion();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasProgress()
    {
        return $this->getStore()->count();
    }

    /**
     * @return string
     */
    public function getProgressLink()
    {
        $store = $this->getStore();
        $last = $store->last();

        if (!$last) {
            // Link to start
            return $store->augmentURL($this->getController()->Link());
        }

        $nextQuestion = $this->getNextQuestion();

        if (!$nextQuestion) {
            // Link to results
            return $store->augmentURL($this->Link('suggestions?complete=1'));
        }

        // Link to last answered question
        return $store->augmentURL(
            $this->Link(sprintf(
                'question?id=%s&step=%s',
                $nextQuestion->ID,
                $store->count() + 1
            ))
        );
    }

    /**
     * @return HTTPResponse
     */
    public function index()
    {
        return $this->redirect($this->getController()->Link());
    }

    /**s
     * @return HTTPResponse|Controller
     */
    public function question()
    {
        if (!$this->getCurrentQuestion()) {
            // No current question, so the user needs to start again
            return $this->redirect($this->Link('reset'));
        }

        return $this->getController();
    }

    /**
     * @return HTTPResponse
     */
    public function reset()
    {
        $store = $this->getStore();
        $store->clear();

        $link = $this->getController()->Link();

        if ($this->getRequest()->getVar('restart')) {
            $link = $this->getStartLink();
        }

        return $this->redirect($store->augmentURL($link));
    }

    /**
     * @return HTTPResponse|Controller
     */
    public function suggestions()
    {
        if (!$this->getStore()->count()) {
            // The user hasn't answered any questions
            return $this->redirect($this->Link('reset'));
        }

        $this->complete = true;

        // The Pathfinder model is setup to present its _results template
        return $this->getController();
    }
}
