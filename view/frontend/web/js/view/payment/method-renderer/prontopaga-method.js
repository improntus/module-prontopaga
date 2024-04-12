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

    const CODE = 'prontopaga',
        CONFIG_PAYMENT = window.checkoutConfig.payment[CODE];

    return Component.extend({
        defaults: {
            template: 'Improntus_ProntoPaga/payment/prontopaga',
            code: 'prontopaga',
            prevSelected: ko.observable(false),
            methodSelected: ko.observable(false),
            isBarVisible: ko.observable(false),
            useDocumentNumber: ko.observable(CONFIG_PAYMENT.use_document_field),
            isFieldRequired: ko.observable(CONFIG_PAYMENT.is_field_required),
            documentNumber: ko.observable(''),
            documentError: ko.observable(false)
        },

        /**
         * @returns {String}
         */
        getCode: function () {
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

        /**
         * @returns {Array}
         */
        getAllowedMethods: function () {
            return CONFIG_PAYMENT.allowed_methods;
        },

        /**
         * @returns {String}
         */
        getTitle: function () {
            return !this.getLogo() ? CONFIG_PAYMENT.title : '';
        },

        /**
         * @returns {String}
         */
        getLogo: function () {
            return CONFIG_PAYMENT.logo;
        },

        /**
         *
         * @param {*} method
         * @returns {String}
         */
        getMethodLogo: function (method) {
            return CONFIG_PAYMENT?.methods_img_url[method]?.img ?? '';
        },

        /**
         *
         * @param {*} e
         */
        toggleSelected: function (e) {
            if (this.prevSelected()) {
                this.prevSelected().toggleClass('_selected');
            }
            $(`#${e.id}`).toggleClass('_selected');
            this.prevSelected($(`#${e.id}`));
            this.methodSelected(e.id);
        },

        /**
         *
         * @returns {Number|String}
         */
        getOrderTotal: function () {
            return quote.totals()['grand_total'];
        },

        /**
         *
         * @param {*} data
         * @param {*} event
         * @returns {Boolean}
         */
        placeOrder: function (data, event) {
            let self = this;

            if (event) {
                event.preventDefault();
            }

            if (!this.methodSelected()) {
                this.messageContainer.addErrorMessage(
                    { message: $t('Please, first select an available platform of payment.') }
                );
                return false;
            }

            if (!this.documentNumber() && this.useDocumentNumber() && this.isFieldRequired()) {
                this.messageContainer.addErrorMessage({ message: $t('Document number is required.') });
                this.validateDoc(false);
                return false;
            }

            this.validateDoc(true);
            self.isBarVisible(true);

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

        /**
         *
         * @param {*} elem
         * @returns {void}
         */
        setDocumentNumber: function (elem) {
            this.documentNumber(elem.value);
        },

        /** @inheritdoc */
        validateDoc: function (isValid) {
            this.documentError(!isValid);
            let doc = $('#card-document');

            isValid
                ? doc.removeClass('error')
                : doc.addClass('error');
        },

        /** @inheritdoc */
        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            window.location.href = `${CONFIG_PAYMENT.redirect_url}?method=${this.methodSelected()}`;
        },

        /** @inheritdoc */
        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        }
    });
});
