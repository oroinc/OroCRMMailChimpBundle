<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport;

use Guzzle\Http\Message\Response;
use ZfrMailChimp\Client\MailChimpClient as BaseClient;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/
 * @link http://apidocs.mailchimp.com/export/1.0/
 */
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
        BaseClient::__construct($apiKey, $version);
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
        $parameters = array_merge(['api_key' => $this->apiKey], $parameters);

        $request = $this->get($url, $parameters);
        return $request->send();
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
            'https://%s.api.mailchimp.com/export/%s/%s',
            end($parts),
            self::EXPORT_API_VERSION,
            $methodName
        );
    }
}
