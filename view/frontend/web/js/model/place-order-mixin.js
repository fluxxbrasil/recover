/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "mage/utils/wrapper",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "Magento_Checkout/js/model/quote",
], function ($, wrapper, alert, $t, quote) {
    "use strict";

    return function (placeOrderAction) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            if(window.checkoutConfig.recover.enabled){
                if(typeof window.checkoutConfig.payment.fluxx_magento2 !== "undefined"){
                    if( parseInt(quote.totals().grand_total) >= parseInt(window.checkoutConfig.recover.min_total)){
                        if(window.checkoutConfig.recover.available.indexOf(paymentData.method) >= 0){
                            if(messageContainer) {
                                alert({
                                    title: window.checkoutConfig.recover.title,
                                    content: window.checkoutConfig.recover.content,
                                    actions: {
                                        always: function(){}
                                    }
                                });
                            }
                        }
                    }
                }
            }
            return originalAction(paymentData, messageContainer);
        });
    };
});
