<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Command;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Command\MailChimpExportCommand;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegmentMember;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;

/**
 * @dbIsolation
 */
class MailChimpExportCommandTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures(
            [
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentMemberData',
            ]
        );
    }

    protected function tearDown()
    {
        // clear DB from separate connection
        $batchJobManager = $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobInstance')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobExecution')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:StepExecution')->execute();
    }

    /**
     * @param array $batchSubscribe
     * @param array $addStaticListSegment
     * @param array $addStaticSegmentMembers
     * @param array $deleteStaticSegmentMembers
     *
     * @dataProvider responseProvider
     */
    public function testJobsSuccessful(
        array $batchSubscribe,
        array $addStaticListSegment,
        array $addStaticSegmentMembers,
        array $deleteStaticSegmentMembers
    ) {
        $transport = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $transport->expects($this->any())
            ->method('batchSubscribe')
            ->will($this->returnValue($batchSubscribe));

        $transport->expects($this->any())
            ->method('addStaticListSegment')
            ->will($this->returnValue($addStaticListSegment));

        $transport->expects($this->any())
            ->method('addStaticSegmentMembers')
            ->will($this->returnValue($addStaticSegmentMembers));

        $transport->expects($this->any())
            ->method('deleteStaticSegmentMembers')
            ->will($this->returnValue($deleteStaticSegmentMembers));

        $this->getContainer()->set('orocrm_mailchimp.transport.integration_transport', $transport);

        // no failed jobs
        $this->assertEmpty($this->getJobs(MemberConnector::JOB_EXPORT, BatchStatus::FAILED));
        $this->assertEmpty($this->getJobs(StaticSegmentConnector::JOB_EXPORT, BatchStatus::FAILED));

        // 2 members from data fixtures, second one should be unsubscribed
        $this->assertMembers(2);

        // 1 segment with empty originId, should be subscribed
        $this->assertStaticSegment(1, 'assertEmpty');

        // 1 existing subscribed member
        $this->assertStaticSegmentMembers(2);

        $result = $this->runCommand(MailChimpExportCommand::NAME, ['--verbose' => true]);
        $this->assertNotEmpty($result);

        // unknown email should be ignored
        $this->assertContains('A member with "miranda.case@example.com" email was not found', $result);

        // no failed jobs
        $this->assertEmpty($this->getJobs(MemberConnector::JOB_EXPORT, BatchStatus::FAILED));
        $this->assertEmpty($this->getJobs(StaticSegmentConnector::JOB_EXPORT, BatchStatus::FAILED));

        // 2 members from data fixtures + 1 from marketing list
        $this->assertMembers(3);

        // 1 subscribed segment
        $this->assertStaticSegment(1, 'assertNotEmpty');

        // 1 existing subscribed member, member2 excluded from ML and was dropped
        // john.doe should be removed from segment as its not longer in ML
        $this->assertStaticSegmentMembers(2, 'john.doe@example.com');
    }

    /**
     * @param int $count
     */
    protected function assertMembers($count)
    {
        $members = $this->getMembers();
        $this->assertCount($count, $members);
        foreach ($members as $member) {
            $this->assertNotEmpty($member->getOriginId());
        }
    }

    /**
     * @param int    $count
     * @param string $method
     */
    protected function assertStaticSegment($count, $method)
    {
        $staticSegments = $this->getStaticSegment();
        foreach ($staticSegments as $staticSegment) {
            $this->$method($staticSegment->getOriginId());
        }
        $this->assertCount($count, $this->getStaticSegment());
    }

    /**
     * @param int $count
     * @param string|null $excludedEmail
     */
    protected function assertStaticSegmentMembers($count, $excludedEmail = null)
    {
        $staticSegmentMembers = $this->getStaticSegmentMember();
        $this->assertCount($count, $staticSegmentMembers);
        foreach ($staticSegmentMembers as $staticSegmentMember) {
            if ($excludedEmail) {
                $this->assertNotEquals($staticSegmentMember->getMember()->getEmail(), $excludedEmail);
            }
            $this->assertEquals(StaticSegmentMember::STATE_SYNCED, $staticSegmentMember->getState());
        }
    }

    /**
     * @return array
     */
    public function responseProvider()
    {
        return [
            [
                'batchSubscribe' => [
                    'adds' => [
                        [
                            'email' => 'daniel.case@example.com',
                            'euid' => time(),
                            'leid' => time(),
                        ]
                    ],
                    'updates' => [
                        [   // used to test a case when returned my MailChimp email does not exist in Oro
                            'email' => 'miranda.case@example.com',
                            'euid' => null,
                            'leid' => null
                        ]
                    ],
                    'add_count' => 1,
                    'update_count' => 0,
                    'error_count' => 0,
                    'errors' => [],
                ],
                'addStaticListSegment' => [
                    'id' => time(),
                ],
                'addStaticSegmentMembers' => [
                    'success_count' => 1,
                    'error_count' => 0,
                    'errors' => [],
                ],
                'deleteStaticSegmentMembers' => [
                    'success_count' => 1,
                    'error_count' => 0,
                    'errors' => [],
                ],
                'resultMembers' => [1],
            ]
        ];
    }

    /**
     * @return Member[]
     */
    protected function getMembers()
    {
        return $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('OroCRMMailChimpBundle:Member')
            ->findAll();
    }

    /**
     * @return StaticSegment[]
     */
    protected function getStaticSegment()
    {
        return $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('OroCRMMailChimpBundle:StaticSegment')
            ->findAll();
    }

    /**
     * @return StaticSegmentMember[]
     */
    protected function getStaticSegmentMember()
    {
        return $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('OroCRMMailChimpBundle:StaticSegmentMember')
            ->findAll();
    }

    /**
     * @param string $alias
     * @param string $status
     *
     * @return JobExecution[]
     */
    protected function getJobs($alias, $status)
    {
        $qb = $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('AkeneoBatchBundle:JobInstance')
            ->createQueryBuilder('job');

        $qb
            ->select('job')
            ->leftJoin('job.jobExecutions', 'jobExecutions')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('job.alias', ':alias'),
                    $qb->expr()->eq('jobExecutions.status', ':status')
                )
            )
            ->setParameter('alias', $alias)
            ->setParameter('status', $status);

        return $qb->getQuery()->getResult();
    }
}
