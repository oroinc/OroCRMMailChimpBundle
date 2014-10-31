<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Reader\SubordinateReaderInterface;

class StaticSegmentExportListIterator extends AbstractSubscribersListIterator implements SubordinateReaderInterface
{
    /**
     * @var string
     */
    protected $staticSegmentMemberClassName;

    /**
     * @return bool
     */
    public function writeRequired()
    {
        if (!$this->subordinateIterator) {
            return false;
        }

        return !$this->subordinateIterator->valid();
    }

    /**
     * @param string $staticSegmentMemberClassName
     */
    public function setStaticSegmentMemberClassName($staticSegmentMemberClassName)
    {
        $this->staticSegmentMemberClassName = $staticSegmentMemberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (!$staticSegment instanceof StaticSegment) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of %s is expected, %s given.',
                    'OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment',
                    is_object($staticSegment) ? get_class($staticSegment) : gettype($staticSegment)
                )
            );
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($this->staticSegmentMemberClassName)
            ->getRepository($this->staticSegmentMemberClassName)
            ->createQueryBuilder('segmentMmb');

        $qb
            ->select('segmentMmb')
            ->join('segmentMmb.staticSegment', 'staticSegment')
            ->andWhere($qb->expr()->eq('staticSegment', ':staticSegment'))
            ->andWhere($qb->expr()->notIn('segmentMmb.state', ':states'))
            ->setParameters(
                [
                    'staticSegment' => $staticSegment,
                    'states' => [StaticSegmentMember::STATE_SYNCED, StaticSegmentMember::STATE_DROP],
                ]
            )
            ->orderBy('staticSegment.id');

        return new BufferedQueryResultIterator($qb);
    }
}
