/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
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
            methodSelected: ko.observable(false),
            isBarVisible: ko.observable(false),
            documentNumber: ko.observable(''),
            documentError: ko.observable(false)
        },

        getCode: function() {
            return this.code;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.item.method,
                'additional_data': {
                    'document_number': this.documentNumber()
                }
            };
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
            return window.checkoutConfig.payment[this.getCode()].methods_img_url[method].img
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

            if (!this.documentNumber()) {
                this.messageContainer.addErrorMessage({ message: $t('Document number is required.') });
                this.validateDoc(false)
                return false
            } else {
                this.validateDoc(true)
            }

            self.isBarVisible(true)

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

        getDocumentNumber: function (elem) {
            let customerData = window.checkoutConfig?.customerData

            if (elem.value) {
                customerData.taxvat = elem.value;
            }
            this.documentNumber(elem.value)

            return (customerData.taxvat && customerData.taxvat !== undefined)
                ? customerData.taxvat.replace(/[^a-zA-Z0-9]/g, '')
                : '';
        },

        validateDoc: function (isValid) {
            this.documentError(!isValid)
            let doc = $('#card-document')
            isValid
                ? doc.removeClass('error')
                : doc.addClass('error');
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
