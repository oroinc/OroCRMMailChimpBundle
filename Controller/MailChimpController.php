<?php

namespace Oro\Bundle\MailChimpBundle\Controller;

use Doctrine\Common\Util\ClassUtils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Form\Handler\ConnectionFormHandler;
use Oro\Bundle\MailChimpBundle\Form\Type\MarketingListConnectionType;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @Route("/mailchimp")
 */
class MailChimpController extends Controller
{
    /**
     * @Route("/ping", name="oro_mailchimp_ping")
     * @AclAncestor("oro_mailchimp")
     * @param Request $request
     * @return JsonResponse
     */
    public function pingAction(Request $request)
    {
        $apiKey = $request->get('api_key');

        $mailChimpClientFactory = $this->get('oro_mailchimp.client.factory');
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
     *      name="oro_mailchimp_marketing_list_connect",
     *      requirements={"id"="\d+"}
     * )
     * @AclAncestor("oro_mailchimp")
     *
     * @Template
     * @param MarketingList $marketingList
     * @param Request $request
     * @return array
     */
    public function manageConnectionAction(MarketingList $marketingList, Request $request)
    {
        $staticSegment = $this->getStaticSegmentByMarketingList($marketingList);
        $form = $this->createForm(MarketingListConnectionType::NAME, $staticSegment);
        $handler = new ConnectionFormHandler($request, $this->getDoctrine(), $form);

        $result = [];
        if ($savedSegment = $handler->process($staticSegment)) {
            $result['savedId'] = $savedSegment->getId();
            $staticSegment = $savedSegment;
        }

        $result['entity'] = $staticSegment;
        $result['form'] = $form->createView();

        return $result;
    }

    /**
     * @ParamConverter(
     *      "marketingList",
     *      class="OroCRMMarketingListBundle:MarketingList",
     *      options={"id" = "entity"}
     * )
     * @AclAncestor("oro_mailchimp")
     *
     * @Template
     *
     * @param MarketingList $marketingList
     * @return array
     */
    public function connectionButtonsAction(MarketingList $marketingList)
    {
        return [
            'marketingList' => $marketingList,
            'staticSegment' => $this->getStaticSegmentByMarketingList($marketingList),
        ];
    }

    /**
     * @Route("/sync-status/{marketingList}",
     *      name="oro_mailchimp_sync_status",
     *      requirements={"marketingList"="\d+"})
     * @ParamConverter("marketingList",
     *      class="OroCRMMarketingListBundle:MarketingList",
     *      options={"id" = "marketingList"})
     * @AclAncestor("oro_mailchimp")
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
     * @Route("/email-campaign-status-positive/{entity}",
     *      name="oro_mailchimp_email_campaign_status",
     *      requirements={"entity"="\d+"})
     * @ParamConverter("emailCampaign",
     *      class="OroCampaignBundle:EmailCampaign",
     *      options={"id" = "entity"})
     * @AclAncestor("oro_mailchimp")
     *
     * @Template
     *
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    public function emailCampaignStatsAction(EmailCampaign $emailCampaign)
    {
        $campaign = $this->getCampaignByEmailCampaign($emailCampaign);

        return ['campaignStats' => $campaign];
    }

    /**
     * @ParamConverter(
     *      "emailCampaign",
     *      class="OroCampaignBundle:EmailCampaign",
     *      options={"id" = "entity"}
     * )
     * @AclAncestor("oro_mailchimp")
     *
     * @Template
     *
     * @param EmailCampaign $emailCampaign
     * @return array
     */
    public function emailCampaignActivityUpdateButtonsAction(EmailCampaign $emailCampaign)
    {
        return [
            'emailCampaign' => $emailCampaign,
            'campaign' => $this->getCampaignByEmailCampaign($emailCampaign)
        ];
    }

    /**
     * @Route("/email-campaign/{id}/activity-updates/toggle",
     *      name="oro_mailchimp_email_campaign_activity_update_toggle",
     *      requirements={"id"="\d+"})
     * @AclAncestor("oro_mailchimp")
     *
     * @param EmailCampaign $emailCampaign
     * @return JsonResponse
     */
    public function toggleUpdateStateAction(EmailCampaign $emailCampaign)
    {
        /** @var MailChimpTransportSettings $settings */
        $settings = $emailCampaign->getTransportSettings();
        $settings->setReceiveActivities(!$settings->isReceiveActivities());

        $em = $this->getDoctrine()->getManagerForClass(ClassUtils::getClass($settings));
        $em->persist($settings);
        $em->flush();

        if ($settings->isReceiveActivities()) {
            $message = 'oro.mailchimp.controller.email_campaign.receive_activities.enabled.message';
        } else {
            $message = 'oro.mailchimp.controller.email_campaign.receive_activities.disabled.message';
        }

        return new JsonResponse(['message' => $this->get('translator')->trans($message)]);
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
            ->getRepository('OroMailChimpBundle:StaticSegment')
            ->findOneBy(['marketingList' => $marketingList]);
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return Campaign
     */
    protected function getCampaignByEmailCampaign(EmailCampaign $emailCampaign)
    {
        $campaign = $this->getDoctrine()
            ->getRepository('OroMailChimpBundle:Campaign')
            ->findOneBy(['emailCampaign' => $emailCampaign]);

        return $campaign;
    }
}
