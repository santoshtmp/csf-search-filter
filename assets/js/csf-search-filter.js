jQuery(function ($) {

    /**
     * parameters
     */
    var csf_filter_form_ids = JSON.parse(csf_obj.form_ids);

    /**
     * =========================================================
     * Make HTTP Object with javascript
     * https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest
     * =========================================================
     */

    function make_XMLHttpRequest() {
        try {
            return new XMLHttpRequest();
        } catch (error) { }
        try {
            return new ActiveXObject('Msxml2.XMLHTTP');
        } catch (error) { }
        try {
            return new ActiveXObject('Microsoft.XMLHTTP');
        } catch (error) { }

        throw new Error('Could not create HTTP request object.');
    }

    /**
     *
     * @param {*} filtr_result_area_id
     * @param {*} include_script
     * @returns
     */
    function get_XMLHttpRequest_Data(filtr_result_area_id, filter_form, submission_link = '', callback) {
        let filtr_result_area = '#' + filtr_result_area_id;
        submission_link = submission_link ? submission_link : filter_form.attr('data-url') + '/?' + filter_form.serialize();
        var filter_area = $(filtr_result_area);
        filter_area.css('opacity', '0.6');
        filter_area.addClass('csf-area-loading');
        var failed_data = "<p class='csf-failed-to-load'>Fail on loading data try again.</p>";
        var request = make_XMLHttpRequest();
        request.open('GET', submission_link, true);
        request.send(null);
        request.onreadystatechange = function () {
            if (request.readyState === XMLHttpRequest.DONE) {
                request.filter_area = false;
                if (request.status === 200) {
                    var htmlDoc = $(request.responseText);
                    var html_data = htmlDoc.find(filtr_result_area).html();
                    if (html_data) {
                        filter_area.html(html_data);
                        // window.history.replaceState('', 'url', submission_link);
                        request.filter_area = true;
                        let search_number_info = htmlDoc.find('#search-number-info').html();
                        if (search_number_info) {
                            $('#search-number-info').html(search_number_info);
                        } else {
                            $('#search-number-info').html('');
                        }
                    } else {
                        filter_area.html(failed_data);
                    }
                } else {
                    filter_area.html(failed_data);
                }
                filter_area.css('opacity', '1');
                filter_area.removeClass('csf-area-loading');
                if (callback) callback(request);
            }
        };
    }

    /**
     * ============================================
     * Initialization Search area
     * ============================================
     */

    // var form_id = $('form').attr('id');
    csf_filter_form_ids.forEach(function (csf_filter_form_id, index) {
        var filter_form = $('form#' + csf_filter_form_id);
        var csf_result_area_id = filter_form.attr('result-area-id');
        filter_form.on('submit', function (e) {
            e.stopPropagation();
            var req = get_XMLHttpRequest_Data(csf_result_area_id, filter_form);
            return false;
        });
        $('#' + csf_filter_form_id + ' select').on('change', function (e) {
            filter_form.trigger('submit');
        });
        $('#' + csf_filter_form_id + ' input[type="checkbox"]').on('change', function () {
            filter_form.trigger('submit');
        });
        // $('#' + csf_result_area_id + ' .pagination a').on('click', function (e) {
        //     this.closest('');
        //     e.stopPropagation();
        //     $(window).scrollTop(0);
        //     get_XMLHttpRequest_Data(csf_result_area_id, $(this).attr('href'));
        //     return false;
        // });
    });

    /**
     * ============================================
     * Pagination filter
     * ============================================
     */
    $(document).on('click', '.pagination a', function (e) {
        e.stopPropagation();
        $(window).scrollTop(0);
        var pagination_link = $(this).attr('href');
        csf_filter_form_ids.forEach(function (csf_filter_form_id, index) {
            var filter_form = $('form#' + csf_filter_form_id);
            var csf_result_area_id = filter_form.attr('result-area-id');
            var data_url = filter_form.attr('data-url');
            if (pagination_link.includes(data_url)) {
                get_XMLHttpRequest_Data(csf_result_area_id, filter_form, pagination_link);
            }
        });
        return false;
    });


    /**
     * ============================================
     * END
     * ============================================
     */
});
