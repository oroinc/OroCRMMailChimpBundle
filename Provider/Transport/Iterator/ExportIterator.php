<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Guzzle\Http\EntityBodyInterface;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class ExportIterator implements \Iterator
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var EntityBodyInterface
     */
    protected $body;

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array|null
     */
    protected $header;

    /**
     * @var mixed
     */
    protected $current = null;

    /**
     * @var mixed
     */
    protected $offset = -1;

    /**
     * @var bool
     */
    protected $useFirstLineAsHeader;

    /**
     * @param MailChimpClient $client
     * @param string $methodName
     * @param array $parameters
     * @param bool $useFirstLineAsHeader
     */
    public function __construct(
        MailChimpClient $client,
        $methodName,
        array $parameters = [],
        $useFirstLineAsHeader = true
    ) {
        $this->client = $client;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        $this->useFirstLineAsHeader = $useFirstLineAsHeader;
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
            $this->body->seek(0);

            if ($this->useFirstLineAsHeader) {
                $line = $this->getLineData();
                if ($line) {
                    $this->header = $line;
                }
            }
        }

        return $this->getResponseItem();
    }

    /**
     * @return array|null
     */
    protected function getLineData()
    {
        $line = $this->body->readLine();
        if (is_string($line)) {
            return json_decode($line, JSON_OBJECT_AS_ARRAY);
        } else {
            return null;
        }
    }

    /**
     * @return array|null
     */
    protected function getResponseItem()
    {
        $line = $this->getLineData();
        if (!$line) {
            return null;
        }

        if ($this->useFirstLineAsHeader) {
            if (count($this->header) !== count($line)) {
                throw new \RuntimeException(sprintf(
                    'Number of elements for header and line have to be the same. ' .
                    'Header count: "%s", line count: "%s", ' .
                    'header: "%s", line: "%s"',
                    count($this->header),
                    count($line),
                    json_encode($this->header),
                    json_encode($line)
                ));
            }
            $line = array_combine($this->header, $line);
        }

        return $line;
    }
}
