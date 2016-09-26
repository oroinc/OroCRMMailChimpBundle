<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Functional\Model\Action;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WorkflowBundle\Model\ProcessData;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use Oro\Bundle\MailChimpBundle\Entity\MemberActivity;
use Oro\Bundle\MailChimpBundle\Model\Action\UpdateEmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

/**
 * @dbIsolation
 */
class UpdateEmailCampaignStatisticsTest extends WebTestCase
{
    /**
     * @var bool
     */
    protected $sceduled = false;

    /**
     * @var UpdateEmailCampaignStatistics
     */
    protected $action;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var MarketingListProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->initClient();

        $this->action = $this->getContainer()
            ->get('oro_mailchimp.workflow.action.update_email_campaign_statistics');

        $this->registry = $this->getContainer()->get('doctrine');

        $this->loadFixtures(
            [
                'Oro\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMemberActivityData'
            ]
        );
    }

    public function testExecute()
    {
        if ($this->sceduled) {
            $this->executeAction();
        }

        // Check that all marketing list items are created
        /** @var MarketingListItem[] $items */
        $items = $this->registry->getRepository('OroCRMMarketingListBundle:MarketingListItem')
            ->findAll();
        $this->assertCount(2, $items);

        // Check that statistics is updated correctly
        /** @var EmailCampaignStatistics[] $statistics */
        $statistics = $this->registry->getRepository('OroCRMCampaignBundle:EmailCampaignStatistics')
            ->findAll();
        $this->assertCount(3, $statistics);
        $statisticsData = [];
        foreach ($statistics as $record) {
            $statisticsData[$record->getEmailCampaign()->getId()][$record->getMarketingListItem()->getEntityId()] = [
                'opens' => $record->getOpenCount(),
                'clicks' => $record->getClickCount()
            ];
        }
        /** @var MemberActivity $activityOne */
        $activityOne = $this->getReference('mailchimp:member_one:activity:open');
        /** @var MemberActivity $activityTwo */
        $activityTwo = $this->getReference('mailchimp:member_two:activity:open:cmp2');

        $firstCampaign = $activityOne->getCampaign()->getEmailCampaign();
        $secondCampaign = $activityTwo->getCampaign()->getEmailCampaign();

        $firstAssignedEntity = $this->getReference('contact:' . $activityOne->getEmail());
        $secondAssignedEntity = $this->getReference('contact:' . $activityTwo->getEmail());

        $this->assertArrayHasKey($firstCampaign->getId(), $statisticsData);
        $this->assertArrayHasKey($secondCampaign->getId(), $statisticsData);

        $this->assertArrayHasKey($firstAssignedEntity->getId(), $statisticsData[$firstCampaign->getId()]);
        $this->assertArrayHasKey($secondAssignedEntity->getId(), $statisticsData[$firstCampaign->getId()]);
        $this->assertArrayHasKey($secondAssignedEntity->getId(), $statisticsData[$secondCampaign->getId()]);

        $this->assertEquals(
            ['opens' => 1, 'clicks' => 2],
            $statisticsData[$firstCampaign->getId()][$firstAssignedEntity->getId()]
        );
        $this->assertEquals(
            ['opens' => 1, 'clicks' => null],
            $statisticsData[$firstCampaign->getId()][$secondAssignedEntity->getId()]
        );
        $this->assertEquals(
            ['opens' => 1, 'clicks' => null],
            $statisticsData[$secondCampaign->getId()][$secondAssignedEntity->getId()]
        );
    }

    protected function executeAction()
    {
        $activities = [
            'mailchimp:member_one:activity:open',
            'mailchimp:member_one:activity:click:1',
            'mailchimp:member_one:activity:click:2',
            'mailchimp:member_two:activity:open',
            'mailchimp:member_two:activity:open:cmp2'
        ];

        // Process activities
        foreach ($activities as $activityReference) {
            $activity = $this->getReference($activityReference);
            $context = new ProcessData();
            $context->set('data', $activity);
            $this->action->execute($context);
        }
        $this->registry->getManager()->flush();
    }
}
