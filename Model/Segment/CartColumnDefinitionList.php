<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

class CartColumnDefinitionList implements ColumnDefinitionListInterface
{
    const CART_ITEMS_NAME = 'cartItems';
    const CART_ITEMS_LABEL = 'Cart Items';

    /**
     * @var array
     */
    private $columns;

    public function __construct(ColumnDefinitionListInterface $columnDefinitionList)
    {
        $this->columns = array();
        $this->columns = array_merge(
            $columnDefinitionList->getColumns(),
            array(
                array(
                    'name' => self::CART_ITEMS_NAME,
                    'label' => self::CART_ITEMS_LABEL
                )
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
