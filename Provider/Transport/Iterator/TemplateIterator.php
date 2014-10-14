<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class TemplateIterator implements \Iterator
{
    /**
     * @var MailChimpClient
     */
    protected $client;

    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @param MailChimpClient $client
     */
    public function __construct(MailChimpClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->ensureIterator();

        return $this->iterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->ensureIterator();

        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->ensureIterator();

        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->ensureIterator();

        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->ensureIterator();

        $this->iterator->rewind();
    }


    /**
     * {@inheritdoc}
     */
    public function initIterator()
    {
        $templatesList = (array)$this->client->getTemplates(
            [
                'types' => [
                    Template::TYPE_USER => true,
                    Template::TYPE_GALLERY => true,
                    Template::TYPE_BASE => true
                ],
                'filters' => [
                    'include_drag_and_drop' => true
                ]
            ]
        );

        $iterator = new \ArrayIterator();
        foreach ($templatesList as $type => $templates) {
            foreach ($templates as $template) {
                $template['type'] = $type;
                $template['origin_id'] = $template['id'];
                unset($template['id']);
                $iterator->append($template);
            }
        }

        $this->iterator = $iterator;
    }

    /**
     * Check iterator existence.
     */
    private function ensureIterator()
    {
        if (!$this->iterator) {
            $this->initIterator();
        }
    }
}
