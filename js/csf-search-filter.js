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
          if (html_data) {
            // update csf result area
            filter_area.html(html_data);
            // update csf form
            let form_id = filter_form.attr('id');
            if (csf_obj.dynamic_filter_item) {
              let update_csf_form = htmlDoc.find('#' + form_id).html();
              $('#' + form_id).html(update_csf_form);
            }
            check_old_active_filter_block(form_id);
            // update search url
            if (csf_obj.update_url) {
              window.history.replaceState('', 'url', submission_link);
            }
            // update csf result number
            let search_number_info = htmlDoc.find('#csf-get-result-info').html();
            if (search_number_info) {
              $('#csf-get-result-info').html(search_number_info);
            } else {
              $('#csf-get-result-info').html('');
            }
            //
            scroll_to_top_filter(form_id);
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
    // form submit
    $(document).on('submit', 'form#' + csf_filter_form_id, function (e) {
      e.stopPropagation();
      var csf_result_area_id = filter_form.attr('result-area-id');
      var req = get_XMLHttpRequest_Data(csf_result_area_id, filter_form);
      return false;
    });

    // on change in filter item make form submit
    $(document).on(
      'change',
      '#' +
      csf_filter_form_id +
      ' select, #' +
      csf_filter_form_id +
      ' input[type="radio"], #' +
      csf_filter_form_id +
      ' input[type="checkbox"]',
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

    // Pagination filter
    $(document).on('click', '.pagination a', function (e) {
      e.preventDefault();
      e.stopPropagation();
      scroll_to_top_filter(csf_filter_form_id);
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
  function check_old_active_filter_block(form_id) {
    if (change_filte_item_id) {
      $(document).ready(function () {
        let filter_item = $('#' + form_id + ' #' + change_filte_item_id);
        if (filter_item) {
          let filter_block = filter_item.parents('.filter-block');
          filter_block.addClass('active-show-options');
        }
      });

    }
  }

  // toggle filter-block options
  $(document).on('click', '.filter-block', function (e) {
    // $(this).toggleClass('active-show-options');
    let class_name = "active-show-options";
    if ($(this).hasClass(class_name)) {
      $(this).removeClass(class_name);
    } else {
      $('.filter-block').removeClass(class_name);
      $(this).addClass(class_name);
    }
  });


  // scroll_to_top_filter
  function scroll_to_top_filter(csf_filter_form_id) {
    // $(window).scrollTop(0);
    $('html, body').animate({
      scrollTop: $('form#' + csf_filter_form_id).offset().top - 250
    }, 0); // Duration in milliseconds (1 second)
  }

  /**
   * ============================================
   * END
   * ============================================
   */
});
