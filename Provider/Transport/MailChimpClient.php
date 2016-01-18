<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport;

use Guzzle\Http\Message\Response;
use Guzzle\Service\Description\Operation;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Service\Description\ServiceDescriptionInterface;
use ZfrMailChimp\Client\MailChimpClient as BaseClient;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Exception\BadResponseException;

// @codingStandardsIgnoreStart
/**
 * @link http://apidocs.mailchimp.com/api/2.0/
 * @link http://apidocs.mailchimp.com/export/1.0/
 *
 * @method array getCampaignUnsubscribesReport (array $args = array()) {@command MailChimp GetCampaignUnsubscribesReport}
 * @method array getCampaignSentToReport (array $args = array()) {@command MailChimp GetCampaignSentToReport}
 */
// @codingStandardsIgnoreEnd
class MailChimpClient extends BaseClient
{
    /**#@+
     * @const string Export API method names
     */
    /**
     * Dumps a full list or a segment of a list
     */
    const EXPORT_LIST = 'list';

    /**
     * Dumps all Ecommerce Orders for an account
     */
    const EXPORT_ECOMM_ORDERS = 'ecommOrders';

    /**
     * Dumps all Subscriber Activity for the requested campaign
     */
    const EXPORT_CAMPAIGN_SUBSCRIBER_ACTIVITY = 'campaignSubscriberActivity';
    /**#@-*/

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $version;

    /**
     * MailChimp Export API version
     * @link http://apidocs.mailchimp.com/export/1.0/
     */
    const EXPORT_API_VERSION = '1.0';

    /**
     * @param string $apiKey
     * @param string $version
     */
    public function __construct($apiKey, $version = BaseClient::LATEST_API_VERSION)
    {
        $this->apiKey = $apiKey;
        $this->version = $version;

        BaseClient::__construct($apiKey, $version);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(ServiceDescriptionInterface $service)
    {
        $this->addAdditionalOperations($service);

        return parent::setDescription($service);
    }

    /**
     * Add additional operations to service.
     *
     * @param ServiceDescriptionInterface $service
     */
    protected function addAdditionalOperations(ServiceDescriptionInterface $service)
    {
        if ($operations = $this->loadOperations()) {
            foreach ($operations as $name => $config) {
                if (!$service->hasOperation($name)) {
                    if (empty($config['name'])) {
                        $config['name'] = $name;
                    }
                    /** @var ServiceDescription $service */
                    $service->addOperation(new Operation($config, $service));
                }
            }
        }
    }

    /**
     * Load additional operations config.
     *
     * @return array
     */
    protected function loadOperations()
    {
        $fileName = sprintf(__DIR__ . '/Operations/MailChimp-%s.php', $this->version);
        if (!is_readable($fileName)) {
            return [];
        }

        $config = require $fileName;
        if (!is_array($config)) {
            return [];
        }

        return $config;
    }

    /**
     * Execute exports API request.
     *
     * @param string $methodName Name of the export method - one of (list, ecommOrders, campaignSubscriberActivity)
     * @param array $parameters Parameters of export method
     * @return Response A plain text dump of JSON objects. The first row is a header row. Each additional row returned
     *  is an individual JSON object. Rows are delimited using a newline (\n) marker, so implementations can read in a
     *  single line at a time, handle it, and move on.
     */
    public function export($methodName, array $parameters)
    {
        $url = $this->getExportAPIMethodUrl($methodName);
        $parameters = array_merge(['apikey' => $this->apiKey], $parameters);

        $request = $this->createRequest(
            'POST',
            $url,
            ['Content-Type' => 'application/json'],
            json_encode($parameters)
        );

        $response = $request->send();

        if (!$response->isSuccessful()) {
            throw BadResponseException::factory(
                $request,
                $response,
                'Request to MailChimp Export API wasn\'t successfully completed.'
            );
        }

        if (0 !== strpos($response->getContentType(), 'text/html')) {
            throw BadResponseException::factory(
                $request,
                $response,
                'Invalid response, expected content type is text/html'
            );
        }

        return $response;
    }

    /**
     * Pass export API method name and you'll get it's URL.
     *
     * @param string $methodName
     * @return string
     */
    protected function getExportAPIMethodUrl($methodName)
    {
        // The URL depends on the API key
        $parts = array_pad(explode('-', $this->apiKey), 2, '');

        return sprintf(
            'https://%s.api.mailchimp.com/export/%s/%s/',
            end($parts),
            self::EXPORT_API_VERSION,
            $methodName
        );
    }
}
