<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\AbstractNativeQueryWriter;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MemberSyncIterator extends AbstractStaticSegmentMembersIterator
{
    const EMAIL_SEPARATOR = '__E__';
    const FIRST_NAME_SEPARATOR = '__F__';
    const LAST_NAME_SEPARATOR = '__L__';

    /**
     * @var MergeVarProviderInterface
     */
    protected $mergeVarsProvider;

    /**
     * @var bool
     */
    protected $hasFirstName = false;

    /**
     * @var bool
     */
    protected $hasLastName = false;

    /**
     * @var string
     */
    protected $firstNameField;

    /**
     * @var string
     */
    protected $lastNameField;

    /**
     * @var array
     */
    protected $contactInformationFields;

    /**
     * @var DQLNameFormatter
     */
    protected $formatter;

    /**
     * @param MergeVarProviderInterface $mergeVarsProvider
     * @return MemberSyncIterator
     */
    public function setMergeVarsProvider(MergeVarProviderInterface $mergeVarsProvider)
    {
        $this->mergeVarsProvider = $mergeVarsProvider;

        return $this;
    }

    /**
     * @param DQLNameFormatter $formatter
     * @return MemberSyncIterator
     */
    public function setFormatter(DQLNameFormatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Return query builder instead of BufferedQueryResultIterator.
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        return new \ArrayIterator(
            [
                [
                    AbstractNativeQueryWriter::QUERY_BUILDER => $qb,
                    'subscribers_list_id' => $staticSegment->getSubscribersList()->getId(),
                    'has_first_name' => $this->hasFirstName,
                    'has_last_name' => $this->hasLastName
                ]
            ]
        );
    }

    /**
     * Add required fields.
     *
     * Fields: first_name, last_name, email, owner_id, subscribers_list_id, channel_id, status, merge_var_values
     *
     * {@inheritdoc}
     */
    protected function getIteratorQueryBuilder(StaticSegment $staticSegment)
    {
        $qb = parent::getIteratorQueryBuilder($staticSegment);

        $this->addNameFields($staticSegment->getMarketingList()->getEntity(), $qb);

        $subscribersListId = $staticSegment->getSubscribersList()->getId();
        $ownerId = $staticSegment->getOwner()->getId();
        $channelId = $staticSegment->getChannel()->getId();

        $qb->addSelect($ownerId . ' as owner_id');
        $qb->addSelect($subscribersListId . ' as subscribers_list_id');
        $qb->addSelect($channelId . ' as channel_id');
        $qb->addSelect(sprintf("'%s' as status", Member::STATUS_EXPORT));
        $qb->addSelect('CURRENT_TIMESTAMP() as created_at');

        //$this->addMergeVars($qb, $staticSegment);

        // Select only members that are not in list yet
        $qb->andWhere($qb->expr()->isNull(sprintf('%s.id', self::MEMBER_ALIAS)));

        return $qb;
    }

    /**
     * Always add first_name and last_name to select, as them will be used for INSERT FROM SELECT later.
     *
     * {@inheritdoc}
     */
    protected function addNameFields($entityName, QueryBuilder $qb)
    {
        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();
        $parts = $this->formatter->extractNamePartsPaths($entityName, $entityAlias);

        if (isset($parts['first_name'])) {
            $this->hasFirstName = true;
            $this->firstNameField = $parts['first_name'];
            $qb->addSelect($this->firstNameField . ' as first_name');
        }
        if (isset($parts['last_name'])) {
            $this->hasLastName = true;
            $this->lastNameField = $parts['last_name'];
            $qb->addSelect($this->lastNameField . ' as last_name');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getContactInformationFields(MarketingList $marketingList)
    {
        $this->contactInformationFields = parent::getContactInformationFields($marketingList);

        return $this->contactInformationFields;
    }

    /**
     * @param QueryBuilder $qb
     * @param StaticSegment $staticSegment
     */
    protected function addMergeVars(QueryBuilder $qb, StaticSegment $staticSegment)
    {
        $mergeVarFields = $this->mergeVarsProvider->getMergeVarFields($staticSegment->getSubscribersList());
        $mergeVarsTemplate = [];

        $mergeVarsTemplate[$mergeVarFields->getEmail()->getTag()] = self::EMAIL_SEPARATOR;
        $mergeVarsTemplate[$mergeVarFields->getFirstName()->getTag()] = self::FIRST_NAME_SEPARATOR;
        $mergeVarsTemplate[$mergeVarFields->getLastName()->getTag()] = self::LAST_NAME_SEPARATOR;

        $emailFieldExpr = $this->getEmailFieldExpression($qb, $staticSegment);
        $mergeVars = json_encode($mergeVarsTemplate);

        $mergeVars = $this->replaceSeparator($mergeVars, self::EMAIL_SEPARATOR, $emailFieldExpr);
        $mergeVars = $this->replaceSeparator($mergeVars, self::FIRST_NAME_SEPARATOR, $this->firstNameField);
        $mergeVars = $this->replaceSeparator($mergeVars, self::LAST_NAME_SEPARATOR, $this->lastNameField);

        if (strpos($mergeVars, ', ') !== false) {
            $qb->addSelect(sprintf("CONCAT('%s') as merge_vars", $mergeVars));
        } else {
            $qb->addSelect(sprintf("'%s' as merge_vars", $mergeVars));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param StaticSegment $staticSegment
     * @return string
     */
    protected function getEmailFieldExpression(QueryBuilder $qb, StaticSegment $staticSegment)
    {
        $emailField = reset($this->contactInformationFields);
        $emailFieldExpr = $this->fieldHelper
            ->getFieldExpr($staticSegment->getMarketingList()->getEntity(), $qb, $emailField);

        return $emailFieldExpr;
    }

    /**
     * @param string $mergeVars
     * @param string $separator
     * @param string $value
     * @return string
     */
    protected function replaceSeparator($mergeVars, $separator, $value)
    {
        if ($value) {
            $mergeVars = str_replace($separator, sprintf("', %s ,'", $value), $mergeVars);
        } else {
            $mergeVars = str_replace(json_encode($separator), 'null', $mergeVars);

        }

        return $mergeVars;
    }
}
