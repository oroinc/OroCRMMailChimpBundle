<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class StaticSegmentIterator extends AbstractMailChimpIterator
{
    /**
     * @var int
     */
    protected $listId;

    /**
     * @param int $listId
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
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
            $this->total = sizeof($result);
            $this->data  = $result;
        }

        $this->current = isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        $result = $this->client->getListStaticSegments(
            [
                'start' => (int)$this->offset / $this->batchSize,
                'limit' => $this->batchSize,
                'get_counts' => false,
                'id' => $this->listId
            ]
        );

        return $result;
    }
}
