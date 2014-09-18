<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Iterator;

use ZfrMailChimp\Client\MailChimpClient;

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
    protected $offset = 0;

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param MailChimpClient $client
     */
    public function __construct(MailChimpClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $key = $this->offset % self::BATCH_SIZE;

        if ($this->valid() && $key == 0) {
            $result      = $this->getResult();
            $this->total = $result['total'];
            $this->data  = $result['data'];
        }

        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->offset == 0 || $this->offset < $this->total;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->offset++;
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
        $this->offset = 0;
        $this->total  = 0;
        $this->data   = [];
    }

    /**
     * @return array
     */
    abstract protected function getResult();
}
