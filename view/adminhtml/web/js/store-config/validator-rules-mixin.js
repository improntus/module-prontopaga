define([
    'jquery'
], function ($) {
    'use strict';
    return function (target) {
        const VALID_CHAR = '/'
        $.validator.addMethod(
            'validate-url-slash',
            function (value) {
                return value.endsWith(VALID_CHAR)
            },
            $.mage.__("Please make sure that URL ends with '/' (slash), e.g. http:://domain/magento/")
        );
        return target;
    };
});
