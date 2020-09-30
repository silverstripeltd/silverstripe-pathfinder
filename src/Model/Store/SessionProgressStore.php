<?php

namespace CodeCraft\Pathfinder\Model\Store;


use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;

/**
 * Uses the session to store a Pathfinder user's progress
 */
class SessionProgressStore extends ProgressStore
{

    /**
     * {@inheritDoc}
     */
    public function add(ProgressEntry $entry)
    {
        parent::add($entries);

        Controller::curr()->getRequest()->getSession()->set(
            $this->getStorageName(),
            json_encode($this->toArray())
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $existing = parent::get();

        if (is_array($existing)) {
            return $existing;
        };

        $stored = Controller::curr()->getRequest()->getSession()->get($this->getStorageName());

        if (!$stored) {
            // We want to return a helpful value, but not "cache" it
            return [];
        }

        $stored = json_decode($stored, true);

        if (!is_array($stored)) {
            Injector::inst()->get(LoggerInterface::class)
                ->warning(sprintf('Unexpected value stored in session for "%s::get()"', self::class));
            // We want to return a helpful value, but not "cache" it
            return [];
        }

        $entries = [];

        foreach ($stored as $entry) {
            $entries[] = ProgressEntry::create($entry);
        }

        parent::set($entries);

        return parent::get();
    }

    /**
     * {@inheritDoc}
     */
    public function set(array $entries)
    {
        parent::set($entries);

        Controller::curr()->getRequest()->getSession()->set(
            $this->getStorageName(),
            json_encode($this->get()->toArray())
        );

        return $this;
    }
}
