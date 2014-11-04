<?php

namespace OroCRM\Bundle\MailChimpBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @Route("/mailchimp")
 */
class MailChimpController extends Controller
{
    /**
     * @Route("/ping", name="orocrm_mailchimp_ping")
     */
    public function pingAction()
    {
        $apiKey = $this->getRequest()->get('api_key');

        $mailChimpClientFactory = $this->get('orocrm_mailchimp.client.factory');
        $client = $mailChimpClientFactory->create($apiKey);
        try {
            $result = $client->ping();
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage()
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/sync-status/{marketingListId}", name="orocrm_mailchimp_sync_status", requirements={"marketingListId"="\d+"})
     * @ParamConverter("marketingList", class="OroCRMMarketingListBundle:MarketingList", options={"id" = "marketingListId"})
     * @Template
     */
    public function emailCampaignSyncStatusAction(MarketingList $marketingList)
    {
        return ['static_segment' => $this->getStaticSegment($marketingList)];
    }

    /**
     * @Route("/sync-status-badge/{marketingListId}", name="orocrm_mailchimp_sync_status_badge", requirements={"marketingListId"="\d+"})
     * @ParamConverter("marketingList", class="OroCRMMarketingListBundle:MarketingList", options={"id" = "marketingListId"})
     * @Template
     */
    public function emailCampaignSyncStatusBadgeAction(MarketingList $marketingList)
    {
        $staticSegment = $this->getStaticSegment($marketingList);
        return ['static_segment' => $staticSegment && $staticSegment->getSyncStatus() == 'synced'];
    }

    /**
     * @param MarketingList $marketingList
     * @return \OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment
     */
    protected function getStaticSegment(MarketingList $marketingList)
    {
        $staticSegment = $this->getDoctrine()
            ->getRepository('OroCRMMailChimpBundle:StaticSegment')
            ->findOneBy(['marketingList' => $marketingList]);

        return $staticSegment;
    }
}
