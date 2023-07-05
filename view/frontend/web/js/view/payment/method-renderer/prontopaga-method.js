define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate',
    'jquery',
    'ko'
], function (
    Component,
    quote,
    additionalValidators,
    placeOrderAction,
    fullScreenLoader,
    $t,
    $,
    ko
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Improntus_ProntoPaga/payment/prontopaga',
            code: 'prontopaga',
            prevSelected: ko.observable(false),
            methodSelected: ko.observable(false)
        },

        getCode: function() {
            return this.code;
        },

        getAllowedMethods: function () {
            return window.checkoutConfig.payment[this.getCode()].allowed_methods;
        },

        getTitle: function () {
            return !this.getLogo() ? window.checkoutConfig.payment[this.getCode()].title : ''
        },

        getLogo: function () {
            return window.checkoutConfig.payment[this.getCode()].logo
        },

        getMethodLogo: function (method) {
            return require.toUrl(window.checkoutConfig.payment[this.getCode()].methods_img_url + '/' + method + '.png')
        },

        toggleSelected: function (e) {
            if (this.prevSelected()) {
                this.prevSelected().toggleClass('_selected')
            }
            $(`#${e.id}`).toggleClass('_selected')
            this.prevSelected($(`#${e.id}`))
            this.methodSelected(e.id)
        },

        getOrderTotal: function () {
            return quote.totals()['grand_total'];
        },

        placeOrder: function (data, event) {
            let self = this;
            if (event) {
                event.preventDefault();
            }

            if (!this.methodSelected()) {
                this.messageContainer.addErrorMessage({ message: $t('Please, first select an available platform of payment.') });
                return false
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    })
                    .done(function () {
                        self.messageContainer.addSuccessMessage({ message: $t('Redirecting to a payment gateway.') });
                        self.afterPlaceOrder();
                    })
                    .always(function () {
                        self.isPlaceOrderActionAllowed(true);
                    });
                return true;
            }
            return false;
        },

        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            window.location.href = `${window.checkoutConfig.payment[this.getCode()].redirect_url}?method=${this.methodSelected()}`;
        },

        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },
    });
});
