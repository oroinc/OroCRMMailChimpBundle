<?php

namespace OroCRM\Bundle\MailChimpBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class TemplateSearchHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function checkAllDependenciesInjected()
    {
        if (!$this->entityRepository || !$this->idFieldName) {
            throw new \RuntimeException('Search handler is not fully configured');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $this->checkAllDependenciesInjected();

        if ($searchById) {
            $item = $this->findById($query);

            return array(
                'results' => [$this->convertItem($item)],
                'more'    => false
            );
        } else {
            $items = $this->searchEntities($query, 0, null);

            return array(
                'results' => $this->convertItems($items),
                'more'    => false
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $id = $parts[0];
        $channelId = !empty($parts[1]) ? $parts[1] : false;

        $criteria = [$this->idFieldName => $id];
        if (false !== $channelId) {
            $criteria['channel'] = $channelId;
        }

        return $this->entityRepository->findOneBy($criteria, null);
    }

    /**
     * {@inheritdoc}
     */
    protected function searchEntities($search, $firstResult, $maxResults)
    {
        list($searchTerm, $channelId) = explode(';', $search);

        $queryBuilder = $this->entityRepository->createQueryBuilder('e');
        $queryBuilder
            ->where($queryBuilder->expr()->like('LOWER(e.name)', ':searchTerm'))
            ->andWhere('e.channel = :channel')
            ->andWhere('e.active = :active')
            ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
            ->setParameter('channel', (int)$channelId)
            ->setParameter('active', true)
            ->addOrderBy('e.category', 'ASC')
            ->addOrderBy('e.name', 'ASC');

        $query = $this->aclHelper->apply($queryBuilder, 'ASSIGN');

        return $query->getResult();
    }

    /**
     * @param Template[] $items
     * @return array
     */
    protected function convertItems(array $items)
    {
        $grouped = [];
        foreach ($items as $item) {
            $groupingKey = $item->getCategory();
            if (!$groupingKey) {
                $groupingKey = $item->getType();
            }
            $grouped[$groupingKey][] = $item;
        }

        $result = [];
        foreach ($grouped as $group => $elements) {
            $gropedItem = [
                'name' => $group
            ];
            foreach ($elements as $element) {
                $gropedItem['children'][] = $this->convertItem($element);
            }
            $result[] = $gropedItem;
        }

        return $result;
    }
}
