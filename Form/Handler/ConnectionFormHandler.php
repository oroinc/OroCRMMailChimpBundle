<?php

namespace Oro\Bundle\MailChimpBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\MailChimpBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConnectionFormHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param Request $request
     * @param ManagerRegistry $registry
     * @param FormInterface $form
     */
    public function __construct(Request $request, ManagerRegistry $registry, FormInterface $form)
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->form = $form;
    }

    /**
     * @param StaticSegment $entity
     * @return StaticSegment|null
     */
    public function process($entity)
    {
        $manager = $this->registry->getManagerForClass('OroMailChimpBundle:StaticSegment');

        $oldSubscribersListId = null;
        $oldStaticSegment = $entity;
        if ($oldStaticSegment->getSubscribersList()) {
            $oldSubscribersListId = $entity->getSubscribersList()->getId();
        }

        if ($this->request->isMethod(Request::METHOD_POST)) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                if ($entity->getId()) {
                    if ($entity->getSubscribersList()
                        && $entity->getSubscribersList()->getId() !== $oldSubscribersListId
                    ) {
                        $entity = $this->createSegmentCopy($oldStaticSegment);

                        if (!$this->campaignExistsForSegment($oldStaticSegment)) {
                            $manager->remove($oldStaticSegment);
                        } else {
                            $oldStaticSegment->setMarketingList(null);
                        }
                    } else {
                        $entity->setSyncStatus(StaticSegment::STATUS_SCHEDULED);
                    }
                }

                $manager->persist($entity);
                $manager->flush();

                return $entity;
            }
        }

        return null;
    }

    /**
     * @param StaticSegment $segment
     *
     * @return bool
     */
    protected function campaignExistsForSegment(StaticSegment $segment)
    {
        /** @var CampaignRepository $campaignRepository */
        $campaignRepository = $this->registry
            ->getManagerForClass('OroMailChimpBundle:Campaign')
            ->getRepository('OroMailChimpBundle:Campaign');

        return (bool)$campaignRepository->findOneBy(['staticSegment' => $segment]);
    }

    /**
     * @param StaticSegment $segment
     *
     * @return StaticSegment
     */
    protected function createSegmentCopy(StaticSegment $segment)
    {
        return (new StaticSegment())
            ->setChannel($segment->getChannel())
            ->setLastReset($segment->getLastReset())
            ->setMarketingList($segment->getMarketingList())
            ->setName($segment->getName())
            ->setOwner($segment->getOwner())
            ->setRemoteRemove($segment->getRemoteRemove())
            ->setSegmentMembers(new ArrayCollection())
            ->setSubscribersList($segment->getSubscribersList())
            ->setSyncStatus(StaticSegment::STATUS_NOT_SYNCED)
            ->setSyncedExtendedMergeVars(new ArrayCollection());
    }
}
