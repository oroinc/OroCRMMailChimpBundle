<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Exception;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class BadResponseException extends \RuntimeException implements MailChimpTransportException
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Factory method to create a new response exception based on the response code.
     *
     * @param RequestInterface $request  Request
     * @param Response         $response Response received
     * @param string           $label
     *
     * @return BadResponseException
     */
    public static function factory(RequestInterface $request, Response $response, $label = null)
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
            '[reason phrase] ' . $response->getReasonPhrase(),
            '[url] ' . $request->getUrl(),
            '[content type] ' . $response->getContentType(),
            '[response body] ' . $response->getBody(true),
        ));

        $result = new static($message);
        $result->setResponse($response);
        $result->setRequest($request);

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

    /**
     * Set the request that caused the exception
     *
     * @param RequestInterface $request Request to set
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get the request that caused the exception
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
