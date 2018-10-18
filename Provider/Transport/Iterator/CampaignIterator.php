<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class CampaignIterator extends AbstractMailChimpIterator
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * @param MailChimpClient $client
     * @param array $filters
     * @param int $batchSize
     */
    public function __construct(MailChimpClient $client, array $filters = [], $batchSize = self::BATCH_SIZE)
    {
        parent::__construct($client, $batchSize);
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        $arguments = [
            'offset' => (int)$this->offset / $this->batchSize,
            'count' => $this->batchSize
        ];

        if ($this->filters) {
            $arguments = array_merge($arguments, $this->filters);
        }
        $result = $this->client->getCampaigns($arguments);

        $list_ids = isset($arguments['list_ids']) && is_array($arguments['list_ids']) ? $arguments['list_ids'] : [];

        $campaigns = array_reduce($result['campaigns'], function ($result, array $campaign) use ($list_ids) {
            if (array_key_exists('recipients', $campaign) &&
                array_key_exists('list_id', $campaign['recipients']) &&
                count($list_ids) > 0 &&
                false === in_array($campaign['recipients']['list_id'], $list_ids, true)
            ) {
                return $result;
            }

            $campaign['report'] = $this->client->getCampaignReport($campaign['id']);

            $result[] = $campaign;
            return $result;
        }, []);

        return [
            'data' => $campaigns,
            'total' => $result['total_items'],
        ];
    }
}
