define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'Improntus_ProntoPaga/ui/grid/cells/status'
        },

        getStatus: ({status}) => {
            debugger
            return `prontopaga payment ${status}`
            if (row.status == 'create') {
                return 'order-create';
            }else if(row.status == 'success') {
                return 'order-processing';
            }else if(row.status == 'rejected') {
                return 'order-complete';
            }else if(row.status == 'error') {
                return 'order-error';
            }
            return '#303030';
        }
    });
});
