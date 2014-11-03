<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

abstract class AbstractMarketingListReader extends IteratorBasedReader implements StaticSegmentAwareInterface
{
    const MEMBER_ALIAS = 'mmb';

    /**
     * @var \Iterator
     */
    protected $sourceIterator;

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
    protected $staticSegmentClassName;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @var StaticSegment
     */
    protected $segment;

    /**
     * @var MarketingList
     */
    protected $marketingList;

    /**
     * @param ContextRegistry $contextRegistry
     * @param MarketingListProvider $marketingListProvider
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        parent::__construct($contextRegistry);

        $this->marketingListProvider = $marketingListProvider;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    /**
     * @param string $segmentClassName
     */
    public function setStaticSegmentClassName($segmentClassName)
    {
        $this->staticSegmentClassName = $segmentClassName;
    }

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->staticSegmentClassName) {
            throw new InvalidConfigurationException('StaticSegment class name must be provided');
        }

        if (!$context->hasOption(StaticSegmentAwareInterface::OPTION_SEGMENT)) {
            throw new InvalidConfigurationException(
                sprintf('Configuration reader must contain "%s".', StaticSegmentAwareInterface::OPTION_SEGMENT)
            );
        }

        $this->segment = $this->getStaticSegment();

        if (!is_a($this->segment, $this->staticSegmentClassName)) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Option "%s" value must be instance of "%s", "%s" given.',
                    StaticSegmentAwareInterface::OPTION_SEGMENT,
                    $this->staticSegmentClassName,
                    is_object($this->segment) ? get_class($this->segment) : gettype($this->segment)
                )
            );
        }

        $this->marketingList = $this->segment->getMarketingList();

        if (!$this->getSourceIterator()) {
            $this->setSourceIterator($this->getQueryIterator());
        }
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(MarketingList $marketingList)
    {
        if (!$this->memberClassName) {
            throw new InvalidConfigurationException('Member class name must be provided');
        }

        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $memberContactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $this->memberClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        /** @var From $from */
        $fromParts = $qb->getDQLPart('from');
        $from = reset($fromParts);

        $expr = $qb->expr()->orX();
        array_walk(
            $contactInformationFields,
            function ($field) use ($expr, $qb, $from, $memberContactInformationFields) {
                $property = sprintf('%s.%s', $from->getAlias(), $field);
                foreach ($memberContactInformationFields as $memberContactInformationField) {
                    $expr->add(
                        $qb->expr()->eq(
                            $property,
                            sprintf('%s.%s', self::MEMBER_ALIAS, $memberContactInformationField)
                        )
                    );
                }
            }
        );

        return $qb->leftJoin($this->memberClassName, self::MEMBER_ALIAS, Join::WITH, $expr);
    }

    /**
     * {@inheritdoc}
     */
    public function getStaticSegment()
    {
        return $this->getContext()->getOption(StaticSegmentAwareInterface::OPTION_SEGMENT);
    }

    /**
     * @return BufferedQueryResultIterator
     */
    abstract protected function getQueryIterator();
}
