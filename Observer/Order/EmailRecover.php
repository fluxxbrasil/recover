<?php
/**
 * Copyright © 2018 Fluxx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Fluxx\Recover\Observer\Order;

use Fluxx\Recover\Helper\Email;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Order status updates.
 */
class EmailRecover implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Fluxx\Recover\Helper\Email
     */
    private $email;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Fluxx\Recover\Helper\Email                $email
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        Email $email
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->email = $email;
    }

    /**
     * Execute method.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($this->email->checkAvailable($order)) {
            $this->email->sendMail($order);
        }

        return $this;
    }
}
