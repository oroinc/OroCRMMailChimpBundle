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
    private $doctrineHelper;

    /**
     * @var string
     */
    private $extendedMergeVarClassName;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $ExtendedMergeVarClassName
     * @param ProviderInterface $provider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        $ExtendedMergeVarClassName,
        ProviderInterface $provider
    ) {
        if (!is_string($ExtendedMergeVarClassName) || empty($ExtendedMergeVarClassName)) {
            throw new \InvalidArgumentException('ExtendedMergeVar class name must be provided.');
        }

        $this->doctrineHelper = $doctrineHelper;
        $this->extendedMergeVarClassName = $ExtendedMergeVarClassName;
        $this->provider = $provider;
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
        $vars = $this->provider
            ->provideExtendedMergeVars(
                $staticSegment->getMarketingList()
            );

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
