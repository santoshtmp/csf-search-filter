<?php

/**
 * Reference : 
 * https://developer.wordpress.org/reference/hooks/pre_get_posts/
 * https://developer.wordpress.org/reference/hooks/posts_join/
 * https://developer.wordpress.org/reference/hooks/posts_where/
 * https://developer.wordpress.org/reference/hooks/posts_groupby/
 * https://developer.wordpress.org/reference/hooks/posts_orderby/
 * 
 * https://www.lab21.gr/blog/extend-the-where-clause-in-wordpress-wp_query/ 
 */


namespace custom_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class search_filter_query
{

    private $all_search_form_ids = null;


    public function __construct()
    {
        add_action('parse_request', [$this, 'parse_query_init'], 9);
        add_action('pre_get_posts', [$this, 'custom_search_filter_query'], 11);
        add_filter('posts_where', [$this, 'where_post_case'], 10, 2);
        add_filter('posts_join', [$this, 'join_post_case']);
        add_filter('posts_groupby', [$this, 'groupby_post_case']);
        add_filter('posts_orderby', [$this, 'edit_posts_orderby']);
    }

    public function parse_query_init($wp)
    {
        if (!is_admin()) {
            $args = [
                'post_type' => 'custom-search-filter',
                'posts_per_page' => -1,
                'post_status' => array('publish'),
                'fields' => 'ids'
            ];
            $this->all_search_form_ids = get_posts($args);
        }
    }

    /**
     * 
     */
    public function custom_search_filter_query($query)
    {
        if (!is_admin() && $query->is_main_query()) {
            foreach ($this->all_search_form_ids as $search_form_id) {
                $search_form_settings = \custom_search_filter\Helper::get_settings_meta($search_form_id);
                $apply_only_on_show_result = $search_form_settings['apply_only_on_show_result'];
                if (!$apply_only_on_show_result || empty($apply_only_on_show_result)) {
                    $this->csf_query($search_form_settings, $query);
                }
            }
        }
    }

    /**
     * 
     */
    public function csf_query($search_form_settings, $query, $return_query = false)
    {
        $post_type = $search_form_settings['filter_setting_post_type'];
        $query_post_type = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : '';
       var_dump('outttttt');
        if ($post_type === $query_post_type) {
            var_dump('innnnnnnnnn');
            $posts_per_page = (int)$search_form_settings['posts_per_page'];
            $fields_free_text_input = (int)$search_form_settings['fields_free_text_input'];
            $fields = $search_form_settings['fields'];
            // post per page 
            if ($posts_per_page) {
                $query->set('posts_per_page', $posts_per_page);
            }
            // free text search
            if ($fields_free_text_input) {
                $_GET_search_text = isset($_GET['csf_search']) ? $_GET['csf_search'] : '';
                if ($_GET_search_text) {
                    $custom_search_post['search'] = $_GET_search_text;
                }
            }
            // all other fields
            $tax_query =  [];
            $meta_query = [];
            foreach ($fields as $key =>  $field) {
                // field display name
                $filter_title = $field['display_name'];
                if (!$filter_title) {
                    break;
                    // if there is no name then go to next field
                }
                $field_name = \custom_search_filter\Helper::get_csf_field_name($filter_title);
                $_GET_field_name = isset($_GET[$field_name]) ? $_GET[$field_name] : '';
                // field data and data type
                $search_field_type = $field['search_field_type'];
                $search_field_data = $field['search_field_data'];
                // taxonomy field data
                if ($search_field_data == 'taxonomy') {
                    $search_field_taxonomy = $field['search_field_taxonomy'];
                    if ($search_field_type == 'checkbox') {
                        if ($_GET_field_name && is_array($_GET_field_name)) {
                            foreach ($_GET_field_name as $key => $_GET_term_slug) {
                                $tax_query[] = $this->tax_filter_query($search_field_taxonomy, $_GET_term_slug);
                            }
                        }
                    }
                    if ($search_field_type == 'dropdown') {
                        if ($_GET_field_name) {
                            $tax_query[] = $this->tax_filter_query($search_field_taxonomy, $_GET_field_name);
                        }
                    }
                }
                // metadata field data
                if ($search_field_data === 'metadata') {
                    $search_field_metadata = $field['search_field_metadata'];
                    if ($search_field_type == 'checkbox') {
                        if ($_GET_field_name && is_array($_GET_field_name)) {
                            foreach ($_GET_field_name as $key => $_GET_meta_value) {
                                $meta_query[] = $this->meta_filter_query($search_field_metadata, $_GET_meta_value);
                            }
                        }
                    }
                    if ($search_field_type == 'dropdown') {
                        if ($_GET_field_name) {
                            $meta_query[] = $this->meta_filter_query($search_field_metadata, $_GET_field_name);
                        }
                    }
                }
            }
            $custom_search_post['tax_query'] = $tax_query;
            $custom_search_post['meta_query'] = $meta_query;
            // set custom_search_post
            $query->set('csf_posts', $custom_search_post);
            if ($return_query) {
                return $query;
            }
            return;
        }
    }


    /**
     * 
     */
    public function each_csf_query($csf_id) {}

