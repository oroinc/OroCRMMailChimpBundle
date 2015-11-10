<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\BlockingJobsInterface;

class BlockingJobsProvider implements BlockingJobsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCommandName()
    {
        return [
            'oro:cron:mailchimp:export'
        ];
    }
}
