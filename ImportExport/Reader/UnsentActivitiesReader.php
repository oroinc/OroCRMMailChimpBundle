<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;

class UnsentActivitiesReader extends IteratorBasedReader
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

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
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->getSourceIterator()) {
            $this->setSourceIterator($this->getQueryIterator());
        }
    }

    /**
     * @return \Iterator
     */
    protected function getQueryIterator()
    {
        $campaigns = $this->getProcessedCampaigns();
        if ($campaigns) {
            /** @var EntityManager $em */
            $em = $this->registry->getManager();
            $unsentEmailsQb = $em->createQueryBuilder();

            $unsentEmailsQb->select(
                [
                    'a1.email',
                    'IDENTITY(a1.campaign) as campaign_id',
                    'IDENTITY(a1.channel) as channel_id',
                    'IDENTITY(a1.member) as member_id'
                ]
            )
                ->from('OroCRMMailChimpBundle:MemberActivity', 'a1')
                ->leftJoin(
                    'OroCRMMailChimpBundle:MemberActivity',
                    'a2',
                    'WITH',
                    $unsentEmailsQb->expr()->andX(
                        $unsentEmailsQb->expr()->eq('a1.email', 'a2.email'),
                        $unsentEmailsQb->expr()->eq('a1.campaign', 'a2.campaign'),
                        $unsentEmailsQb->expr()->eq('a2.action', ':sendAction')
                    )
                )
                ->where($unsentEmailsQb->expr()->isNull('a2.id'))
                ->andWhere($unsentEmailsQb->expr()->IN('a1.campaign', ':campaigns'))
                ->setParameter('sendAction', 'send')
                ->setParameter('campaigns', $campaigns)
                ->addGroupBy('a1.email')
                ->addGroupBy('a1.campaign')
                ->addGroupBy('a1.channel')
                ->addGroupBy('a1.member');

            return new BufferedQueryResultIterator($unsentEmailsQb);
        } else {
            return new \ArrayIterator();
        }
    }

    /**
     * @return array
     */
    protected function getProcessedCampaigns()
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->stepExecution->getJobExecution();
        $processedCampaigns = (array)$jobExecution->getExecutionContext()->get('processed_campaigns');
        $jobExecution->getExecutionContext()->put('processed_campaigns', null);

        return $processedCampaigns;
    }
}
