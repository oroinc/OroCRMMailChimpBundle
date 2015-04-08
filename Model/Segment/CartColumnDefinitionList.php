<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

class CartColumnDefinitionList implements ColumnDefinitionListInterface
{
    const CART_ITEM_1_NAME = 'item_1';
    const CART_ITEM_1_LABEL = 'First Cart Item';

    const CART_ITEM_2_NAME = 'item_2';
    const CART_ITEM_2_LABEL = 'Second Cart Item';

    const CART_ITEM_3_NAME = 'item_3';
    const CART_ITEM_3_LABEL = 'Third Cart Item';

    /**
     * @var array
     */
    private $columns;

    /**
     * @param ColumnDefinitionListInterface $columnDefinitionList
     */
    public function __construct(ColumnDefinitionListInterface $columnDefinitionList)
    {
        $this->columns = array();
        $this->columns = array_merge(
            $columnDefinitionList->getColumns(),
            array(
                array(
                    'name' => self::CART_ITEM_1_NAME,
                    'label' => self::CART_ITEM_1_LABEL
                ),
                array(
                    'name' => self::CART_ITEM_2_NAME,
                    'label' => self::CART_ITEM_2_LABEL
                ),
                array(
                    'name' => self::CART_ITEM_3_NAME,
                    'label' => self::CART_ITEM_3_LABEL
                ),
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
