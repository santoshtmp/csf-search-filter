<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Version: 1.0
 * =======================================
 */

namespace csf_search_filter;

use WP_Query;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}



// class to handle SF data
class CSF_Data
{

    /**
     * @param string $post_type
     * @param string  $meta_key => filter_term_key
     * @return array 
     */
    public static function get_csf_metadata($post_type, $meta_key, $metadata_reference = '', $meta_value_check = '', $all_post_ids = [])
    {
        if (!$post_type || !$meta_key) {
            return;
        }
        $seperate_meta_key =  explode("|", $meta_key);
        $seperate_metadata_reference =  explode("|", $metadata_reference);
        $meta_info = [];
        foreach ($seperate_meta_key as $key => $each_meta_key) {
            $each_metadata_reference = isset($seperate_metadata_reference[$key]) ? $seperate_metadata_reference[$key] : '';
            // 
            if ($all_post_ids && is_array($all_post_ids)) {
                foreach ($all_post_ids as $key => $post_id) {
                    $meta_info = self::check_post_meta_info($post_id,  $each_meta_key, $each_metadata_reference, $meta_info, false, $meta_value_check);
                }
            } else {
                global $wp_query;
                $wp_query_vars = $wp_query->query_vars;
                $post_args = [
                    'post_type' => $post_type,
                    'posts_per_page' => -1,
                    'post_status' => array('publish'),
                    'tax_query' => isset($wp_query_vars['tax_query']) ? $wp_query_vars['tax_query'] : [],
                    'meta_query' => isset($wp_query_vars['meta_query']) ? $wp_query_vars['meta_query'] : []
                ];
                // get all post from given post type and retrive given meta-key values
                $posts = get_posts($post_args);
                foreach ($posts as $key => $post) {
                    $meta_info = self::check_post_meta_info($post->ID,  $each_meta_key, $each_metadata_reference, $meta_info, false, $meta_value_check);
                }
            }
        }

        return $meta_info;
    }


    // 
    public static function check_post_meta_info($post_id, $meta_key, $metadata_reference, $meta_info = [], $return_field_term_id = false, $meta_value_check = '')
    {
        if (!$post_id || !$meta_key) {
            return;
        }
        $post_meta_value = true;
        $index = 0;
        while ($post_meta_value) {
            $meta_key_new = str_replace('{array}', $index, $meta_key);
            $meta_value = get_post_meta($post_id, $meta_key_new, true);
            if ($meta_value) {
                if ($meta_value_check && $meta_value_check == $meta_value) {
                    if (is_array($meta_value)) {
                        foreach ($meta_value as $key => $meta_val) {
                            $meta_info = self::check_add_meta_info($meta_info, $meta_val, $metadata_reference, $return_field_term_id);
                        }
                    } else {
                        $meta_info = self::check_add_meta_info($meta_info, $meta_value, $metadata_reference, $return_field_term_id);
                    }
                } else {
                    if (is_array($meta_value)) {
                        foreach ($meta_value as $key => $meta_val) {
                            $meta_info = self::check_add_meta_info($meta_info, $meta_val, $metadata_reference, $return_field_term_id);
                        }
                    } else {
                        $meta_info = self::check_add_meta_info($meta_info, $meta_value, $metadata_reference, $return_field_term_id);
                    }
                }
            }
            $index = $index + 1;
            if (!str_contains($meta_key, '{array}') || ! $meta_value) {
                $post_meta_value = false;
            }
        }
        return $meta_info;
    }



    /**
     * @param array $meta_info
     * @param string $meta_val
     * @param string $metadata_reference
     * @return array
     */
    public static function check_add_meta_info($meta_info, $meta_val, $metadata_reference = '', $return_field_term_id = false)
    {
        $meta_val_parent =  $meta_val_term_id = '';
        $meta_val_slug = $meta_val_name = $meta_val;
        if ($metadata_reference) {
            $reference = self::check_metadata_reference($meta_val, $metadata_reference);
            $meta_val_term_id = $reference['meta_val_term_id'];
            $meta_val_name = $reference['meta_val_name'];
            $meta_val_slug = $reference['meta_val_slug'];
            $meta_val_parent = $reference['meta_val_parent'];
        }
        if ($return_field_term_id) {
            $meta_info[$meta_val] = $meta_val_term_id;
        } else {
            if (isset($meta_info[$meta_val])) {
                if (array_key_exists($meta_val, $meta_info)) {
                    $meta_info[$meta_val]['count'] = $meta_info[$meta_val]['count'] + 1;
                }
            } else {
                $meta_info[$meta_val]['value'] = $meta_val;
                $meta_info[$meta_val]['slug'] = $meta_val_slug;
                $meta_info[$meta_val]['name'] = $meta_val_name;
                $meta_info[$meta_val]['term_id'] = $meta_val_term_id;
                $meta_info[$meta_val]['parent'] = $meta_val_parent;
                $meta_info[$meta_val]['count'] = 1;
            }
        }
        return $meta_info;
    }


    /**
     *  @return array [
     *    'meta_val_term_id' => $meta_val_term_id,
     *    'meta_val_name' => $meta_val_name,
     *    'meta_val_slug' => $meta_val_slug,
     *    'meta_val_parent' => $meta_val_parent,
     *  ];
     */
    public static function check_metadata_reference($meta_val, $metadata_reference)
    {
        $current_term = $meta_val_parent =  $meta_val_term_id = $meta_val_name = $meta_val_slug = $meta_val;
        $metadata_reference = explode(',', $metadata_reference);
        if (isset($metadata_reference[0])) {
            if ($metadata_reference[0] == 'taxonomy') {
                if (intval($meta_val)) {
                    $current_term = get_term($meta_val);
                } else {
                    if (isset($metadata_reference[1])) {
                        $current_term = get_term_by('slug', $meta_val, $metadata_reference[1]);
                    }
                }
                if ($current_term) {
                    $meta_val_name = $current_term->name;
                    $meta_val_slug = $current_term->slug;
                    $meta_val_parent = $current_term->parent;
                    $meta_val_term_id = $current_term->term_id;
                }
            } else if ($metadata_reference[0] == 'post') {
                $meta_val_name = get_the_title($meta_val);
            } else {
                if (function_exists($metadata_reference[0])) {
                    $data = $metadata_reference[0]();
                    $meta_val_name =  (isset($data[$meta_val])) ? $data[$meta_val] : $meta_val_name;
                }
            }
        }
        return [
            'meta_val_term_id' => $meta_val_term_id,
            'meta_val_name' => $meta_val_name,
            'meta_val_slug' => $meta_val_slug,
            'meta_val_parent' => $meta_val_parent,
        ];
    }


    /**
     * Count the total meta value in the  post type or list of provided post ids
     * @param string $post_type
     * @param string  $meta_key => filter_term_key
     * @return array 
     */
    public static function get_meta_value_count($post_type, $meta_key, $meta_value, $metadata_reference, $all_post_ids = [])
    {
        $meta = self::get_csf_metadata($post_type, $meta_key, $metadata_reference, $meta_value, $all_post_ids);
        if ($meta) {
            foreach ($meta as $key => $value) {
                if ($value['value'] == $meta_value) {
                    return $value['count'];
                }
            }
        }
        return 0;
    }



    // SF_Data class end

}
