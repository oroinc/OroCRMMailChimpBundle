<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Persistence\ManagerRegistry;

class CampaignRemoveWriter extends RemoveWriter
{
    /**
     * @var string
     */
    protected $campaignType;

    /**
     * @param ManagerRegistry $registry
     * @param string $entityName
     * @param string $field
     * @param string $campaignType
     */
    public function __construct(ManagerRegistry $registry, $entityName, $field, $campaignType)
    {
        parent::__construct($registry, $entityName, $field);

        if (!is_string($campaignType) || empty($campaignType)) {
            throw new \InvalidArgumentException('CampaignType should be provided.');
        }

        $this->campaignType = $campaignType;
    }

    /**
     * {@inheritdoc}
     */
    protected function createQueryBuilder(array $item)
    {
        $qb = parent::createQueryBuilder($item);

        $qb->andWhere($qb->expr()->eq('e.type', ':campaignType'))
            ->setParameter('campaignType', $this->campaignType);

        return $qb;
    }
}
