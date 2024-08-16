jQuery(function ($) {

    var add_field = $('[data-region="add-field-backup"]').html();
    if (add_field) {
        $('[data-region="add-field-backup"]').remove();
    }
    // add new field
    $('[data-action="add-field"]').on('click', function () {
        let next_field_num = $('.search-fields[data-region="search-fields"] .data-field-wrapper').length;
        let select_post_type = $('#filter_setting_post_type').val();
        let ajax = $.ajax({
            url: csf_obj.ajaxUrl,
            type: 'POST',
            data: {
                action: 'csf_add_data_field_wrapper',
                nonce: csf_obj.nonce,
                post_type: select_post_type,
                next_field_num: parseInt(next_field_num)
            }
        });
        ajax.done(function (response) {
            $('.search-fields[data-region="search-fields"]').append(response);
        });
        // ajax.fail(function () {
        //     console.log("failed ");
        // });
        // ajax.always(function (response) {
        //     console.log(response);
        // });
    });

    // remove field
    $('#search-field-dynamic').on('click', 'button', function (event) {
        let targetElement_action = $(event.target).attr('data-action');
        // remove the field
        if (targetElement_action === 'remove-field') {
            $(event.target).parent().remove();
        }
    });

    // Handle the change event on search_field_data type
    $('#search-field-dynamic').on('change', 'select[data-field="search_field_data"]', function () {
        let selectedValue = $(this).val();
        let element_taxo = $(this).parent().nextAll().filter('.search_field_taxonomy');
        let element_meta = $(this).parent().nextAll().filter('.search_field_metadata');
        if (selectedValue == 'taxonomy') {
            element_taxo.show();
            element_taxo.find('select').attr('required', true);
            element_meta.hide();
            element_meta.find('select').attr('required', false);
        } else {
            element_taxo.hide();
            element_taxo.find('select').attr('required', false);
            element_meta.show();
            element_meta.find('select').attr('required', true);
        }
    });

});