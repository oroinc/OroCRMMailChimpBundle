<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Guzzle\Http\EntityBodyInterface;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class ExportIterator implements \Iterator
{
    /**
     * @var MailChimpClient
     */
    private $client;

    /**
     * @var EntityBodyInterface
     */
    private $body;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array|null
     */
    private $header;

    /**
     * @var mixed
     */
    protected $current = null;

    /**
     * @var mixed
     */
    protected $offset = -1;

    /**
     * @param MailChimpClient $client
     * @param string $methodName
     * @param array $parameters
     */
    public function __construct(MailChimpClient $client, $methodName, array $parameters = [])
    {
        $this->client = $client;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
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
    public function next()
    {
        $this->current = $this->read();
        if ($this->valid()) {
            $this->offset += 1;
        }
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
    public function valid()
    {
        return !is_null($this->current);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->offset = -1;
        $this->current = null;
        $this->body = null;
        $this->header = null;

        $this->next();
    }

    /**
     * Read one line from export response, if read success converts line to associative array according to export data
     * format.
     *
     * @return array|null
     */
    protected function read()
    {
        if (!$this->body) {
            $response = $this->client->export($this->methodName, $this->parameters);
            $this->body = $response->getBody();
            $line = $this->body->readLine();
            if (is_string($line)) {
                $this->header = json_decode($line);
            } else {
                return null;
            }
        }

        $line = $this->body->readLine();
        if (is_string($line)) {
            return array_combine($this->header, json_decode($line));
        } else {
            return null;
        }
    }
}
