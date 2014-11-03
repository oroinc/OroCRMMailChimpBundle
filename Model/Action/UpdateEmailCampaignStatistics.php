<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\EntityAwareInterface;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class UpdateEmailCampaignStatistics extends AbstractAction
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var EmailCampaignStatisticsConnector
     */
    protected $campaignStatisticsConnector;

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param EmailCampaignStatisticsConnector $campaignStatisticsConnector
     * @param MarketingListProvider $marketingListProvider
     * @param FieldHelper $fieldHelper
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        EmailCampaignStatisticsConnector $campaignStatisticsConnector,
        MarketingListProvider $marketingListProvider,
        FieldHelper $fieldHelper
    ) {
        parent::__construct($contextAccessor);

        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->campaignStatisticsConnector = $campaignStatisticsConnector;
        $this->marketingListProvider = $marketingListProvider;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function isAllowed($context)
    {
        $isAllowed = false;
        if ($context instanceof EntityAwareInterface) {
            $entity = $context->getEntity();
            if ($entity instanceof MemberActivity) {
                $mailChimpCampaign = $entity->getCampaign();
                $isAllowed = $mailChimpCampaign
                    && $mailChimpCampaign->getEmailCampaign()
                    && $mailChimpCampaign->getStaticSegment()
                    && $mailChimpCampaign->getStaticSegment()->getMarketingList();
            }
        }

        return $isAllowed && parent::isAllowed($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $this->updateStatistics($context->getEntity());
    }

    /**
     * @param MemberActivity $memberActivity
     */
    protected function updateStatistics(MemberActivity $memberActivity)
    {
        $mailChimpCampaign = $memberActivity->getCampaign();
        $emailCampaign = $mailChimpCampaign->getEmailCampaign();
        $marketingList = $mailChimpCampaign->getStaticSegment()->getMarketingList();

        $relatedEntities = $this->getMarketingListEntitiesByEmail($marketingList, $memberActivity->getEmail());
        foreach ($relatedEntities as $relatedEntity) {
            $emailCampaignStatistics = $this->campaignStatisticsConnector->getStatisticsRecord(
                $emailCampaign,
                $relatedEntity
            );

            $this->incrementStatistics($memberActivity, $emailCampaignStatistics);
        }
    }

    /**
     * @param MemberActivity $memberActivity
     * @param EmailCampaignStatistics $emailCampaignStatistics
     */
    protected function incrementStatistics(
        MemberActivity $memberActivity,
        EmailCampaignStatistics $emailCampaignStatistics
    ) {
        switch ($memberActivity->getAction()) {
            case MemberActivity::ACTIVITY_SENT:
                $marketingListItem = $emailCampaignStatistics->getMarketingListItem();
                $marketingListItem->setLastContactedAt($memberActivity->getActivityTime());
                $marketingListItem->setContactedTimes((int)$marketingListItem->getContactedTimes() + 1);
                break;
            case MemberActivity::ACTIVITY_OPEN:
                $emailCampaignStatistics->incrementOpenCount();
                break;
            case MemberActivity::ACTIVITY_CLICK:
                $emailCampaignStatistics->incrementClickCount();
                break;
            case MemberActivity::ACTIVITY_BOUNCE:
                $emailCampaignStatistics->incrementBounceCount();
                break;
            case MemberActivity::ACTIVITY_ABUSE:
                $emailCampaignStatistics->incrementAbuseCount();
                break;
            case MemberActivity::ACTIVITY_UNSUB:
                $emailCampaignStatistics->incrementUnsubscribeCount();
                break;
        }
    }

    /**
     * @param MarketingList $marketingList
     * @param string $email
     * @return array
     */
    protected function getMarketingListEntitiesByEmail($marketingList, $email)
    {
        $emailFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $qb = $this->marketingListProvider->getMarketingListEntitiesQueryBuilder($marketingList);

        foreach ($emailFields as $emailField) {
            $parameterName = $emailField . mt_rand();
            $qb->andWhere(
                $qb->expr()->eq(
                    $this->fieldHelper->getFieldExpr($marketingList->getEntity(), $qb, $emailField),
                    ':' . $parameterName
                )
            )->setParameter($parameterName, $email, Type::STRING);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        return $this;
    }
}
