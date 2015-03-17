<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\Segment\CartColumnDefinitionList;

class MemberCartMergeVarDataConverter implements DataConverterInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @vars string
     */
    private $cartItemsTemplate;

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
     * @inheritdoc
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (false === isset($importedRecord['extended_merge_vars'])) {
            return array();
        }
        /** @var ArrayCollection $extendedMergeVars */
        $extendedMergeVars = $importedRecord['extended_merge_vars'];
        if (false === ($extendedMergeVars instanceof ArrayCollection)) {
            return array();
        }

        $result = array();

        /** @var ExtendedMergeVar $cartMergeVar */
        $cartMergeVar = $extendedMergeVars->filter(function ($each) {
            if ($each->getName() == CartColumnDefinitionList::CART_ITEMS_NAME) {
                return true;
            }
            return false;
        })->first();

        if ($cartMergeVar && isset($importedRecord['entity_id'])) {
            $cartEntityId = $importedRecord['entity_id'];
            $cart = $this->doctrineHelper
                ->getEntityRepository($importedRecord['entityClass'])
                ->find($cartEntityId);
            if ($cart) {
                $result = array(
                    $cartMergeVar->getTag() => $this->prepareCartItemsHtml($cart)
                );
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        throw new \Exception('Is not implemented.');
    }

    /**
     * @param Cart $cart
     * @return string
     */
    private function prepareCartItemsHtml(Cart $cart)
    {
        $cartItems = $cart->getCartItems();
        if ($cartItems->isEmpty()) {
            return '';
        }
        $html = $this->twig->render($this->cartItemsTemplate, array('cartItems' => $cartItems));
        return $html;
    }
}
