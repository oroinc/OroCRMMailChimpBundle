<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;

class ExtendedMergeVarAddIterator extends AbstractSubordinateIterator
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $extendedMergeVarClassName;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ProviderInterface $provider
     * @param string $extendedMergeVarClassName
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ProviderInterface $provider,
        $extendedMergeVarClassName
    ) {
        if (!is_string($extendedMergeVarClassName) || empty($extendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be provided.');
        }

        $this->doctrineHelper = $doctrineHelper;
        $this->provider = $provider;
        $this->extendedMergeVarClassName = $extendedMergeVarClassName;
    }

    /**
     * @param \Iterator $mainIterator
     */
    public function setMainIterator(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $vars = $this->provider->provideExtendedMergeVars($staticSegment->getMarketingList());

        $varNames = array_map(
            function ($each) {
                return $each['name'];
            },
            $vars
        );

        $qb = $this->doctrineHelper
            ->getEntityManager($this->extendedMergeVarClassName)
            ->getRepository($this->extendedMergeVarClassName)
            ->createQueryBuilder('extendedMergeVar');

        $qb
            ->select('extendedMergeVar.name')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'),
                    $qb->expr()->in('extendedMergeVar.name', ':vars'),
                    $qb->expr()->notIn('extendedMergeVar.state', ':states')
                )
            )
            ->setParameter(':staticSegment', $staticSegment)
            ->setParameter(':vars', $varNames)
            ->setParameter(':states', [ExtendedMergeVar::STATE_REMOVE, ExtendedMergeVar::STATE_DROPPED]);

        $existingVars = array_map(
            function ($each) {
                return $each['name'];
            },
            $qb->getQuery()->getArrayResult()
        );

        return new \CallbackFilterIterator(
            new \ArrayIterator($vars),
            function (&$current) use ($staticSegment, $existingVars) {
                if (is_array($current) && isset($current['name'])) {
                    if (in_array($current['name'], $existingVars)) {
                        return false;
                    }
                    $current['static_segment_id'] = $staticSegment->getId();
                    $current['state'] = ExtendedMergeVar::STATE_ADD;
                }
                return true;
            }
        );
    }
}
