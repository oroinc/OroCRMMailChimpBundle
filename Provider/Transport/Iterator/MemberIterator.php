<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;
use Oro\Bundle\MailChimpBundle\Util\CallbackFilterIteratorCompatible;

class MemberIterator extends AbstractSubordinateIterator
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
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
                    'Oro\\Bundle\\MailChimpBundle\\Entity\\SubscribersList',
                    is_object($subscribersList) ? get_class($subscribersList) : gettype($subscribersList)
                )
            );
        }

        $parameters = $this->parameters;
        $parameters['id'] = $subscribersList->getOriginId();
        if (is_array($parameters['status'])) {
            // If we need members with many statuses, we will do separate requests for them.
            $result = new \AppendIterator();
            foreach ($parameters['status'] as $status) {
                $parameters['status'] = $status;
                $result->append($this->createExportMemberIterator($subscribersList, $parameters));
            }
            $result->rewind();
        } else {
            $result = $this->createExportMemberIterator($subscribersList, $parameters);
        }

        return $result;
    }

    /**
     * @param SubscribersList $subscribersList
     * @param array $parameters
     * @return \Iterator
     */
    protected function createExportMemberIterator(SubscribersList $subscribersList, $parameters)
    {
        return new CallbackFilterIteratorCompatible(
            $this->createExportIterator(MailChimpClient::EXPORT_LIST, $parameters),
            function (&$current) use ($subscribersList, $parameters) {
                if (is_array($current)) {
                    $current['subscribersList_id'] = $subscribersList->getId();
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
        $exportIterator = new ExportIterator($this->client, $method, $parameters);

        if ($this->logger) {
            $exportIterator->setLogger($this->logger);
        }

        return $exportIterator;
    }
}
