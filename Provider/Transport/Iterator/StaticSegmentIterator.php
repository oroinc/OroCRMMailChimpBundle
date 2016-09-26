<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class StaticSegmentIterator extends AbstractMailChimpIterator
{
    const SUBSCRIBERS_LIST_ID = 'subscribers_list_id';

    /**
     * @var string
     */
    protected $subscriberListId;

    /**
     * @param string $listId
     */
    public function setSubscriberListId($listId)
    {
        $this->subscriberListId = $listId;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (!$this->subscriberListId) {
            throw new \InvalidArgumentException('SubscribersList id must be provided');
        }

        $this->offset += 1;
        $key = $this->offset % $this->batchSize;

        if (($this->valid() || ($this->total == -1)) && $key == 0) {
            $result = $this->getResult();
            $this->total = sizeof($result);

            $this->data = array_map(
                function ($item) {
                    $item[self::SUBSCRIBERS_LIST_ID] = $this->subscriberListId;

                    return $item;
                },
                $result
            );
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
                'id' => $this->subscriberListId
            ]
        );

        return $result;
    }
}
