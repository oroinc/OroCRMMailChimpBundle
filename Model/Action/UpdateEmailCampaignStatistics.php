<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Doctrine\ORM\Query\Expr\From;

use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\EntityAwareInterface;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;
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
     * @param ContextAccessor $contextAccessor
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param EmailCampaignStatisticsConnector $campaignStatisticsConnector
     * @param MarketingListProvider $marketingListProvider
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        EmailCampaignStatisticsConnector $campaignStatisticsConnector,
        MarketingListProvider $marketingListProvider
    ) {
        parent::__construct($contextAccessor);

        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->campaignStatisticsConnector = $campaignStatisticsConnector;
        $this->marketingListProvider = $marketingListProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        if ($context instanceof EntityAwareInterface) {
            $entity = $context->getEntity();
            if ($entity instanceof MemberActivity) {
                $this->updateStatistics($entity);
            }
        }
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
                $emailCampaignStatistics->setOpenCount((int)$emailCampaignStatistics->getOpenCount() + 1);
                break;
            case MemberActivity::ACTIVITY_CLICK:
                $emailCampaignStatistics->setClickCount((int)$emailCampaignStatistics->getClickCount() + 1);
                break;
            case MemberActivity::ACTIVITY_BOUNCE:
                $emailCampaignStatistics->setBounceCount($emailCampaignStatistics->getBounceCount() + 1);
                break;
            case MemberActivity::ACTIVITY_ABUSE:
                $emailCampaignStatistics->setAbuseCount($emailCampaignStatistics->getAbuseCount() + 1);
                break;
            case MemberActivity::ACTIVITY_UNSUB:
                $emailCampaignStatistics->setUnsubscribeCount(
                    (int)$emailCampaignStatistics->getUnsubscribeCount() + 1
                );
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

        $fromParts = $qb->getDQLPart('from');
        /** @var From $from */
        $from = reset($fromParts);
        $alias = $from->getAlias();

        foreach ($emailFields as $emailField) {
            $qb->andWhere($qb->expr()->eq($alias . '.' . $emailField, $email));
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
