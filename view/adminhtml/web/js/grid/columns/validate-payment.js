define([
    'jquery',
    'Magento_Ui/js/grid/columns/column',
    'Magento_Ui/js/modal/modal',
    'uiRegistry',
    'beautify',
    'beautifyConfig'
], function ($, Column, modal, registry, beautify, beautifyConfig) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Improntus_ProntoPaga/grid/cells/validate-payment',
            modalElem: '.improntus-prontopaga.modal.validate',
            modalContent: '.formatted-data blockquote'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            modal({
                type: 'popup',
                responsive: true,
                clickableOverlay: true,
                closeText: $.mage.__('Close'),
                title: $.mage.__('Validate transacction'),
                subTitle: ' ',
                responsive: true,
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'action primary',
                    click: function () {
                        this.closeModal();
                    }
                }]
            }, $(this.modalElem));
        },

        /**
         *
         * @param {*} transaction_id
         */
        showInfo: function ({transaction_id}) {
            let self = this;

            $.ajax({
                showLoader: true,
                url: window.validateUrl,
                data: { transactionId: transaction_id },
                type: 'POST',
                dataType: 'json'
            }).done((response) => {
                if (response.code === 200) {
                    response = beautify.js_beautify(response.body, beautifyConfig);
                    registry.get('index = improntus_prontopaga_transaction_listing_data_source').reload({'refresh': true});
                }
                $(`${self.modalElem} ${self.modalContent}`).html(response);
                $(`${self.modalElem}`).modal('setSubTitle', `uid: ${transaction_id}`);
                $(self.modalElem).modal('openModal');
            });
        }
    });
});
