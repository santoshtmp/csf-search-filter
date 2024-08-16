<?php


/**
 * ===================================================
 *  Enqueue in backend admin area
 * ===================================================
 */
function admin_custom_search_filter_scripts()
{
    // check post title
    global $pagenow,  $post_type;
    $js_file_path = csf_path . 'assets/js/form-settings-metabox.js';
    $seach_filter_post_type = \custom_search_filter\Search_Filter_Post_Type::get_seach_filter_post_type();
    if ($post_type === $seach_filter_post_type and ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php')) {
        wp_enqueue_script(
            'search-filter-form-settings-metabox',
            $js_file_path,
            array('jquery'),
            filemtime(get_stylesheet_directory($js_file_path)),
            array(
                'in_footer' => true,
                'strategy' => 'defer'
            )
        );
        // Pass the AJAX URL and nonce to the script
        wp_localize_script('search-filter-form-settings-metabox', 'csf_obj', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('csf_add_data_field_wrapper')
        ));
    }
}
add_action('admin_enqueue_scripts', 'admin_custom_search_filter_scripts');
