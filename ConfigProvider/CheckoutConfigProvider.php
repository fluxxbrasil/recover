<?php
/**
 * Copyright © 2018 Fluxx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Fluxx\Recover\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{

    const CONFIG_PATH_REVOCER_ALERT_ENABLED = 'recover/alert/enabled';

    const CONFIG_PATH_REVOCER_ALERT_AVAILABLE = 'recover/alert/available';

    const CONFIG_PATH_REVOCER_ALERT_TITLE = 'recover/alert/title';

    const CONFIG_PATH_REVOCER_ALERT_CONTENT = 'recover/alert/content';

    const CONFIG_PATH_FLUXX_MAGENTO_MIN_TOTAL = 'payment/fluxx_magento2/min_order_total';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'recover' => [
                'enabled' => (bool) $this->scopeConfig->getValue(
                    self::CONFIG_PATH_REVOCER_ALERT_ENABLED,
                    ScopeInterface::SCOPE_STORE
                ),
                'available' => $this->getListPaymentAvailable(),
                'title'     => $this->scopeConfig->getValue(
                    self::CONFIG_PATH_REVOCER_ALERT_TITLE,
                    ScopeInterface::SCOPE_STORE
                ),
                'content' => $this->scopeConfig->getValue(
                    self::CONFIG_PATH_REVOCER_ALERT_CONTENT,
                    ScopeInterface::SCOPE_STORE
                ),
                'min_total' => $this->scopeConfig->getValue(
                    self::CONFIG_PATH_FLUXX_MAGENTO_MIN_TOTAL,
                    ScopeInterface::SCOPE_STORE
                ),
            ],
        ];
    }

    /**
     * [getListPaymentAvailable description].
     *
     * @return [type] [description]
     */
    private function getListPaymentAvailable()
    {
        $listPayments = $this->scopeConfig->getValue(self::CONFIG_PATH_REVOCER_ALERT_AVAILABLE, ScopeInterface::SCOPE_STORE);

        return explode(',', $listPayments);
    }
}
