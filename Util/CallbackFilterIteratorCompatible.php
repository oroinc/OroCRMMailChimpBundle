<?php

namespace OroCRM\Bundle\MailChimpBundle\Util;

/**
 * This is replacement of CallbackFilterIterator to fix bug (https://bugs.php.net/bug.php?id=72051)
 */
class CallbackFilterIteratorCompatible extends \FilterIterator
{
    /**
     * @var callable $callback
     */
    protected $callback;

    /**
     * @var mixed $current
     */
    protected $current;

    /**
     * CallbackFilterIterator constructor.
     *
     * @param \Iterator $iterator
     * @param callable $callback
     */
    public function __construct(\Iterator $iterator, callable $callback)
    {
        $this->callback = $callback;
        parent::__construct($iterator);
    }

    /**
     * @return bool
     */
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        $this->current = $iterator->current();
        return call_user_func_array($this->callback, array(&$this->current, $iterator->key(), $iterator));
    }

    /**
     * @return mixed|void
     */
    public function current()
    {
        if (!$this->getInnerIterator()->valid()) {
            return null;
        }

        if ($this->accept()) {
            return $this->current;
        }

        $this->getInnerIterator()->next();

        return $this->current();
    }
}
