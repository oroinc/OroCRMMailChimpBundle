<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

abstract class AbstractMailChimpIterator implements \Iterator
{
    const BATCH_SIZE = 20;

    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @var mixed
     */
    protected $current = null;

    /**
     * @var int
     */
    protected $offset = -1;

    /**
     * @var int
     */
    protected $total = -1;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * @param MailChimpClient $client
     * @param int $batchSize
     */
    public function __construct(MailChimpClient $client, $batchSize = self::BATCH_SIZE)
    {
        $this->client = $client;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->total > 0 && $this->offset < $this->total;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->offset += 1;
        $key = $this->offset % $this->batchSize;

        if (($this->valid() || ($this->total == -1)) && $key == 0) {
            $result      = $this->getResult();
            $this->total = $result['total'];
            $this->data  = $result['data'];
        }

        $this->current = isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->current = null;
        $this->offset  = -1;
        $this->total   = -1;
        $this->data    = null;

        $this->next();
    }

    /**
     * @return array
     */
    abstract protected function getResult();
}
