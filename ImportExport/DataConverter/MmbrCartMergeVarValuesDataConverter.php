<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;

class MmbrCartMergeVarValuesDataConverter implements DataConverterInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $cartItemsTemplate;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param \Twig_Environment $twig
     * @param string $cartItemsTemplate
     */
    public function __construct(DoctrineHelper $doctrineHelper, \Twig_Environment $twig, $cartItemsTemplate)
    {
        if (false === is_string($cartItemsTemplate) || empty($cartItemsTemplate)) {
            throw new \InvalidArgumentException('Extended Merge Var cart items template should be provided.');
        }
        $this->doctrineHelper = $doctrineHelper;
        $this->twig = $twig;
        $this->cartItemsTemplate = $cartItemsTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (false === isset($importedRecord['extended_merge_vars'])) {
            return [];
        }
        /** @var Collection $extendedMergeVars */
        $extendedMergeVars = $importedRecord['extended_merge_vars'];
        if (false === ($extendedMergeVars instanceof Collection)) {
            return [];
        }

        $result = [];

        /** @var Collection $cartItemMergeVars */
        $cartItemMergeVars = $extendedMergeVars->filter(function ($each) {
            return false !== strpos($each->getName(), 'item_');
        });

        if (!$cartItemMergeVars->isEmpty() && isset($importedRecord['entity_id'])) {
            $cartEntityId = $importedRecord['entity_id'];
            /** @var Cart $cart */
            $cart = $this->doctrineHelper
                ->getEntityRepository($importedRecord['entityClass'])
                ->find($cartEntityId);
            if ($cart) {
                $cartItems = $cart->getCartItems();
                if (!$cartItems->isEmpty()) {
                    $index = 0;
                    foreach ($cartItemMergeVars as $mergeVar) {
                        $item = $cartItems->get($index);
                        if (is_null($item)) {
                            continue;
                        }
                        $result[$mergeVar->getTag()] = $this->prepareCartItemsHtml($item, $index);
                        $index++;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        throw new \Exception('Is not implemented.');
    }

    /**
     * @param CartItem $item
     * @return string
     */
    protected function prepareCartItemsHtml(CartItem $item, $index)
    {
        $html = $this->twig->render($this->cartItemsTemplate, ['item' => $item, 'index' => $index]);
        return $html;
    }
}
