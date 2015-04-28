<?php

namespace OroCRM\Bundle\MailChimpBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use OroCRM\Bundle\MailChimpBundle\DependencyInjection\CompilerPass\ExtendedMergeVarsProviderPass;

class OroCRMMailChimpBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ExtendedMergeVarsProviderPass());
    }
}
