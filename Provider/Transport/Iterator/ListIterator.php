<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Exception;

/**
 * class ListIterator
 */
class ListIterator extends AbstractMailChimpIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getResult()
    {
        $listData = $this->client->getLists([
            'offset' => (int)$this->offset / $this->batchSize,
            'count' => $this->batchSize,
        ]);

        $result = [
            'data' => $listData['lists'],
            'total' => $listData['total_items']
        ];

        $this->loadMergeVarsData($result);

        return $result;
    }

    /**
     * @param array $lists
     * @return array
     */
    protected function normalizeList(array $lists)
    {
        $data = [];
        foreach ($lists as $list) {
            $result = [];
            foreach ($list as $item => $value) {
                if (is_array($value)) {
                    if (in_array($item, ['contact', 'campaign_defaults', 'stats'])) {
                        foreach ($value as $key => $val) {
                            $result[$item . '_' . $key] = $val;
                        }
                    }
                } else {
                    $result[$item] = $value;
                }
            }
            $data[] = $result;
        }

        return $data;
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
            if (isset($listData['id'], $mergeVars[$listData['id']])) {
                $listData['merge_fields'] = $mergeVars[$listData['id']];
            } else {
                $listData['merge_fields'] = [];
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
     * @throws Exception
     */
    protected function getMergeVarsByListIds(array $listIds)
    {
        $result = [];
        foreach ($listIds as $listId) {
            $mergeVars = $this->client->getListMergeVars($listId);

            if (isset($mergeVars['merge_fields']) && is_array($mergeVars['merge_fields'])) {
                $result[$listId] = $mergeVars['merge_fields'];
            }
        }

        return $result;
    }
}
