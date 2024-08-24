<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Plugin URI: https://github.com/santoshtmp
 * Version: 1.0
 * Author: santoshtmp
 * =======================================
 */

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


namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

//class to hande SF Query
class CSF_Query
{

    public function __construct()
    {
        add_action('pre_get_posts', [$this, 'csf_search_filter_query'], 11);
        add_filter('posts_where', [$this, 'where_post_case'], 10, 2);
        add_filter('posts_join', [$this, 'join_post_case']);
        add_filter('posts_groupby', [$this, 'groupby_post_case']);
        add_filter('posts_orderby', [$this, 'edit_posts_orderby']);
    }

    /**
     * 
     */
    public function csf_search_filter_query($query)
    {
        if (!is_admin() && $query->is_main_query()) {
            $query_post_type = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : 'page';
            if (is_post_type_archive($query_post_type)) {

                $search_fields = \csf_search_filter\CSF_Fields::set_search_fields();
                $fields_settings = (isset($search_fields[$query_post_type])) ? $search_fields[$query_post_type] : '';
                if (! $fields_settings) {
                    return;
                }
                return $this->csf_query($fields_settings, $query);
            }
        }
    }


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
    public function csf_query($fields_settings, $query, $return_query = false)
    {

        // post per page 
        $posts_per_page = (isset($fields_settings['posts_per_page'])) ? $fields_settings['posts_per_page'] : 12;
        if ($posts_per_page) {
            $query->set('posts_per_page', $posts_per_page);
        }

        // search fields
        $post_type = (isset($fields_settings['post_type'])) ? $fields_settings['post_type'] : 'page';
        $fields = $fields_settings['fields'];
        $tax_query =  [];
        $meta_query = [];
        foreach ($fields as $key =>  $field) {
            // search_field_type => 'dropdown' or 'checkbox' or 'search_text'
            $search_field_type = (isset($field['search_field_type'])) ? $field['search_field_type'] : 'dropdown';
            if ($search_field_type === 'search_text') {
                $_GET_search_text = isset($_GET['search']) ? $_GET['search'] : '';
                if ($_GET_search_text) {
                    $custom_search_post['search'] = $_GET_search_text;
                    // other free_search
                    $free_search = (isset($fields_settings['free_search'])) ? $fields_settings['free_search'] : false;
                    if ($free_search) {
                        // Also perform free text search in below defined tax key
                        if ($_GET_search_text) {
                            $post_taxonomies = $fields_settings['free_search']['post_taxonomies'];
                            foreach ($post_taxonomies as $post_taxonomy) {
                                $term_ids = [];
                                $terms =  $this->get_taxonomy_with_name_like($post_taxonomy, $_GET_search_text);
                                if ($terms) {
                                    foreach ($terms as $key => $value) {
                                        if (!in_array($value->term_id, $term_ids)) {
                                            $term_ids[] = $value->term_id;
                                        }
                                    }
                                }
                                if ($term_ids) {
                                    $term_ids = implode(',', $term_ids);
                                    $tax_query[] = array(
                                        'taxonomy' => $post_taxonomy,
                                        'field' => 'id',
                                        'terms' => $term_ids,
                                    );
                                }
                            }
                        }
                        // Also perform free text search in below defined meta key value
                        $meta_keys = $fields_settings['free_search']['meta_keys'];
                        foreach ($meta_keys as $meta_key) {
                            $meta_query[] = array(
                                'key' => $meta_key,
                                'value' => $_GET_search_text,
                                'compare' => 'LIKE',
                            );
                        }
                    }
                }
                continue;
            }
            // filter_term_type => 'taxonomy' or 'metadata'
            $filter_term_type = (isset($field['filter_term_type'])) ? $field['filter_term_type'] : '';
            if (! $filter_term_type) {
                echo "search filter field term_type is not set/defind";
                continue;
            }

            // field display name
            $filter_title = (isset($field['display_name'])) ? $field['display_name'] : '';
            if (!$filter_title) {
                break;
                // if there is no name then go to next field
            }

            // get name and its value
            $field_name = \csf_search_filter\CSF_Form::get_search_field_name($filter_title);
            $_GET_field_name = isset($_GET[$field_name]) ? $_GET[$field_name] : '';

            // filter_term_key => 'taxonomy_key' or 'metadata_key'; [if meta_key is in array: example metakey_{array}_metakey]
            $filter_term_key = (isset($field['filter_term_key'])) ? $field['filter_term_key'] : '';
            if (! $filter_term_key) {
                echo "search filter field term_key is not set/defind";
                continue;
            }
            // metadata_reference => 'taxonomy,taxonomy_key' or 'post'; only apply to filter_term_key metadata_key
            $metadata_reference = (isset($field['metadata_reference'])) ? $field['metadata_reference'] : '';
            if ($metadata_reference) {
                $metadata_reference = explode(',', $metadata_reference);
                if ($metadata_reference[0] == 'taxonomy' && isset($metadata_reference[1])) {
                    // check if the taxonomy is associalted with current post type
                    $taxonomy = $metadata_reference[1];
                    $taxonomies = get_object_taxonomies($post_type);
                    if (in_array($taxonomy, $taxonomies)) {
                        $filter_term_type = 'taxonomy';
                        $filter_term_key = $taxonomy;
                    } else {
                        // else if third paramater is defined as 'slug' then escape; which don't need term_id because it will perform meta query on accepted value
                        $query_by_slug = isset($metadata_reference[2]) ? $metadata_reference[2] : '';
                        if ($query_by_slug != 'slug') {
                            // else find the taxonomy and get term id according to search_field_type
                            if ($search_field_type == 'checkbox') {
                                if ($_GET_field_name && is_array($_GET_field_name)) {
                                    $_GET_field_name_temp = [];
                                    foreach ($_GET_field_name as $key => $_GET_term_slug) {
                                        $current_term = get_term_by('slug', $_GET_term_slug, $taxonomy);
                                        if ($current_term) {
                                            $_GET_field_name_temp[] = $current_term->term_id;
                                        }
                                    }
                                    $_GET_field_name = $_GET_field_name_temp;
                                    unset($_GET_field_name_temp);
                                }
                            }
                            if ($search_field_type == 'dropdown') {
                                $current_term = get_term_by('slug', $_GET_field_name, $taxonomy);
                                if ($current_term) {
                                    $_GET_field_name = $current_term->term_id;
                                }
                            }
                        }
                    }
                }
            }
            // taxonomy field data filter_term_type
            if ($filter_term_type == 'taxonomy') {
                if ($search_field_type == 'checkbox') {
                    if ($_GET_field_name && is_array($_GET_field_name)) {
                        foreach ($_GET_field_name as $key => $_GET_term_slug) {
                            $tax_query[] = $this->tax_filter_query($filter_term_key, $_GET_term_slug);
                        }
                    }
                }
                if ($search_field_type == 'dropdown') {
                    if ($_GET_field_name) {
                        $tax_query[] = $this->tax_filter_query($filter_term_key, $_GET_field_name);
                    }
                }
            }
            // metadata field data filter_term_type
            if ($filter_term_type === 'metadata') {
                if ($search_field_type == 'checkbox') {
                    if ($_GET_field_name && is_array($_GET_field_name)) {
                        foreach ($_GET_field_name as $key => $_GET_meta_value) {
                            $meta_query[] = $this->meta_filter_query($filter_term_key, $_GET_meta_value);
                        }
                    }
                }
                if ($search_field_type == 'dropdown') {
                    if ($_GET_field_name) {
                        $meta_query[] = $this->meta_filter_query($filter_term_key, $_GET_field_name);
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
    public function meta_filter_query($meta_key, $meta_value, $compare = 'LIKE')
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

    // SF_Query class end
}
