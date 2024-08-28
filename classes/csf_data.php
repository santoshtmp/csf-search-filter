<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Plugin URI: https://github.com/santoshtmp/csf-search-filter
 * Version: 1.0
 * Author: santoshtmp
 * =======================================
 */

namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}



// class to handle SF data
class CSF_Data
{

    public function __construct()
    {
        $emable_csf_cache_meta = (get_option('emable_csf_cache_meta')) ?: '';
        if ($emable_csf_cache_meta) {
            add_action('after_switch_theme', [$this, 'create_csf_cache_table']);
            add_action('save_post', [$this, 'before_post_save_csf_cache_metadata'], 7);
        }
    }

    /**
     * @param string $post_type
     * @param string $meta_key => filter_term_key
     * @return array 
     */
    public static function get_csf_metadata($post_type, $meta_key, $metadata_reference = '')
    {
        global $wpdb;
        $results = '';
        $emable_csf_cache_meta = (get_option('emable_csf_cache_meta')) ?: '';
        if ($emable_csf_cache_meta) {
            $table_name = $wpdb->prefix . 'csf_cache_metadata';
            $cache_metadata = [];
            $sql_prepare = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_type = %s AND filter_meta_key = %s",
                $post_type,
                $meta_key
            );
            $results = $wpdb->get_results($sql_prepare);
        }
        if ($results) {
            foreach ($results as $key => $result) {
                if ($result->count > 0) {
                    $reference = self::check_metadata_reference($result->meta_value, $metadata_reference);
                    $data = [];
                    $data['id'] = $result->id;
                    $data['post_type'] = $result->post_type;
                    $data['meta_key'] = $result->filter_meta_key;
                    $data['value'] = $result->meta_value;
                    $data['term_id'] = $result->term_id;
                    $data['metadata_reference'] = $result->metadata_reference;
                    $data['count'] = $result->count;
                    $data['slug'] = $reference['meta_val_slug'];
                    $data['name'] = $reference['meta_val_name'];
                    $data['parent'] = $reference['meta_val_parent'];

                    $cache_metadata[] = $data;
                }
            }
        } else {
            $cache_metadata = self::get_csf_meta_info($post_type, $meta_key, $metadata_reference);
        }
        return $cache_metadata;
    }

    /**
     * @param string $post_type
     * @param string  $meta_key => filter_term_key
     * @return array 
     */
    public static function get_meta_value_count($post_type, $meta_key, $meta_value, $metadata_reference)
    {
        global $wpdb;
        $results = '';
        $emable_csf_cache_meta = (get_option('emable_csf_cache_meta')) ?: '';
        if ($emable_csf_cache_meta) {
            $table_name = $wpdb->prefix . 'csf_cache_metadata';
            $results = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE post_type = %s AND filter_meta_key = %s AND meta_value = %s",
                    $post_type,
                    $meta_key,
                    $meta_value
                )
            );
        }
        if ($results) {
            return $results->count;
        } else {
            $meta = self::get_csf_meta_info($post_type, $meta_key, $metadata_reference, $meta_value);
            if ($meta) {
                foreach ($meta as $key => $value) {
                    if ($value['value'] == $meta_value) {
                        return $value['count'];
                    }
                }
            }
        }
        return 0;
    }

    /**
     * @param string $post_type
     * @param string  $meta_key => filter_term_key
     * @return array 
     */
    public static function get_csf_meta_info($post_type, $meta_key, $metadata_reference = '', $meta_value_check = '')
    {
        if (!$post_type || !$meta_key) {
            return;
        }
        $meta_info = [];
        $post_args = [
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => array('publish'),
        ];
        // get all post from given post type and retrive given meta-key values
        $posts = get_posts($post_args);
        foreach ($posts as $key => $post) {
            $meta_info = self::check_post_meta_info($post->ID,  $meta_key, $metadata_reference, $meta_info, false, $meta_value_check);
        }
        asort($meta_info);
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
                    var_dump($meta_value);
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
     * https://developer.wordpress.org/reference/functions/register_activation_hook/
     * https://codex.wordpress.org/Creating_Tables_with_Plugins
     * https://developer.wordpress.org/reference/classes/wpdb/
     * https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
     * https://developer.wordpress.org/reference/hooks/save_post/
     */
    public static function create_csf_cache_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'csf_cache_metadata'; // Define your table name
        $charset_collate = $wpdb->get_charset_collate();
        $csf_cache_metadata_version_new = 1.0;
        $csf_cache_metadata_version_old = get_option('csf_cache_metadata_version');
        // if ($csf_cache_metadata_version_new > $csf_cache_metadata_version_old) {
        $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                post_type varchar(255) NOT NULL,
                filter_meta_key varchar(255) NOT NULL,
                meta_value varchar(255) NOT NULL,
                term_id bigint(20) NOT NULL,
                metadata_reference varchar(255) NOT NULL,
                count bigint(20) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql); // This will create or update the table
        update_option('csf_cache_metadata_version', $csf_cache_metadata_version_new);

        $fields = \csf_search_filter\CSF_Fields::set_csf_cache_metadata_fields();
        if ($fields) {
            foreach ($fields as $post_type => $meta_keys) {
                if (is_array($meta_keys)) {
                    foreach ($meta_keys as $index_key => $meta_key) {
                        $filter_meta_key = (isset($meta_key['filter_meta_key'])) ? $meta_key['filter_meta_key'] : '';
                        $metadata_reference = (isset($meta_key['metadata_reference'])) ? $meta_key['metadata_reference'] : '';
                        $csf_meta_info = \csf_search_filter\CSF_Data::get_csf_meta_info($post_type, $filter_meta_key, $metadata_reference);
                        if ($csf_meta_info && is_array($csf_meta_info)) {
                            foreach ($csf_meta_info as $key => $meta_info) {
                                $data = [
                                    'post_type' => $post_type,
                                    'filter_meta_key' => $filter_meta_key,
                                    'meta_value' => $meta_info['value'],
                                    'term_id' => $meta_info['term_id'],
                                    'metadata_reference' => $metadata_reference,
                                    'count' => $meta_info['count'],
                                ];
                                \csf_search_filter\CSF_Data::set_csf_cache_data($data);
                            }
                        }
                    }
                }
            }
        }
        // }
    }
    // 
    public static function set_enable_csf_cache_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'csf_cache_metadata'; // Define your table name
        $find_table_name = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );

        if ($find_table_name != $table_name) {
            self::create_csf_cache_table();
        }
    }
    // 
    public static function clear_delete_csf_cache_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'csf_cache_metadata'; // Define your table name
        $find_table_name = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );
        // Run the query
        if ($find_table_name == $table_name) {
            // SQL query to drop the table
            // $wpdb->prepare(
            //     "DROP TABLE IF EXISTS %s",
            //     $table_name
            // )
            $results = $wpdb->query(
                "DROP TABLE IF EXISTS $table_name"
            );
        }

        // Check if the table was successfully deleted
        $find_table_name_check_again = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );
        if ($find_table_name_check_again != $table_name) {
            return true;
        } else {
            return false;
        }
    }

    // 
    public static function set_csf_cache_data($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'csf_cache_metadata';
        // get data
        $post_type = $data['post_type'];
        $filter_meta_key = $data['filter_meta_key'];
        $meta_value = $data['meta_value'];
        $term_id = ($data['term_id']) ?: 0;
        $metadata_reference = ($data['metadata_reference']) ?: "filter_meta_key";
        $count = ($data['count']) ?: 1;
        // required data
        if (!$post_type || !$filter_meta_key || !$meta_value) {
            return false;
        }

        // arrange the data
        $table_data = [
            'post_type' => $post_type,
            'filter_meta_key' => $filter_meta_key,
            'meta_value' => $meta_value,
            'term_id' => $term_id,
            'metadata_reference' => $metadata_reference,
            'count' => $count,
        ];
        $table_data_format = ['%s', '%s', '%s', '%d', '%s', '%d'];
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_type = %s AND filter_meta_key = %s AND meta_value = %s",
                $post_type,
                $filter_meta_key,
                $meta_value
            )
        );
        if ($results) {
            $count_increase  = isset($data['count_increase']) ? $data['count_increase'] : true;
            $count_val = ($count_increase) ? (int)$results->count + 1 : (int)$results->count - 1;
            $table_data['count'] = ($count_val < 0) ? 0 : $count_val;
            $update = $wpdb->update(
                $table_name,
                $table_data,
                ['id' => $results->id],
                $table_data_format,
                ['%d']
            );
            return $update;
        } else {
            $insert = $wpdb->insert(
                $table_name,
                $table_data,
                $table_data_format,
            );
            return $insert;
        }
        return true;
    }


    // convert multi array in single key value array
    public function get_all_keys_values($array, $parent_key = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Concatenate parent key if it exists (for nested keys)
            $full_key = $parent_key ? $parent_key . '.' . $key : $key;

            if (is_array($value)) {
                // If the value is an array, recursively get its keys and values
                $result = array_merge($result, self::get_all_keys_values($value, $full_key));
            } else {
                // If it's a scalar value, store the key-value pair
                $result[$full_key] = $value;
            }
        }

        return $result;
    }

    // 
    public function check_post_fields_value($current_result, $form_field_name, $metadata_reference, $new_array_val = [], $return_field_term_id = false)
    {
        $fields = explode('{array}', $form_field_name);
        $field = isset($fields[1]) ? $fields[1] : $fields[0];
        $field = ($field) ? $field : $fields[0];
        if ($field) {
            foreach ($current_result as $key => $value) {
                $is_present = str_contains($key, $field);
                if ($is_present && $value) {
                    $new_array_val = self::check_add_meta_info($new_array_val, $value, $metadata_reference,  $return_field_term_id);
                }
            }
        }
        return $new_array_val;
    }

    //  https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
    public function before_post_save_csf_cache_metadata($post_id)
    {
        // Check if the current user has permission to edit the post
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Avoid auto-save, bulk edits, or other non-standard post saving events
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 
        if ($post_id) {

            // // Verify the default _wpnonce (WordPress automatically generates this for saving posts)
            // if (! isset($_POST['_wpnonce']) || ! check_admin_referer('update-post_' . $post_id)) {
            //     return;
            // }

            $fields = \csf_search_filter\CSF_Fields::set_csf_cache_metadata_fields();
            if ($fields) {
                $current_post_type = get_post_type($post_id);
                $current_result = self::get_all_keys_values($_POST);
                foreach ($fields as $post_type => $fields_value) {
                    if ($current_post_type !== $post_type) {
                        continue;
                    }
                    if (is_array($fields_value)) {
                        foreach ($fields_value as $index_key => $field) {
                            $filter_meta_key = (isset($field['filter_meta_key'])) ? $field['filter_meta_key'] : '';
                            $metadata_reference = (isset($field['metadata_reference'])) ? $field['metadata_reference'] : '';
                            $form_field_name = (isset($field['form_field_name'])) ? $field['form_field_name'] : '';
                            if ($form_field_name) {
                                if (str_contains($form_field_name, "{array}")) {
                                    $old_array_val = \csf_search_filter\CSF_Data::check_post_meta_info($post_id, $filter_meta_key, $metadata_reference, [], true);
                                    $new_array_val = \csf_search_filter\CSF_Data::check_post_fields_value($current_result, $form_field_name, $metadata_reference, [], true);
                                    $uniq_old_array_val = array_diff($old_array_val, $new_array_val);
                                    $uniq_new_array_val = array_diff($new_array_val, $old_array_val);
                                    // old count decrease
                                    foreach ($uniq_old_array_val as $key => $value) {
                                        $old_data = [
                                            'post_type' => $post_type,
                                            'filter_meta_key' => $filter_meta_key,
                                            'meta_value' => $key,
                                            'term_id' => $value,
                                            'metadata_reference' => $metadata_reference,
                                            'count' => 1,
                                            'count_increase' => false
                                        ];
                                        $result = \csf_search_filter\CSF_Data::set_csf_cache_data($old_data);
                                    }
                                    // new count increase
                                    foreach ($uniq_new_array_val as $key => $value) {
                                        $new_data = [
                                            'post_type' => $post_type,
                                            'filter_meta_key' => $filter_meta_key,
                                            'meta_value' => $key,
                                            'term_id' => $value,
                                            'metadata_reference' => $metadata_reference,
                                            'count' => 1,
                                            'count_increase' => true
                                        ];
                                        $result = \csf_search_filter\CSF_Data::set_csf_cache_data($new_data);
                                    }
                                } else {
                                    $old_val = get_post_meta($post_id, $filter_meta_key, true);
                                    $new_val = (isset($current_result[$form_field_name])) ? $current_result[$form_field_name] : '';
                                    if ($new_val != $old_val) {
                                        // new data count increase
                                        $new_meta_info = self::check_add_meta_info([], $new_val, $metadata_reference);
                                        foreach ($new_meta_info as $key => $meta_data) {
                                            $new_data = [
                                                'post_type' => $post_type,
                                                'filter_meta_key' => $filter_meta_key,
                                                'meta_value' => $meta_data['value'],
                                                'term_id' => $meta_data['term_id'],
                                                'metadata_reference' => $metadata_reference,
                                                'count' => $meta_data['count'],
                                                'count_increase' => true
                                            ];
                                            $result = \csf_search_filter\CSF_Data::set_csf_cache_data($new_data);
                                        }

                                        // old data count decrease
                                        $old_meta_info = self::check_add_meta_info([], $old_val, $metadata_reference);
                                        foreach ($old_meta_info as $key => $meta_data) {
                                            $old_data = [
                                                'post_type' => $post_type,
                                                'filter_meta_key' => $filter_meta_key,
                                                'meta_value' => $meta_data['value'],
                                                'term_id' => $meta_data['term_id'],
                                                'metadata_reference' => $metadata_reference,
                                                'count' => $meta_data['count'],
                                                'count_increase' => false
                                            ];
                                            $result = \csf_search_filter\CSF_Data::set_csf_cache_data($old_data);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    // SF_Data class end

}
