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
        foreach ($items as $writerItem) {
            /** @var EntityManager $em */
            $em = $this->registry->getManagerForClass($this->entityName);
            $qb = $em->createQueryBuilder();
            $qb->delete($this->entityName, 'e');
            if (array_key_exists($this->field, $writerItem) && !empty($writerItem[$this->field])) {
                $qb->andWhere($qb->expr()->notIn('e.' . $this->field, ':items'))
                    ->setParameter('items', (array)$writerItem[$this->field]);
            }

            // Workaround to limit by channel. Channel is not available in second step context.
            if (array_key_exists('channel', $writerItem)) {
                $qb->andWhere($qb->expr()->eq('e.channel', ':channel'))
                    ->setParameter('channel', $writerItem['channel']);
            }

            $qb->getQuery()->execute();
        }
    }
}
