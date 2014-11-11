<?php

namespace OroCRM\Bundle\MailChimpBundle\Autocomplete;

class ListSearchHandler extends IntegrationAwareSearchHandler
{
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
            ->setParameter('searchTerm', '%' . strtolower($searchTerm) . '%')
            ->setParameter('channel', (int)$channelId)
            ->addOrderBy('e.name', 'ASC')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults);

        $query = $this->aclHelper->apply($queryBuilder, 'VIEW');

        return $query->getResult();
    }
}
