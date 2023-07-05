define([
    'jquery',
    'beautify',
    'beautifyConfig'
], function ($, beautify, beautifyConfig) {
    'use strict';

    return (config) => {
        $(() => {
            $.each(config, (index, value) => {
                const dataJson = JSON.stringify(value)
                $(`.${index}`).html(`"${index}":` + beautify.js_beautify(dataJson, beautifyConfig))
            })

            $('.beauty').on('click', () => {
                $('.formatted-data').toggle()
                $('.unformatted-data').toggle()
                $('.beauty').toggle()
            });
        });
    }
});
