<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Command;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Command\MailChimpExportCommand;
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
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadStaticSegmentData',
            ]
        );
    }

    public function testJobsSuccessful()
    {
        $this->assertEmpty($this->getJobs(MemberConnector::JOB_EXPORT, BatchStatus::FAILED));
        $this->assertEmpty($this->getJobs(StaticSegmentConnector::JOB_EXPORT, BatchStatus::FAILED));

        $result = $this->runCommand(MailChimpExportCommand::NAME);
        $this->assertNotEmpty($result);

        $this->assertEmpty($this->getJobs(MemberConnector::JOB_EXPORT, BatchStatus::FAILED));
        $this->assertEmpty($this->getJobs(StaticSegmentConnector::JOB_EXPORT, BatchStatus::FAILED));
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
