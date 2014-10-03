<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class RemoveWriter implements ItemWriterInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param ManagerRegistry $registry
     * @param string $entityName
     * @param string $field
     */
    public function __construct(ManagerRegistry $registry, $entityName, $field)
    {
        $this->registry = $registry;
        $this->entityName = $entityName;
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $toDelete) {
            /** @var EntityManager $em */
            $em = $this->registry->getManagerForClass($this->entityName);
            $qb = $em->createQueryBuilder();
            $qb->delete($this->entityName, 'e')
                ->andWhere($qb->expr()->notIn('e.' . $this->field, ':items'))
                ->setParameter('items', $toDelete);
            $qb->getQuery()->execute();
        }
    }
}
