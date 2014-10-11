<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
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
     * @param \Iterator $subscribersLists
     * @param MailChimpClient $client
     * @param array $parameters
     */
    public function __construct(\Iterator $subscribersLists, MailChimpClient $client, array $parameters = [])
    {
        parent::__construct($subscribersLists);
        $this->client = $client;
        $this->parameters = $parameters;
        if (!isset($this->parameters['status'])) {
            $this->parameters['status'] = Member::STATUS_SUBSCRIBED;
        }
    }

    /**
     * Creates iterator of members for List
     *
     * @param SubscribersList $subscribersList
     * @return \Iterator
     */
    protected function createSubordinateIterator($subscribersList)
    {
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
        if (is_array($parameters['status'])) {
            // If we need members with many statuses, we will bo separate requests for them.
            $result = new \AppendIterator();
            foreach ($parameters['status'] as $status) {
                $parameters['status'] = $status;
                $result->append($this->createExportMembersIterator($subscribersList, $parameters));
            }
        } else {
            $result = $this->createExportMembersIterator($subscribersList, $parameters);
        }

        return $result;
    }

    /**
     * @param SubscribersList $subscribersList
     * @param array $parameters
     * @return \Iterator
     */
    protected function createExportMembersIterator(SubscribersList $subscribersList, $parameters)
    {
        return new \CallbackFilterIterator(
            $this->createExportIterator(MailChimpClient::EXPORT_LIST, $parameters),
            function (&$current) use ($subscribersList, $parameters) {
                if (is_array($current)) {
                    $current['list_id'] = $subscribersList->getOriginId();
                    $current['status'] = $parameters['status'];
                }
                return true;
            }
        );
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Iterator
     */
    protected function createExportIterator($method, array $parameters)
    {
        return new ExportIterator($this->client, $method, $parameters);
    }
}
