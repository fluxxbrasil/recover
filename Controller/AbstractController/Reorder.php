<?php
/**
 * Copyright © 2018 Fluxx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Fluxx\Recover\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Url\DecoderInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Abstract class for controllers Reorder(Customer) and Reorder(Guest).
 */
abstract class Reorder extends Action\Action
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderInterfaceFactory
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Action\Context $context
     * @param OrderInterface $orderLoader
     * @param Registry       $registry
     */
    public function __construct(
        Action\Context $context,
        OrderInterface $orderLoader,
        DecoderInterface $urlDecoder,
        Registry $registry
    ) {
        $this->orderLoader = $orderLoader;
        $this->urlDecoder = $urlDecoder;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Action for reorder.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $orderId = $this->_request->getParam('order_id');
        $hash = $this->_request->getParam('hash');

        try {
            $order = $this->orderLoader->load($orderId);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            } else {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            return $resultRedirect->setPath('sales/order/history');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );

            return $resultRedirect->setPath('checkout/cart');
        }

        $protectedCode = $order->getProtectCode();
        if ($hash !== $protectedCode) {
            $this->messageManager->addErrorMessage(__('We can\'t add this item to your shopping cart right now.'));

            return $resultRedirect->setPath('checkout/cart');
        }

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }

                return $resultRedirect->setPath('sales/order/history');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );

                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();

        return $resultRedirect->setPath('checkout');
    }
}
