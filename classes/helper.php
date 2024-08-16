<?php

namespace custom_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
class Helper
{

    /**
     * @param string $post_type
     * @return array
     */
    public static function get_post_all_meta_data($post_type)
    {
        global $wpdb;
        $meta_datas = [];

        // Get all post IDs for the specified post type
        $post_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE post_type = %s",
            $post_type
        ));
        // Get all meta keys for these posts
        if (!empty($post_ids)) {
            $ignore_meta_keys = "('_edit_last', '_edit_lock', '_thumbnail_id')";
            $sql_query = "  SELECT DISTINCT meta_key 
											FROM $wpdb->postmeta 
											WHERE post_id IN (" . implode(',', array_map('intval', $post_ids)) . ") AND meta_key NOT IN $ignore_meta_keys";
            $meta_keys = $wpdb->get_col($wpdb->prepare($sql_query));
            if (!empty($meta_keys)) {
                foreach ($meta_keys as $meta_key) {
                    // $val = ltrim($meta_key, '_');
                    // $meta_datas[$val] =  $val;
                    $first_character = substr($meta_key, 0, 1);
                    if ($first_character != '_') {
                        $meta_datas[$meta_key] =  $meta_key;
                    }
                }
                asort($meta_datas);
            }
        }
        return $meta_datas;
    }


    /**
     * @param string $post_type
     * @param string $meta_key
     * @return array 
     */
    public static function get_unique_post_meta_count_info($post_type, $meta_key)
    {
        $meta_info = [];
        $post_args = [
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => array('publish'),
        ];
        // get all post from given post type and retrive given meta-key values
        $posts = get_posts($post_args);
        foreach ($posts as $key => $post) {
            $post_meta_key_data = get_post_meta($post->ID, $meta_key);
            if ((isset($post_meta_key_data[0]))) {
                $value = $post_meta_key_data[0];
                if ($value) {
                    if (isset($meta_info[$value])) {
                        if (array_key_exists($value, $meta_info)) {
                            // $meta_info[$value]['value'] = $value;
                            // $meta_info[$value]['slug'] = $value;
                            // $meta_info[$value]['name'] = $value;
                            $meta_info[$value]['count'] = $meta_info[$value]['count'] + 1;
                        }
                    } else {
                        $meta_info[$value]['value'] = $value;
                        $meta_info[$value]['slug'] = $value;
                        $meta_info[$value]['name'] = $value;
                        $meta_info[$value]['count'] = 1;
                    }
                }
            }
        }
        asort($meta_info);
        return $meta_info;
    }


    //
    public static function load_search_add_field($post_type, $index_field = 0, $settings = [])
    {
        if (!$post_type) {
            return '';
        }
        if (empty($settings)) {
            $default_field = [
                'search_field_data' => 'taxonomy',
                'search_field_taxonomy' => '',
                'search_field_metadata' => '',
                'search_field_type' => 'dropdown',
                'display_count' => 1,
            ];
            $settings = $default_field;
        }
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'includes/admin-search-add-fields.php';
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }




    // 
    public static function get_settings_meta($csf_id)
    {
        $settings = get_post_meta($csf_id, '_custom_search-filter-settings', true);

        if (!is_array($settings)) {
            $settings = array();
        }

        return $settings;
    }

    // 
    public static function get_csf_field_name($display_name)
    {
        $field_name = "csf_" . str_replace([' ', '-'], '_', strtolower($display_name));
        return  $field_name;
    }
}
