<?php

/**
 * 
 */

function add_sarch_data_field_wrapper()
{
    // Check for nonce security (optional)
    $valid_request = check_ajax_referer('csf_add_data_field_wrapper', 'nonce');
    if (!$valid_request) {
        echo "invalid-request";
        wp_die();
    }
    // Process the data sent via AJAX
    if (isset($_POST['next_field_num'])) {
        $next_field_num = (int)($_POST['next_field_num']);
        $post_type =  sanitize_text_field($_POST['post_type']);
        $fields =  \custom_search_filter\Helper::load_search_add_field($post_type, $next_field_num, $values = []);
        // Return a response
        echo $fields;
    } else {
        echo 'No data received.';
    }
    // Always die in functions echoing AJAX content
    wp_die();
}
add_action('wp_ajax_csf_add_data_field_wrapper', 'add_sarch_data_field_wrapper');
 // add_action('wp_ajax_nopriv_csf_add_data_field_wrapper', 'add_sarch_data_field_wrapper');
