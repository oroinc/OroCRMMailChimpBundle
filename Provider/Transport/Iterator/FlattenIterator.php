<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

/**
 * Iterator for data structures like ['item_key' => []]
 * where item_key will be passed to all children with some given key
 *
 */
class FlattenIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    protected $toIterate;

    /**
     * @var string
     */
    protected $keyToElementName;

    /**
     * @var string
     */
    protected $toIterateKey;

    /**
     * @var \Iterator
     */
    protected $subIterate;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var bool
     */
    protected $processEmpty;

    /**
     * @var int
     */
    protected $dataLevel;

    /**
     * @param \Iterator|array $toIterate
     * @param $keyToElementName
     * @param bool $processEmpty
     * @param int $dataLevel
     */
    public function __construct($toIterate, $keyToElementName, $processEmpty = false, $dataLevel = 1)
    {
        if (!$toIterate instanceof \Iterator && is_array($toIterate)) {
            $toIterate = new \ArrayIterator($toIterate);
        }
        $this->toIterate = $toIterate;
        $this->keyToElementName = $keyToElementName;
        $this->processEmpty = $processEmpty;
        $this->dataLevel = $dataLevel;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = $this->subIterate->current();
        if ($this->dataLevel == 1) {
            $current[$this->keyToElementName] = $this->toIterateKey;
        }

        return $current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if ($this->subIterate && $this->subIterate->valid()) {
            $this->subIterate->next();
        }
        if (!$this->subIterate->valid()) {
            $this->toIterate->next();
            $this->initializeSubIterate();
        }

        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return ($this->subIterate && $this->subIterate->valid()) || $this->toIterate->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
        $this->toIterateKey = null;

        $this->toIterate->rewind();
        $this->initializeSubIterate();
    }

    /**
     * Initialize sub-iterator.
     */
    protected function initializeSubIterate()
    {
        if ($this->toIterate->valid()) {
            $this->toIterateKey = $this->toIterate->key();
            $currentIterator = new \ArrayIterator((array)$this->toIterate->current());
            if ($this->dataLevel == 1) {
                $this->subIterate = $currentIterator;
            } else {
                $this->subIterate = new self(
                    $currentIterator,
                    $this->keyToElementName,
                    $this->processEmpty,
                    $this->dataLevel - 1
                );
            }
            $this->subIterate->rewind();
        } else {
            $this->toIterateKey = null;
            $this->subIterate = null;
        }
    }
}
