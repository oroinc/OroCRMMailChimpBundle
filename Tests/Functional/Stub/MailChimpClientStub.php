<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\Stub;

use Guzzle\Http\Message\Response;

use Symfony\Component\Yaml\Yaml;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MailChimpClientStub extends MailChimpClient
{
    /**
     * Loads data from fixtures by originId
     *
     * {@inheritdoc}
     */
    public function export($methodName, array $parameters)
    {
        $fileName = $parameters['id'] . '.yml';
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $fileName;

        $response = Yaml::parse($filePath);

        if (!is_array($response)) {
            throw new \InvalidArgumentException(
                sprintf('Fixture "%s" not found', $fileName)
            );
        }

        return new Response($response['code'], $response['headers'], json_encode($response['body']));
    }
}
