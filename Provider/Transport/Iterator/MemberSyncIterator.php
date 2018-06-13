<?php

namespace Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ImportExportBundle\Writer\AbstractNativeQueryWriter;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Component\PhpUtils\ArrayUtil;

class MemberSyncIterator extends AbstractStaticSegmentMembersIterator
{
    const EMAIL_SEPARATOR      = '__E__';
    const PHONE_SEPARATOR      = '__P__';
    const FIRST_NAME_SEPARATOR = '__F__';
    const LAST_NAME_SEPARATOR  = '__L__';

    /** @var MergeVarProviderInterface */
    protected $mergeVarsProvider;

    /** @var bool */
    protected $hasFirstName = false;

    /** @var bool */
    protected $hasLastName = false;

    /** @var string */
    protected $firstNameField;

    /** @var string */
    protected $lastNameField;

    /** @var array */
    protected $contactInformationFields;

    /** @var DQLNameFormatter */
    protected $formatter;

    /** @var string */
    protected $extendMergeVarsClass;

    /**
     * @param MergeVarProviderInterface $mergeVarsProvider
     *
     * @return MemberSyncIterator
     */
    public function setMergeVarsProvider(MergeVarProviderInterface $mergeVarsProvider)
    {
        $this->mergeVarsProvider = $mergeVarsProvider;

        return $this;
    }

