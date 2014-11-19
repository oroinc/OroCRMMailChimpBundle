<?php

namespace OroCRM\Bundle\MailChimpBundle\Autocomplete;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class TemplateSearchHandler extends IntegrationAwareSearchHandler
{
    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage, $searchById = false)
    {
        $this->checkAllDependenciesInjected();

        if ($searchById) {
            $items = $this->findById($query);

            return [
                'results' => [$this->convertItem(reset($items))],
                'more'    => false
            ];
        } else {
            $items = $this->searchEntities($query, 0, null);

            return [
                'results' => $this->convertItems($items),
                'more'    => false
            ];
        }
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

        $query = $this->aclHelper->apply($queryBuilder, 'VIEW');

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
