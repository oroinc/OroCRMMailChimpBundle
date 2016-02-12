<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\WorkflowBundle\Model\EntityAwareInterface;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class UpdateEmailCampaignStatistics extends AbstractMarketingListEntitiesAction
{
    /**
     * @var EmailCampaignStatisticsConnector
     */
    protected $campaignStatisticsConnector;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param EmailCampaignStatisticsConnector $campaignStatisticsConnector
     */
    public function setCampaignStatisticsConnector(EmailCampaignStatisticsConnector $campaignStatisticsConnector)
    {
        $this->campaignStatisticsConnector = $campaignStatisticsConnector;
    }

    /**
     * @param ManagerRegistry $registry
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
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
        /** @var EntityManager $em */
        $em = $this->registry->getManager();
        foreach ($relatedEntities as $relatedEntity) {
            $emailCampaignStatistics = $this->getStatisticsRecord($emailCampaign, $relatedEntity, $em);

            $this->incrementStatistics($memberActivity, $emailCampaignStatistics);
            $em->persist($emailCampaignStatistics);
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
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (!$this->campaignStatisticsConnector) {
            throw new \InvalidArgumentException('EmailCampaignStatisticsConnector is not provided');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntitiesQueryBuilder(MarketingList $marketingList)
    {
        return $this->marketingListProvider
            ->getMarketingListEntitiesQueryBuilder($marketingList, MarketingListProvider::FULL_ENTITIES_MIXIN);
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @param object $relatedEntity
     * @param EntityManager $em
     * @return EmailCampaignStatistics
     */
    protected function getStatisticsRecord(EmailCampaign $emailCampaign, $relatedEntity, EntityManager $em)
    {
        $emailCampaignStatistics = $this->campaignStatisticsConnector->getStatisticsRecord(
            $emailCampaign,
            $relatedEntity
        );

        if ($em->getUnitOfWork()->getEntityState($emailCampaignStatistics) === UnitOfWork::STATE_DETACHED) {
            return $em->merge($emailCampaignStatistics);
        }

        return $emailCampaignStatistics;
    }
}
