<?php
/**
 * Copyright © 2018 Fluxx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Fluxx\Recover\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Email helper.
 */
class Email extends AbstractHelper
{
    const XML_PATH_RECOVER_EMAIL_TEMPLATE = 'recover/email/recover/template';

    const XML_PATH_RECOVER_EMAIL_GUEST_TEMPLATE = 'recover/email/recover/guest_template';

    const XML_PATH_RECOVER_EMAIL_IDENTITY = 'recover/email/recover/identity';

    const XML_PATH_RECOVER_EMAIL_COPY_TO = 'recover/email/recover/copy_to';

    const XML_PATH_RECOVER_EMAIL_COPY_METHOD = 'recover/email/recover/copy_method';

    const XML_PATH_RECOVER_AVAILABLE_METHODS = 'recover/email/available';

    const XML_PATH_FLUXX_METHOD_MIN_TOTAL = 'payment/fluxx_magento2/min_order_total';

    const XML_PATH_FLUXX_METHOD_ACTIVE = 'payment/fluxx_magento2/active';

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder      $transportBuilder
     * @param StateInterface        $inlineTranslation
     * @param LoggerInterface       $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Condicional for send email.
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     *
     * @return bool
     */
    public function checkAvailable($order)
    {
        $storeId = $order->getStoreId();

        if ($order->getState() != 'canceled') {
            $this->logger->debug('State - '.$order->getState());

            return false;
        }

        $configActive = $this->scopeConfig->getValue(
            self::XML_PATH_FLUXX_METHOD_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$configActive) {
            $this->logger->debug('Active - '.$configActive);

            return false;
        }

        $configMinTotal = $this->scopeConfig->getValue(
            self::XML_PATH_FLUXX_METHOD_MIN_TOTAL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($order->getGrandTotal() < $configMinTotal) {
            $this->logger->debug('Order - '.$order->getGrandTotal());

            return false;
        }

        $configAvailableMethods = $this->scopeConfig->getValue(
            self::XML_PATH_RECOVER_AVAILABLE_METHODS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();

        if (!in_array($paymentCode, explode(',', $configAvailableMethods))) {
            $this->logger->debug('PaymentCode - '.$paymentCode);

            return false;
        }

        return true;
    }

    /**
     * Send Email.
     *
     * @param \Magento\Sales\Model\OrderFactory $order
     *
     * @return this
     */
    public function sendMail($order)
    {
        $this->inlineTranslation->suspend();
        $storeId = $order->getStoreId();

        $sender = $this->scopeConfig->getValue(
            self::XML_PATH_RECOVER_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $copyTo = $this->scopeConfig->getValue(
            self::XML_PATH_RECOVER_EMAIL_COPY_TO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $copyMethod = $this->scopeConfig->getValue(
            self::XML_PATH_RECOVER_EMAIL_COPY_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($order->getCustomerIsGuest()) {
            $template = $this->scopeConfig->getValue(
                self::XML_PATH_RECOVER_EMAIL_GUEST_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $customerName = $order->getBillingAddress()->getFirstname();
        } else {
            $template = $this->scopeConfig->getValue(
                self::XML_PATH_RECOVER_EMAIL_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $customerName = $order->getCustomerFirstname();
        }

        $vars = [
            'order'     => $order,
            'store'     => $this->storeManager->getStore($storeId),
            'hash'      => $order->getProtectCode(),
            'firstname' => $customerName,
        ];

        $this->transportBuilder->setTemplateIdentifier(
            $template
        )
        ->setTemplateOptions(
            [
                'area'  => Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )
        ->setTemplateVars($vars)
        ->setFromByScope($sender);

        $this->transportBuilder->addTo($order->getCustomerEmail());

        if (!empty($copyTo)) {
            if ($copyMethod == 'bcc') {
                $this->transportBuilder->addBcc($copyTo);
            } else {
                $this->transportBuilder->addBcc($copyTo);
            }
        }

        $transport = $this->transportBuilder->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();

        return $this;
    }
}
