define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'Improntus_ProntoPaga/ui/grid/cells/status'
        },

        getStatus: ({status}) => {
            return `prontopaga payment ${status}`;
        }
    });
});
