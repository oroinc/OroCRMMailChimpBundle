<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Exception;

use Guzzle\Http\Message\Response;

class BadResponseException extends \RuntimeException implements MailChimpTransportException
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * Factory method to create a new response exception based on the response code.
     *
     * @param string $url
     * @param string $parameters
     * @param Response $response Response received
     * @param string $label
     * @return BadResponseException
     */
    public static function factory($url, $parameters, Response $response, $label = null)
    {
        if (!$label) {
            if ($response->isClientError()) {
                $label = 'Client error response';
            } elseif ($response->isServerError()) {
                $label = 'Server error response';
            } else {
                $label = 'Unsuccessful response';
            }
        }

        $message = $label . PHP_EOL . implode(PHP_EOL, array(
                '[status code] ' . $response->getStatusCode(),
                '[API error code] ' . $response->getHeader('X-MailChimp-API-Error-Code'),
                '[reason phrase] ' . $response->getReasonPhrase(),
                '[url] ' . $url,
                '[request parameters]' . $parameters,
                '[content type] ' . $response->getContentType(),
                '[response body] ' . $response->getBody(true),
            ));

        $result = new static($message);
        $result->setResponse($response);

        return $result;
    }

    /**
     * Set the response that caused the exception
     *
     * @param Response $response Response to set
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the response that caused the exception
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
