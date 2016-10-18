<?php

namespace Oro\Bundle\MailChimpBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\DependencyInjection\CompilerPass\ExtendedMergeVarsProviderPass;

class OroCRMMailChimpBundle extends Bundle
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
            ->add(Topics::EXPORT_MAIL_CHIMP_SEGMENTS)
        ;

        $container->addCompilerPass($addTopicMetaPass);
    }
}
