<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

class TemplateConnector extends AbstractMailChimpConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $templatesList = $this->transport->getTemplates();
        $result = new \ArrayIterator();
        foreach ($templatesList as $type => $templates) {
            foreach ($templates as $template) {
                $template['type'] = $type;
                $template['origin_id'] = $template['id'];
                unset($template['id']);
                $result->append($template);
            }
        }

        return $result;
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
