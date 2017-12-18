<?php

namespace Oro\Bundle\MailChimpBundle;

use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\DependencyInjection\CompilerPass\ExtendedMergeVarsProviderPass;
use Oro\Bundle\MailChimpBundle\DependencyInjection\OroMailChimpExtension;
use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMailChimpBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ExtendedMergeVarsProviderPass());

        $addTopicMetaPass = AddTopicMetaPass::create();
        $addTopicMetaPass
            ->add(Topics::EXPORT_MAILCHIMP_SEGMENTS)
        ;

        $container->addCompilerPass($addTopicMetaPass);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroMailChimpExtension();
        }

        return $this->extension;
    }
}
