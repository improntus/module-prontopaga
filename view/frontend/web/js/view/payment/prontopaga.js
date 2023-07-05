define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        const type = 'prontopaga';
        if (window.checkoutConfig.payment[type].active) {
            rendererList.push(
                {
                    type: type,
                    component: 'Improntus_ProntoPaga/js/view/payment/method-renderer/prontopaga-method'
                }
            );
        }
        return Component.extend({});
    }
);
