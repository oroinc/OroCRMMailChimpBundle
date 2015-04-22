<?php

namespace OroCRM\Bundle\MailChimpBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * @Route("/mailchimp")
 */
class MailChimpController extends Controller
{
    /**
     * @Route("/ping", name="orocrm_mailchimp_ping")
     * @AclAncestor("orocrm_mailchimp")
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
     * @Route(
     *      "/manage-connection/marketing-list/{id}",
     *      name="orocrm_mailchimp_marketing_list_connect",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("orocrm_mailchimp")
     *
     * @Template
     * @param MarketingList $marketingList
     * @return array
     */
    public function manageConnectionAction(MarketingList $marketingList)
    {
        $staticSegment = $this->getStaticSegmentByMarketingList($marketingList);

        /** @var Form $form */
        $form = $this->get('orocrm_mailchimp.form.marketing_list_connecion');
        /** @var ApiFormHandler $handler */
        $handler = $this->get('orocrm_mailchimp.form.handler.connection_form');

        $result = ['entity' => $staticSegment];
        if ($handler->process($staticSegment)) {
            $result['savedId'] = $staticSegment->getId();
        }
        $result['form'] = $form->createView();

        return $result;
    }

    /**
     * @Route(
     *      "/marketing-list/buttons/{entity}",
     *      name="orocrm_mailchimp_marketing_list_buttons",
     *      requirements={"entity"="\d+"}
     * )
     * @ParamConverter(
     *      "marketingList",
     *      class="OroCRMMarketingListBundle:MarketingList",
     *      options={"id" = "entity"}
     * )
     * @AclAncestor("orocrm_mailchimp")
     *
     * @Template
     *
     * @param MarketingList $marketingList
     * @return array
     */
    public function connectionButtonsAction(MarketingList $marketingList)
    {
        $isAbandonedCart = false;

        if ($this->get('orocrm_abandonedcart.provider')->getAbandonedCartCampaign($marketingList)) {
            $isAbandonedCart = true;
        }

        return [
            'marketingList' => $marketingList,
            'staticSegment' => $this->getStaticSegmentByMarketingList($marketingList),
            'isAbandonedCart' => $isAbandonedCart
        ];
    }

    /**
     * @Route("/sync-status/{marketingList}",
     *      name="orocrm_mailchimp_sync_status",
     *      requirements={"marketingList"="\d+"})
     * @ParamConverter("marketingList",
     *      class="OroCRMMarketingListBundle:MarketingList",
     *      options={"id" = "marketingList"})
     * @AclAncestor("orocrm_mailchimp")
     *
     * @Template
     *
     * @param MarketingList $marketingList
     * @return array
     */
    public function marketingListSyncStatusAction(MarketingList $marketingList)
    {
        return ['static_segment' => $this->findStaticSegmentByMarketingList($marketingList)];
    }

    /**
     * @param MarketingList $marketingList
     * @return StaticSegment
     */
    protected function getStaticSegmentByMarketingList(MarketingList $marketingList)
    {
        $staticSegment = $this->findStaticSegmentByMarketingList($marketingList);

        if (!$staticSegment) {
            $staticSegment = new StaticSegment();
            $staticSegment->setName(mb_substr($marketingList->getName(), 0, 100));
            $staticSegment->setSyncStatus(StaticSegment::STATUS_NOT_SYNCED);
            $staticSegment->setMarketingList($marketingList);
        }

        return $staticSegment;
    }

    /**
     * @param MarketingList $marketingList
     * @return StaticSegment
     */
    protected function findStaticSegmentByMarketingList(MarketingList $marketingList)
    {
        return $this->getDoctrine()
            ->getRepository('OroCRMMailChimpBundle:StaticSegment')
            ->findOneBy(['marketingList' => $marketingList]);
    }

    /**
     * @Route("/email-campaign-status-positive/{entity}",
     *      name="orocrm_mailchimp_email_campaign_status",
     *      requirements={"entity"="\d+"})
     * @ParamConverter("emailCampaign",
     *      class="OroCRMCampaignBundle:EmailCampaign",
     *      options={"id" = "entity"})
     * @AclAncestor("orocrm_mailchimp")
     *
     * @Template
     *
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    public function emailCampaignStatsAction(EmailCampaign $emailCampaign)
    {
        $campaign = $this->getDoctrine()
            ->getRepository('OroCRMMailChimpBundle:Campaign')
            ->findOneBy(['emailCampaign' => $emailCampaign]);
        return ['campaignStats' => $campaign];
    }
}
