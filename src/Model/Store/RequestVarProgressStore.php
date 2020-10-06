<?php

namespace CodeCraft\Pathfinder\Model\Store;

use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;

/**
 * Can be used to pass progress on to the next step via the URL
 */
class RequestVarProgressStore extends ProgressStore
{
    /**
     * @var string
     */
    private static $progress_var_name = 'progress';

    /**
     * @var string
     */
    protected $encodedProgress;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $stored = $this->getDecodedStore();

        $entries = [];

        foreach ($stored as $entry) {
            $entries[] = ProgressEntry::create($entry);
        }

        parent::set($entries);
    }

    /**
     * @param array $store
     *
     * r@eturn string
     */
    public static function encode($store)
    {
        return urlencode(base64_encode(json_encode($store)));
    }

    /**
     * @param string $encoded An encoded store
     * @return array
     */
    public static function decode($encoded)
    {
        return json_decode(base64_decode(urldecode($encoded)), true);
    }

    /**
     * @return string
     */
    public function getProgressVarName()
    {
        return $this->config()->get('progress_var_name');
    }

    /**
     * @return string
     */
    public function getEncodedProgress()
    {
        if ($this->encodedProgress) {
            return $this->encodedProgress;
        }

        // Fallback to the request
        $request = Controller::curr()->getRequest();

        // Accept query variable from GET or POST
        $this->encodedProgress = $request->requestVar($this->getProgressVarName());

        return $this->encodedProgress;
    }

    /**
     * Gzip, Base64 and URL encode the store
     *
     * @return string
     */
    public function getEncodedStore()
    {
        return static::encode($this->toArray());
    }

    /**
     * @return array
     */
    public function getDecodedStore()
    {
        $encoded = $this->getEncodedProgress();

        if (!$encoded) {
            return [];
        }

        $store = static::decode($encoded);

        if (!is_array($stored)) {
            Injector::inst()->get(LoggerInterface::class)
                ->warning(sprintf('Unexpected value found in request for "%s::decode()"', self::class));
            return [];
        }

        return $store;
    }

    /**
     * {@inheritDoc}
     */
    public function augmentURL($url, $form = null)
    {
        $url = parent::augmentURL($url);

        // Match the query segment of the url
        preg_match('/\?(.*?)(?:#|$)/s', $url, $matches);

        // Prepare entries to be stored in the url
        $stored = $this->getEncodedStore();

        // Add the stored value to the query
        $query = implode('&', [$matches[1], sprintf('progress=%s', $this->getEncodedStore())]);

        // Insert it into the url
        return str_replace($matches[1], $query, $url);
    }
}
