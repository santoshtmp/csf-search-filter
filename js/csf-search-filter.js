jQuery(function ($) {
  /**
   * parameters
   */
  var csf_filter_form_ids = JSON.parse(csf_obj.form_ids);
  let change_filte_item_id;
  let filter_form;

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
    let loading_class = 'filter-area-loading';
    filter_area.addClass(loading_class);
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
          var csf_filter_form = htmlDoc.find('#' + csf_filter_form_ids).html();
          if (html_data) {
            // update csf result area
            filter_area.html(html_data);
            // update csf form
            $('#' + csf_filter_form_ids).html(csf_filter_form);
            check_old_active_filter_block();
            // update search url
            // window.history.replaceState('', 'url', submission_link);
            // update csf result number
            let search_number_info = htmlDoc.find('#csf-get-result-info').html();
            if (search_number_info) {
              $('#csf-get-result-info').html(search_number_info);
            } else {
              $('#csf-get-result-info').html('');
            }
            //
            request.filter_area = true;
          } else {
            filter_area.html(failed_data);
          }
        } else {
          filter_area.html(failed_data);
        }
        filter_area.css('opacity', '1');
        filter_area.removeClass(loading_class);
        if (callback) callback(request);
      }
    };
  }

  /**
   * ============================================
   * Initialization for all given search ids
   * ============================================
   */

  // var form_id = $('form').attr('id');
  csf_filter_form_ids.forEach(function (csf_filter_form_id, index) {
    filter_form = $('form#' + csf_filter_form_id);
    // var filter_rest_btn = $('form#' + csf_filter_form_id + ' .filter-reset-btn button');
    // filter_form.on('submit', function (e) {
    //     e.stopPropagation();
    //     var req = get_XMLHttpRequest_Data(csf_result_area_id, filter_form);
    //     return false;
    // });
    $(document).on('submit', 'form#' + csf_filter_form_id, function (e) {
      e.stopPropagation();
      var csf_result_area_id = filter_form.attr('result-area-id');
      var req = get_XMLHttpRequest_Data(csf_result_area_id, filter_form);
      return false;
    });

    // on change in filter item make form submit
    $(document).on(
      'change',
      '#' + csf_filter_form_id + ' select, #' + csf_filter_form_id + ' input[type="radio"], #' + csf_filter_form_id + ' input[type="checkbox"]',
      function (e) {
        change_filte_item_id = $(this).attr('id');
        filter_form.trigger('submit');
      }
    );

    // filter_rest_btn.on('click', function () {
    $(document).on('click', 'form#' + csf_filter_form_id + ' .filter-reset-btn a', function (e) {
      e.preventDefault();
      e.stopPropagation();
      setTimeout(() => {
        change_filte_item_id = '';
        csf_result_area_id = filter_form.attr('result-area-id');
        get_XMLHttpRequest_Data(csf_result_area_id, filter_form, filter_form.attr('data-url'));
      }, 100);
    });

    // invalid field values provided
    if (csf_obj.invalid_csf_value) {
      $('#' + csf_result_area_id).append(' <p>Invalid search filter value is provided.  </p> ');
    }

    // Pagination filter
    $(document).on('click', '.pagination a', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(window).scrollTop(0);
      var pagination_link = $(this).attr('href');
      var csf_result_area_id = filter_form.attr('result-area-id');
      var data_url = filter_form.attr('data-url');
      if (pagination_link.includes(data_url)) {
        get_XMLHttpRequest_Data(csf_result_area_id, filter_form, pagination_link);
        return false;
      }
    });

    //
  });

  // move the section view to the active filter block
  let filter_block_active = document.querySelector('form .filter-block.active');
  if (filter_block_active) {
    filter_block_active.scrollIntoView({
      behavior: 'smooth',
    });
  }

  /**
   * check and make old active filter-block
   */
  function check_old_active_filter_block() {
    if (change_filte_item_id) {
      let filter_item = $('.filter-block .filter-item[item-type="' + change_filte_item_id + '"]');
      // let filter_title = filter_item.parent().parent().prev('div');
      let filter_block = filter_item.parents('.filter-block');
      let filter_title = filter_block.find('.filter-title');
      filter_title.addClass('active');
      filter_block.addClass('active');
    }
  }

  // toggle accordion
  $(document).on('click', '.filter-block', function (e) {
    $(this).toggleClass('active');
    $(this).parent().toggleClass('active');
  });

  // Toggle the accordions when clicking on a funnel
  $(document).on('click', '.archive-page .funnel-icon-wrapper', () => {
    const accordions = $('.archive-page .filter-block.accordion');
    accordions.toggleClass('hidden');
  });

  // Hide the filters on initial load in mobile view
  $(document).ready(() => {
    const accordions = $('.archive-page .filter-block.accordion');
    if ($(window).width() < 768) {
      accordions.addClass('hidden');
    }
  });

  /**
   * ============================================
   * END
   * ============================================
   */
});
