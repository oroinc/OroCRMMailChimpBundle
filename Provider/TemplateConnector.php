<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

class TemplateConnector extends AbstractMailChimpConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $result = $this->transport->getTemplates();
        foreach ($result as $type => $templates) {
            foreach ($templates as $template) {
                $template['type'] = $type;
                $template['origin_id'] = $template['id'];
                unset($template['id']);
                $result[] = $template;
            }
        }

        return new \ArrayIterator($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.mailchimp.connector.template.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return $this->entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mailchimp_campaign_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'template';
    }
}
