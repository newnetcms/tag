$(document).ready(function () {
    'use strict';

    $("select.tags").each(function () {
        let placeholder = $(this).attr('placeholder');

        if ($(this).hasClass('remote')) {
            let url = $(this).data('remote-url') || window.adminPath + '/tags/search'
            let args = $(this).data('remote-args') || {};

            $(this).select2({
                placeholder: placeholder,
                tags: true,
                tokenSeparators: [','],
                ajax: {
                    url: url,
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page || 1,
                            args: args,
                        }
                    },
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.items,
                            pagination: {
                                more: data.hasMore
                            }
                        };
                    }
                }
            });
        } else {
            $(this).select2({
                placeholder: placeholder,
                tags: true,
                tokenSeparators: [',']
            });
        }
    });
});
