<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;

class ExtendedMergeVarRemoveIterator extends AbstractSubordinateIterator
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

        $qb->select(
            [
                'extendedMergeVar.id',
                $staticSegment->getId() . ' static_segment_id',
                'extendedMergeVar.name',
                $qb->expr()->literal(ExtendedMergeVar::STATE_REMOVE) . ' state'
            ]
        );

        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('extendedMergeVar.staticSegment', ':staticSegment'),
                    $qb->expr()->notIn('extendedMergeVar.name', ':vars'),
                    $qb->expr()->neq('extendedMergeVar.state', ':state')
                )
            )
            ->setParameter('staticSegment', $staticSegment)
            ->setParameter('vars', $varNames)
            ->setParameter('state', ExtendedMergeVar::STATE_DROPPED);

        $bufferedIterator = new BufferedQueryResultIterator($qb);
        $bufferedIterator->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)->setReverse(true);

        return $bufferedIterator;
    }
}