    /**
     * @param DQLNameFormatter $formatter
     *
     * @return MemberSyncIterator
     */
    public function setFormatter(DQLNameFormatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param string $extendMergeVarsClass
     *
     * @return MemberSyncIterator
     */
    public function setExtendMergeVarsClass($extendMergeVarsClass)
    {
        $this->extendMergeVarsClass = $extendMergeVarsClass;

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

        $this->addMergeVars($qb, $staticSegment);

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

        $this->hasFirstName = false;
        $this->firstNameField = null;
        if (isset($parts['first_name'])) {
            $this->hasFirstName = true;
            $this->firstNameField = $parts['first_name'];
            $qb->addSelect($this->firstNameField . ' as first_name');
        }

        $this->hasLastName = false;
        $this->lastNameField = null;
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
        $this->contactInformationFields = $this->contactInformationFieldsProvider
            ->getMarketingListTypedFields($marketingList);

        return $this->contactInformationFields;
    }

    /**
     * Add merge prepared for insertion merge vars column.
     *
     * @param QueryBuilder  $qb
     * @param StaticSegment $staticSegment
     */
    protected function addMergeVars(QueryBuilder $qb, StaticSegment $staticSegment)
    {
        $columnInformation = $this->marketingListProvider->getColumnInformation($staticSegment->getMarketingList());
        $extendMergeVars = $this->getExtendMergeVars($qb->getEntityManager());

        /** @var ExtendedMergeVar[] $extendMergeVars */
        $extendMergeVars = array_filter(
            $extendMergeVars,
            function (ExtendedMergeVar $mergeVar) use ($columnInformation) {
                return array_key_exists($mergeVar->getName(), $columnInformation);
            }
        );

        $mergeVarsTemplate = $this->getMergeVarsTemplate($staticSegment);
        foreach ($extendMergeVars as $mergeVar) {
            $mergeVarsTemplate[$mergeVar->getTag()] = '__' . $mergeVar->getTag() . '__';
        }

        $hasTagField = ArrayUtil::some(
            function (ExtendedMergeVar $var) {
                return $var->getName() === 'tag_field';
            },
            $extendMergeVars
        );
        if ($hasTagField) {
            $qb
                ->join(
                    'OroTagBundle:Tagging',
                    'tagging',
                    'WITH',
                    'tagging.entityName = :entity_name AND tagging.recordId = t1.id'
                )
                ->join('tagging.tag', 'tag')
                ->setParameter('entity_name', $staticSegment->getMarketingList()->getEntity());
            $columnInformation['tag_field'] = 'GROUP_CONCAT(tag.name)';
        }

        $emailFieldExpr = $this->getEmailFieldExpression($qb, $staticSegment);
        $phoneFieldExpr = $this->getPhoneFieldExpression($qb, $staticSegment);

        // Prepare template to be SQL used in SQL CONCAT expression.
        $mergeVars = json_encode($mergeVarsTemplate);
        $mergeVars = $this->replaceSeparator($mergeVars, self::EMAIL_SEPARATOR, $emailFieldExpr);
        $mergeVars = $this->replaceSeparator($mergeVars, self::PHONE_SEPARATOR, $phoneFieldExpr);
        $mergeVars = $this->replaceSeparator($mergeVars, self::FIRST_NAME_SEPARATOR, $this->firstNameField);
        $mergeVars = $this->replaceSeparator($mergeVars, self::LAST_NAME_SEPARATOR, $this->lastNameField);
        foreach ($extendMergeVars as $mergeVar) {
            $mergeVars = $this->replaceSeparator(
                $mergeVars,
                '__' . $mergeVar->getTag() . '__',
                $columnInformation[$mergeVar->getName()]
            );
        }

        // If there is at leas one concat argument - CONCAT, if no - return as string
        $mergeVarsExpr = null;
        if (strpos($mergeVars, ', ') !== false) {
            $mergeVarsExpr = sprintf("CONCAT('%s')", $mergeVars);
        } else {
            $mergeVarsExpr = sprintf("'%s'", $mergeVars);
        }

        // On supported platform cast concat result as json to able to insert into compatible column
        if ($qb->getEntityManager()->getConnection()->getDatabasePlatform()->hasNativeJsonType()) {
            $mergeVarsExpr = 'CAST(' . $mergeVarsExpr . ' as json)';
        }

        if ($mergeVarsExpr) {
            $qb->addSelect($mergeVarsExpr . ' as merge_vars');
        }
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * @return array
     */
    protected function getMergeVarsTemplate(StaticSegment $staticSegment)
    {
        $mergeVarFields = $this->mergeVarsProvider->getMergeVarFields($staticSegment->getSubscribersList());
        $mergeVarsTemplate = [];

        // Prepare merge vars template
        $emailField = $mergeVarFields->getEmail();
        if ($emailField) {
            $mergeVarsTemplate[$emailField->getTag()] = self::EMAIL_SEPARATOR;
        }
        $phoneField = $mergeVarFields->getPhone();
        if ($phoneField) {
            $mergeVarsTemplate[$phoneField->getTag()] = self::PHONE_SEPARATOR;
        }
        $firstNameField = $mergeVarFields->getFirstName();
        if ($firstNameField) {
            $mergeVarsTemplate[$firstNameField->getTag()] = self::FIRST_NAME_SEPARATOR;
        }
        $lastNameField = $mergeVarFields->getLastName();
        if ($lastNameField) {
            $mergeVarsTemplate[$lastNameField->getTag()] = self::LAST_NAME_SEPARATOR;
        }

        return $mergeVarsTemplate;
    }

    /**
     * @param QueryBuilder  $qb
     * @param StaticSegment $staticSegment
     *
     * @return string
     */
    protected function getEmailFieldExpression(QueryBuilder $qb, StaticSegment $staticSegment)
    {
        return $this->getTypedFieldExpression($qb, $staticSegment, 'email');
    }

    /**
     * @param QueryBuilder  $qb
     * @param StaticSegment $staticSegment
     *
     * @return string
     */
    protected function getPhoneFieldExpression(QueryBuilder $qb, StaticSegment $staticSegment)
    {
        return $this->getTypedFieldExpression($qb, $staticSegment, 'phone');
    }

    /**
     * @param QueryBuilder  $qb
     * @param StaticSegment $staticSegment
     * @param string        $fieldType
     *
     * @return string
     */
    protected function getTypedFieldExpression(QueryBuilder $qb, StaticSegment $staticSegment, $fieldType)
    {
        $fieldExpr = '';
        $contactInformationFields = array_flip($this->contactInformationFields);
        if (isset($contactInformationFields[$fieldType])) {
            $fieldName = $contactInformationFields[$fieldType];
            $fieldExpr = $this->fieldHelper
                ->getFieldExpr($staticSegment->getMarketingList()->getEntity(), $qb, $fieldName);
        }

        return $fieldExpr;
    }

    /**
     * Replace separator in template with concat field expression.
     *
     * @param string $mergeVars
     * @param string $separator
     * @param string $value
     * @return string
     */
    protected function replaceSeparator($mergeVars, $separator, $value)
    {
        if ($value) {
            // CONCAT returns NULL if one of arguments is NULL - return empty string instead NULL.
            $value = "COALESCE(CAST(" . $value . " as text), '')";
            $mergeVars = str_replace($separator, sprintf("', %s ,'", $value), $mergeVars);
        } else {
            $mergeVars = str_replace(json_encode($separator), 'null', $mergeVars);
        }

        return $mergeVars;
    }

    /**
     * @param EntityManager $entityManager
     * @return ExtendedMergeVar[]
     */
    protected function getExtendMergeVars(EntityManager $entityManager)
    {
        return $entityManager->getRepository($this->extendMergeVarsClass)
            ->findAll();
    }
}
