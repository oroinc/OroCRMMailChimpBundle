<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

class ListIterator extends AbstractMailChimpIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        $result = $this->client->getLists(
            ['start' => (int)$this->offset / $this->batchSize, 'limit' => $this->batchSize]
        );

        $this->loadMergeVarsData($result);

        return $result;
    }

    /**
     * Adds "merge_vars" value for each list.
     *
     * @param array|mixed $data
     */
    protected function loadMergeVarsData(&$data)
    {
        if (!isset($data['data']) || !is_array($data['data']) || !$listIds = $this->getListIds($data)) {
            return;
        }

        $mergeVars = $this->getMergeVarsByListIds($listIds);

        foreach ($data['data'] as &$listData) {
            if (isset($listData['id']) && isset($mergeVars[$listData['id']])) {
                $listData['merge_vars'] = $mergeVars[$listData['id']];
            } else {
                $listData['merge_vars'] = [];
            }
        }
    }

    /**
     * Get ids of each element in data.
     *
     * @param array $data
     * @return array
     */
    protected function getListIds(array $data)
    {
        $result = [];

        foreach ($data['data'] as $listData) {
            if (isset($listData['id'])) {
                $result[] = $listData['id'];
            }
        }
        return $result;
    }

    /**
     * Get list of merge vars array for each list id.
     *
     * @param array $listIds
     * @return array
     */
    protected function getMergeVarsByListIds(array $listIds)
    {
        $result = [];
        $data = $this->client->getListMergeVars(['id' => $listIds]);

        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $listData) {
                if (isset($listData['id']) && isset($listData['merge_vars']) && is_array($listData['merge_vars'])) {
                    $result[$listData['id']] = $listData['merge_vars'];
                }
            }
        }

        return $result;
    }
}
