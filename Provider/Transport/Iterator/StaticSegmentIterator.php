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
    protected function getResult()
    {
        $result = $this->client->getListStaticSegments([
            'offset' => (int)$this->offset / $this->batchSize,
            'count' => $this->batchSize,
            'list_id' => $this->subscriberListId
        ]);

        return [
            'data' => $result['segments'],
            'total' => $result['total_items']
        ];
    }
}
