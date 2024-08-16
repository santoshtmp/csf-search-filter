<?php

namespace custom_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Search_Filter_Post_Type
{

    private $label = 'Search Filter';
    private $slug = 'custom-search-filter';
    private static $static_slug = 'custom-search-filter';
    private $meta_key = '_custom_search-filter-settings';


    public function __construct()
    {
        add_action('init', [$this, 'register_search_filter_post_type']);
        add_filter('manage_' . $this->slug . '_posts_columns', [$this, 'add_csf_column_header']);
        add_action('manage_' . $this->slug . '_posts_custom_column', [$this, 'populate_csf_column_data'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_search_posts_meta_boxes']);
        add_action('save_post', [$this, 'save_search_post_meta_box_data']);
    }

    public static function get_seach_filter_post_type()
    {
        return self::$static_slug;
    }

    // register the "search filter" post type
    public function register_search_filter_post_type()
    {
        $labels = array(
            'name'               => _x($this->label, 'post type general name'),
            'singular_name'      => _x($this->label, 'post type singular name'),
            'add_new'            => __('Add New ' . $this->label),
            'add_new_item'       => __('Add New ' . $this->label),
            'edit_item'          => __('Edit ' . $this->label),
            'new_item'           => __('New ' . $this->label),
            'view_item'          => __('View ' . $this->label),
            'search_items'       => __('Search ' . $this->label),
            'not_found'          => __('No "' . $this->label . '" found'),
            'not_found_in_trash' => __('No "' . $this->label . '" found in Trash'),
            'menu_name'          =>  $this->label
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            // 'publicly_queryable' => false,
            'show_in_menu'       => true,
            'query_var'          => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'show_ui'         => true,
            '_builtin'        => false,
            'rewrite'         => false,
            'supports'        => array('title'),
            // 'show_in_menu'    => false,
        );

        register_post_type($this->slug, $args);
    }

    // Add a new column to the post type list table
    public function add_csf_column_header($columns)
    {
        unset($columns['date']);
        $columns['shortcode']     = __('Shortcode', $this->slug);
        $columns['posttype'] = __('Post Type', $this->slug);
        $columns['date']          = __('Date', $this->slug);

        return $columns;
    }

    // Populate the csf column with data
    public function populate_csf_column_data($column, $post_id)
    {
        switch ($column) {
            case 'shortcode':
                echo '[customsearchfilter id="' . $post_id . '"]';
            case 'posttype':
                echo get_post_meta($post_id, 'select_posttype', true);
        }
    }

    // 
    // 
    public function add_search_posts_meta_boxes()
    {
        $screen = $this->slug;

        add_meta_box(
            'search-filter-settings',   // ID of the meta box
            __('Custom Search Filter Settings', $this->slug), // Title of the meta box
            array($this, 'load_search_form_settings_metabox'), // Callback function to display the form fields
            $screen,     // Post type where meta box will appear
            'advanced',     // Context (side, normal, advanced)
            'high'       // Priority (default, high, low)
        );

        add_meta_box(
            $this->slug . '-shortcodes',
            __('Shortcodes', $this->slug),
            array($this, 'load_search_form_shortcode_metabox'),
            $screen,
            'side'
        );
    }

    // add_meta_box callback
    function load_search_form_shortcode_metabox($object, $box)
    {
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin-search-form-shortcode-metabox.php';
    }

    //
    function save_search_post_meta_box_data($post_id)
    {
        /* Verify the nonce before proceeding. */
        if (!isset($_POST[$this->slug . '_nonce']) || !wp_verify_nonce($_POST[$this->slug . '_nonce'], 'search_filter_nonce')) {
            return $post_id;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $settings = [];
        $settings['filter_setting_post_type'] = '';
        $settings['posts_per_page'] = '';
        // $settings['main_query'] = 0;
        $settings['auto_submit'] = '';
        $settings['reset_submit_btn'] = '';
        $settings['field_relation'] = '';
        $settings['fields_free_text_input'] = '';
        $settings['fields'] = [];
        $settings['apply_only_on_show_result'] = '';
        $settings['search_filter_result_template'] = '';
        $settings['search_filter_css_class'] = '';

        // Sanitize and save the meta field data
        if (isset($_POST['filter_setting_post_type'])) {
            $settings['filter_setting_post_type'] = sanitize_key($_POST['filter_setting_post_type']);
        }

        if (isset($_POST['posts_per_page'])) {
            $settings['posts_per_page'] = (int) $_POST['posts_per_page'];
        }

        // if (isset($_POST['main_query'])) {
        //     $settings['main_query'] = (int) $_POST['main_query'];
        // }

        if (isset($_POST['auto_submit'])) {
            $settings['auto_submit'] = (int) $_POST['auto_submit'];
        }

        if (isset($_POST['reset_submit_btn'])) {
            $settings['reset_submit_btn'] = (int) $_POST['reset_submit_btn'];
        }

        if (isset($_POST['fields_free_text_input'])) {
            $settings['fields_free_text_input'] = (int)($_POST['fields_free_text_input']);
        }
        if (isset($_POST['field_relation'])) {
            $settings['field_relation'] = sanitize_key($_POST['field_relation']);
        }
        if (isset($_POST['fields'])) {
            $settings['fields'] = $_POST['fields'];
        }
        if (isset($_POST['search_filter_css_class'])) {
            $settings['search_filter_css_class'] = (string)($_POST['search_filter_css_class']);
        }
        if (isset($_POST['apply_only_on_show_result'])) {
            $settings['apply_only_on_show_result'] = (int)($_POST['apply_only_on_show_result']);
        }
        if (isset($_POST['search_filter_result_template'])) {
            $settings['search_filter_result_template'] = (string)($_POST['search_filter_result_template']);
        }
        // var_dump($settings['fields']);
        // die;

        /* Get the meta value of the custom field key. */
        $meta_value = get_post_meta($post_id, $this->meta_key, true);
        if ($settings && '' == $meta_value) {
            /* If a new meta value was added and there was no previous value, add it. */
            add_post_meta($post_id, $this->meta_key, $settings, true);
        } elseif ($settings && $settings != $meta_value) {
            /* If the new meta value does not match the old value, update it. */
            update_post_meta($post_id, $this->meta_key, $settings);
        } elseif ('' == $settings && $meta_value) {
            /* If there is no new meta value but an old value exists, delete it. */
            delete_post_meta($post_id, $this->meta_key, $meta_value);
        }
    }

    // 
    function load_search_form_settings_metabox()
    {
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin-search-form-settings-metabox.php';
    }

    function set_selected($desired_value, $current_value, $echo = true)
    {
        if ($desired_value == $current_value) {
            if ($echo == true) {
                echo ' selected="selected"';
            } else {
                return ' selected="selected"';
            }
        }
    }

    function set_radio($desired_value, $current_value, $echo = true)
    {
        if ($desired_value == $current_value) {
            if ($echo == true) {
                echo ' checked="checked"';
            } else {
                return ' checked="checked"';
            }
        }
    }

    function set_checked($current_value)
    {
        if (1 === absint($current_value)) {
            echo ' checked="checked"';
        }
    }
}

global $search_filter_post_type;
$search_filter_post_type = new Search_Filter_Post_Type();
