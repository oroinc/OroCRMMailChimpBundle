<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;

class ExtendedMergeVarAddIterator extends AbstractSubordinateIterator
{
    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @param ProviderInterface $provider
     */
    public function __construct(ProviderInterface $provider)
    {
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
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        if (!$this->provider->isApplicable($staticSegment->getMarketingList())) {
            return new \EmptyIterator();
        }

        $vars = $this->provider->provideExtendedMergeVars($staticSegment->getMarketingList());

        $existingVars = $staticSegment
            ->getExtendedMergeVars([ExtendedMergeVar::STATE_ADD, ExtendedMergeVar::STATE_SYNCED])
            ->map(function (ExtendedMergeVar $extendedMergeVar) {
                return $extendedMergeVar->getName();
            })
            ->toArray();

        $vars = array_filter(
            $vars,
            function ($var) use ($existingVars) {
                return !in_array($var['name'], $existingVars, true);
            }
        );

        return new \CallbackFilterIterator(
            new \ArrayIterator($vars),
            function (&$current) use ($staticSegment) {
                if (is_array($current)) {
                    $current['static_segment_id'] = $staticSegment->getId();
                    $current['state'] = ExtendedMergeVar::STATE_ADD;
                }
                return true;
            }
        );
    }
}
