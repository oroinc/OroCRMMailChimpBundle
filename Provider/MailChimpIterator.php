<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use ZfrMailChimp\Client\MailChimpClient;

class MailChimpIterator implements \Iterator
{
    const BATCH_SIZE = 20;

    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $method;

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
     * @param string          $method
     */
    public function __construct(MailChimpClient $client, $method)
    {
        $this->client = $client;
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $key = $this->offset % self::BATCH_SIZE;

        if ($this->valid() && empty($this->data[$key])) {
            $result      = $this->client->{$this->method}(['start' => $this->offset, 'limit' => self::BATCH_SIZE]);
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
}
