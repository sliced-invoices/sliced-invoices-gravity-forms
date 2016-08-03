(function ($) {
    'use strict';

    /**
     * add pre-defined items from select into the empty line item fields
     */
    $(document).on('keyup change', '.sliced_line_items .gfield_list .gfield_list_cell select', function () {

        var data = $(this).find(':selected').val(),
            list = data.split('|'),
            qty = list[0].trim(),
            price = list[2].trim(),
            desc = list[3].trim(),
            group = $(this).closest('tr');

        $(group).find('td:eq(0) input').val(qty);
        $(group).find('td:eq(2) input').val(price);
        $(group).find('td:eq(3) input').val(desc);

    });

})(jQuery);