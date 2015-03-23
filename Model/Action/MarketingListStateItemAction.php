<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListStateItemInterface;

class MarketingListStateItemAction extends AbstractMarketingListEntitiesAction
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $marketingListStateItemClassName;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param string $marketingListStateItemClassName
     */
    public function setMarketingListStateItemClassName($marketingListStateItemClassName)
    {
        $this->marketingListStateItemClassName = $marketingListStateItemClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $entitiesByClassName = $this->getMarketingListStateItems($context->getEntity());

        foreach ($entitiesByClassName as $className => $entities) {
            $em = $this->doctrineHelper->getEntityManager($className);
            foreach ($entities as $entity) {
                $em->persist($entity);
            }

            $em->flush($entities);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (!$this->doctrineHelper) {
            throw new \InvalidArgumentException('DoctrineHelper is not provided');
        }

        if (!$this->marketingListStateItemClassName) {
            throw new \InvalidArgumentException('marketingListStateItemClassName is not provided');
        }

        return $this;
    }

    /**
     * @param SubscribersList $subscriberList
     * @return BufferedQueryResultIterator|MarketingList[]
     */
    protected function getMarketingListIterator(SubscribersList $subscriberList)
    {
        $qb = $this->doctrineHelper
            ->getEntityManager('OroCRMMarketingListBundle:MarketingList')
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->createQueryBuilder('ml');

        $qb
            ->select('ml')
            ->join(
                'OroCRMMailChimpBundle:StaticSegment',
                'staticSegment',
                Join::WITH,
                'staticSegment.marketingList = ml.id'
            )
            ->join('staticSegment.subscribersList', 'subscribersList')
            ->where($qb->expr()->eq('subscribersList.id', ':subscribersList'))
            ->setParameter('subscribersList', $subscriberList->getId());

        return new BufferedQueryResultIterator($qb);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntitiesQueryBuilder(MarketingList $marketingList)
    {
        $className = $marketingList->getEntity();

        $qb = $this->doctrineHelper
            ->getEntityManager($className)
            ->getRepository($className)
            ->createQueryBuilder('e');

        return $qb;
    }

    /**
     * @param Member $member
     * @return MarketingListStateItemInterface[]
     */
    protected function getMarketingListStateItems(Member $member)
    {
        $entities = [];

        if (!$subscribersList = $member->getSubscribersList()) {
            return $entities;
        }

        $marketingLists = $this->getMarketingListIterator($subscribersList);
        foreach ($marketingLists as $marketingList) {
            $marketingListEntities = $this->getMarketingListEntitiesByEmail($marketingList, $member->getEmail());

            /** @var MarketingList $marketingListEntity */
            foreach ($marketingListEntities as $marketingListEntity) {
                $entityId = $this->doctrineHelper->getSingleEntityIdentifier($marketingListEntity);

                $criteria = [
                    'entityId' => $entityId,
                    'entity' => $marketingList->getEntity(),
                    'marketingList' => $marketingList->getId()
                ];

                if ($this->getMarketingListStateItem($criteria)) {
                    continue;
                }

                /** @var MarketingListStateItemInterface $marketingListStateItem */
                $marketingListStateItem = new $this->marketingListStateItemClassName();

                $marketingListStateItem
                    ->setEntityId($entityId)
                    ->setMarketingList($marketingList);

                $entities[$marketingList->getEntity()][] = $marketingListStateItem;
            }
        }

        return $entities;
    }

    /**
     * @param array $criteria
     * @return MarketingListStateItemInterface|null
     */
    protected function getMarketingListStateItem(array $criteria)
    {
        return $this->doctrineHelper
            ->getEntityManager($this->marketingListStateItemClassName)
            ->getRepository($this->marketingListStateItemClassName)
            ->findOneBy($criteria);
    }
}
