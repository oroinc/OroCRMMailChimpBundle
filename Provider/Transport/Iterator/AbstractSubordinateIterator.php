<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

abstract class AbstractSubordinateIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    protected $mainIterator;

    /**
     * @var \Iterator|null
     */
    protected $subordinateIterator;

    /**
     * @var mixed
     */
    protected $current = null;

    /**
     * @var mixed
     */
    protected $offset = -1;

    /**=
     * @param \Iterator $mainIterator
     */
    public function __construct(\Iterator $mainIterator)
    {
        $this->mainIterator = $mainIterator;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->current = $this->read();
        if ($this->valid()) {
            $this->offset += 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !is_null($this->current);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->offset = -1;
        $this->current = null;
        $this->subordinateIterator = null;
        $this->mainIterator->rewind();

        $this->next();
    }

    /**
     * Read next element from subordinate iterator, iterates to next element of main iterator when done.
     *
     * @return array|null
     */
    protected function read()
    {
        if (!$this->mainIterator->valid()) {
            return null;
        }

        if (!$this->subordinateIterator) {
            $mainIteratorElement = $this->mainIterator->current();
            $this->subordinateIterator = $this->createSubordinateIterator($mainIteratorElement);
            if (!$this->subordinateIterator->valid()) { // Iterator could be already rewound
                $this->subordinateIterator->rewind();
            }
        }

        if (!$this->subordinateIterator->valid()) {
            // Read for next List
            $this->subordinateIterator = null;
            $this->mainIterator->next();
            return $this->read();
        }

        $result = $this->subordinateIterator->current();
        $this->subordinateIterator->next();
        return $result;
    }

    /**
     * Creates subordinate iterator from element of main iterator
     *
     * @param mixed $mainIteratorElement
     * @return \Iterator
     */
    abstract protected function createSubordinateIterator($mainIteratorElement);
}
