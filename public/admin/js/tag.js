$(document).ready(function () {
    'use strict';

    $("select.tags").each(function () {
        let $select = $(this);
        let placeholder = $select.attr('placeholder');

        if ($select.hasClass('remote')) {
            let url = $select.data('remote-url') || window.adminPath + '/tags/search';
            let args = $select.data('remote-args') || {};

            $select.select2({
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
                        };
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
            $select.select2({
                placeholder: placeholder,
                tags: true,
                tokenSeparators: [',']
            });
        }

        // 🔹 Giữ thứ tự chọn theo người dùng
        $select.on('select2:select', function (e) {
            let element = e.params.data.element;
            let $element = $(element);

            // Đưa option được chọn xuống cuối danh sách
            $element.detach();
            $select.append($element);
            $select.trigger('change');
        });

        // 🔹 Giữ thứ tự khi load lại từ server (edit form)
        // Chỉ chạy sau khi Select2 đã khởi tạo xong
        let selectedOrder = [];
        $select.find('option:selected').each(function () {
            selectedOrder.push($(this).val());
        });

        if (selectedOrder.length > 0) {
            // Tạo map để sắp xếp lại option theo thứ tự cũ
            let allOptions = $select.find('option').toArray();

            allOptions.sort(function (a, b) {
                let aIndex = selectedOrder.indexOf($(a).val());
                let bIndex = selectedOrder.indexOf($(b).val());
                if (aIndex === -1) aIndex = 99999;
                if (bIndex === -1) bIndex = 99999;
                return aIndex - bIndex;
            });

            $select.html(allOptions);
            $select.val(selectedOrder).trigger('change');
        }
    });
});