    // 
    public function tax_filter_query($taxonomy, $terms_slug)
    {
        return array(
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => $terms_slug,
        );
    }


    // 
    public function meta_filter_query($meta_key, $meta_value, $compare = '=')
    {
        return array(
            'key' => $meta_key,
            'value' => $meta_value,
            'compare' => $compare,
        );
    }

    /**
     * custom_search_post for where case
     */
    // add_filter('posts_where', 'where_post_case', 10, 2);
    function where_post_case($where, $wp_query)
    {
        global $wpdb;
        if ($custom_search_post = $wp_query->get('csf_posts')) {
            $custom_where = [];
            $relation = (isset($custom_search_post['relation'])) ? $custom_search_post['relation'] : 'OR';
            $search = (isset($custom_search_post['search'])) ? $custom_search_post['search'] : '';
            $meta_query = (isset($custom_search_post['meta_query'])) ? $custom_search_post['meta_query'] : '';
            $tax_query = (isset($custom_search_post['tax_query'])) ? $custom_search_post['tax_query'] : '';
            if ($search) {
                $custom_where[] =  $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like($search) . '%\' ';
            }
            if ($meta_query && is_array($meta_query)) {
                foreach ($meta_query as $key => $query) {
                    $meta_key = $query['key'];
                    $meta_value = $query['value'];
                    $compare = $query['compare'];
                    if ($compare === 'LIKE') {
                        $meta_value_compare = $compare . " '%$meta_value%' ";
                    } else {
                        $meta_value_compare = $compare . " '$meta_value' ";
                    }
                    $custom_where[] = " ($wpdb->postmeta.meta_key = '$meta_key' AND $wpdb->postmeta.meta_value $meta_value_compare) ";
                }
            }
            if ($tax_query && is_array($tax_query)) {
                foreach ($tax_query as $key => $query) {
                    $taxonomy = $query['taxonomy'];
                    $terms = $query['terms'];
                    $field = $query['field'];
                    if ($field === 'id') {
                        $custom_where[] = " $wpdb->term_relationships.term_taxonomy_id IN ($terms) ";
                    } else {
                        $current_term = get_term_by($field, $terms, $taxonomy);
                        if ($current_term) {
                            $current_term_id = $current_term->term_id;
                            $custom_where[] = " $wpdb->term_relationships.term_taxonomy_id IN ($current_term_id) ";
                        }
                    }
                }
            }

            if ($custom_where) {
                $where .= ' AND (' . implode($relation, $custom_where) . ') ';
            }
        }

        return $where;
    }


    /**
     * custom_search_post for join case
     */
    // add_filter('posts_join', 'join_post_case');
    function join_post_case($join)
    {
        global $wp_query, $wpdb;

        if ($custom_search_post = $wp_query->get('csf_posts')) {
            $meta_query = (isset($custom_search_post['meta_query'])) ? $custom_search_post['meta_query'] : '';
            $tax_query = (isset($custom_search_post['tax_query'])) ? $custom_search_post['tax_query'] : '';
            if ($meta_query && is_array($meta_query)) {
                $join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
            }
            if ($tax_query && is_array($tax_query)) {
                $join .= " LEFT JOIN $wpdb->term_relationships ON $wpdb->posts.ID = $wpdb->term_relationships.object_id ";
            }
        }
        return $join;
    }


    /**
     * custom_search_post for groupby case
     */
    // add_filter('posts_groupby', 'groupby_post_case');
    function groupby_post_case($groupby)
    {
        global $wp_query, $wpdb;

        if ($custom_search_post = $wp_query->get('csf_posts')) {
            $meta_query = (isset($custom_search_post['meta_query'])) ? $custom_search_post['meta_query'] : '';
            $tax_query = (isset($custom_search_post['tax_query'])) ? $custom_search_post['tax_query'] : '';
            if (($meta_query && is_array($meta_query)) || ($tax_query && is_array($tax_query))) {
                $groupby .= " $wpdb->posts.ID ";
            }
        }
        return $groupby;
    }


    /**
     * custom_search_post for orderby case
     */
    // add_filter('posts_orderby', 'edit_posts_orderby');
    function edit_posts_orderby($posts_orderby)
    {
        global $wp_query, $wpdb;

        if ($custom_search_post = $wp_query->get('csf_posts')) {
            $search = (isset($custom_search_post['search'])) ? $custom_search_post['search'] : '';
            if ($search) {
                $posts_orderby .=  ', ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like($search) . '%\' DESC ';
            }
        }
        return $posts_orderby;
    }

    /**
     * @param string $taxonomy
     * @param string $search_text
     * @return array
     */
    function get_taxonomy_with_name_like($taxonomy, $search_text)
    {
        global $wpdb;
        $search_term = '%' . $wpdb->esc_like($search_text) . '%';
        $query = $wpdb->prepare("
            SELECT t.name, t.term_id, t.slug
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = %s
            AND t.name LIKE %s
        ", $taxonomy, $search_term);
        $results = $wpdb->get_results($query);
        return $results;
    }
}

$search_filter_query = new search_filter_query();
