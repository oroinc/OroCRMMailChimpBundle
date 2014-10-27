<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\From;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class MarketingListReader extends IteratorBasedReader
{
    const OPTION_SEGMENT = 'segment';

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param MarketingListProvider $marketingListProvider
     */
    public function setMarketingListProvider(MarketingListProvider $marketingListProvider)
    {
        $this->marketingListProvider = $marketingListProvider;
    }

    /**
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function setContactInformationFieldsProvider(
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$context->hasOption(self::OPTION_SEGMENT)) {
            throw new InvalidConfigurationException(
                sprintf('Configuration reader must contain "%s".', self::OPTION_SEGMENT)
            );
        }

        $segment = $context->getOption(self::OPTION_SEGMENT);

        if (!$segment instanceof Segment) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Option "%s" value must be instance of "%s", "%s" given.',
                    self::OPTION_SEGMENT,
                    $this->className,
                    is_object($segment) ? get_class($segment) : gettype($segment)
                )
            );
        }

        $marketingList = $segment->getMarketingList();

        $this->setSourceIterator($this->getSubscribeIterator($marketingList));
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return \Iterator
     */
    protected function getSubscribeIterator(MarketingList $marketingList)
    {
        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        /** @var From $from */
        $fromParts = $qb->getDQLPart('from');
        $from = reset($fromParts);

        $expr = $qb->expr()->orX();
        array_walk(
            $contactInformationFields,
            function ($field) use ($expr, $qb, $from) {
                $expr->add(
                    $qb->expr()->eq(sprintf('%s.%s', $from->getAlias(), $field), 'mmb.email')
                );
            }
        );

        $qb->leftJoin('OroCRMMailChimpBundle:Member', 'mmb', Join::WITH, $expr);

        return new BufferedQueryResultIterator($qb);
    }
}
