<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;
use Oro\Bundle\MailChimpBundle\Util\CallbackFilterIteratorCompatible;

class TemplateIterator implements \Iterator
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
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @param MailChimpClient $client
     * @param array $parameters
     */
    public function __construct(MailChimpClient $client, array $parameters = [])
    {
        $this->client = $client;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (!$this->iterator) {
            $this->initIterator();
        }
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function initIterator()
    {
        $templatesList = (array)$this->client->getTemplates($this->parameters);

        $this->iterator = new CallbackFilterIteratorCompatible(
            new FlattenIterator($templatesList, 'type', false),
            function (&$current) {
                if (is_array($current)) {
                    $current['origin_id'] = $current['id'];
                    unset($current['id']);
                }

                return true;
            }
        );
    }
}
