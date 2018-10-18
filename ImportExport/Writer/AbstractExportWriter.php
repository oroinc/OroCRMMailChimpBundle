<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

abstract class AbstractExportWriter extends PersistentBatchWriter implements ClearableInterface
{
    /**
     * @var TransportInterface|MailChimpTransport
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!$this->transport) {
            throw new \InvalidArgumentException('Transport was not provided');
        }

        parent::write($items);
    }

    /**
     * @param array $response
     * @param string $container
     * @param string|null $key
     *
     * @return array
     */
    protected function getArrayData(array $response, $container, $key = null)
    {
        if (!empty($response[$container])) {
            if ($key) {
                return array_column($response[$container], $key);
            }

            return $response[$container];
        }

        return [];
    }

    /**
     * @param mixed $response
     * @param callable $func
     */
    protected function handleResponse($response, callable $func = null)
    {
        if (!is_array($response)) {
            return;
        }
        if (!$this->logger) {
            return;
        }

        if ($func) {
            $func($response, $this->logger);
        }
        if (!empty($response['errors']) && is_array($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = '';
                if (array_key_exists('error', $error)) {
                    $message = $error['error'];
                } elseif (array_key_exists('message', $error)) {
                    $message = $error['message'];
                }

                if (false !== strpos($message, 'fake') ||
                    false !== strpos($message, 'valid') ||
                    false !== strpos($message, 'already exist') ||
                    false !== stripos($message, 'none of the emails provided')
                ) {
                    $this->logger->warning('Mailchimp returns error from the server: message: "{message}"', [
                        'message' => $message,
                        'errorData' => $error,
                    ]);
                    continue;
                }

                $this->logger->error('Mailchimp returns error from the server: message: "{message}"', [
                    'message' => $message,
                    'errorData' => $error,
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return parent::doClear();
    }

    /**
     * @inheritdoc
     */
    protected function doClear()
    {
        // Don't do clear in PersistentBatchWriter::writer()
        // Mailchimp bundle uses iterators which prefetch and cache entities. (ex. BufferedIdentityQueryResultIterator)
        // It causes issues with detached entities after EntityManager::clear().
        // see CRM-8490
    }

    /**
     * @param SubscribersList $subscribersList
     * @return array
     * @throws \Exception
     */
    protected function getSubscribersListMergeVars(SubscribersList $subscribersList)
    {
        $response = $this->transport->getListMergeVars($subscribersList->getOriginId());

        $this->handleResponse($response);

        if (!empty($response['errors'])) {
            throw new \RuntimeException('Can not get list of merge vars.');
        }

        return $this->extractMergeVarsFromResponse($response);
    }

    /**
     * @param array $response
     * @return array
     */
    protected function extractMergeVarsFromResponse(array $response)
    {
        if (!is_array($response) || !isset($response['merge_fields']) || !is_array($response['merge_fields'])) {
            return [];
        }
        return $response['merge_fields'];
    }
}
