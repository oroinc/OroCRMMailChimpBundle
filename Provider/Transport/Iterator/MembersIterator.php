<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MembersIterator extends AbstractSubordinateIterator
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var integer
     */
    protected $currentSubscribersListOriginId;

    /**
     * @param \Iterator $subscribersLists
     * @param MailChimpClient $client
     * @param array $parameters
     */
    public function __construct(\Iterator $subscribersLists, MailChimpClient $client, array $parameters = [])
    {
        parent::__construct($subscribersLists);
        $this->client = $client;
        $this->parameters = $parameters;
    }

    public function current()
    {
        $result = parent::current();

        if (is_array($result)) {
            $result['list_id'] = $this->currentSubscribersListOriginId;
        }

        return $result;
    }

    /**
     * Creates iterator of members for List
     *
     * @param SubscribersList $subscribersList
     * @return \Iterator
     */
    protected function createSubordinateIterator($subscribersList)
    {
        $this->currentSubscribersListOriginId = $subscribersList->getOriginId();
        if (!$subscribersList instanceof SubscribersList) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, %s given.',
                    'OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList',
                    is_object($subscribersList) ? get_class($subscribersList) : gettype($subscribersList)
                )
            );
        }

        $parameters = $this->parameters;
        $parameters['id'] = $subscribersList->getOriginId();
        if (isset($parameters['status']) && is_array($parameters['status'])) {
            // If we need members with many statuses, we will bo separate requests for them.
            $result = new \AppendIterator();
            foreach ($parameters['status'] as $status) {
                $parameters['status'] = $status;
                $result->append($this->createExportIterator(MailChimpClient::EXPORT_LIST, $parameters));
            }
        } else {
            $result = $this->createExportIterator(MailChimpClient::EXPORT_LIST, $parameters);
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return ExportIterator
     */
    protected function createExportIterator($method, array $parameters)
    {
        return new ExportIterator($this->client, $method, $parameters);
    }
}
