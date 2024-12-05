<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Version: 1.0
 * =======================================
 */

namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Here we define or set the search filter fields/settings
class CSF_Fields
{

    // 
    public static function set_search_fields()
    {
        $csf_set_search_fields = (get_option('csf_set_search_fields')) ?: '';
        if ($csf_set_search_fields) {
            return json_decode($csf_set_search_fields, true);
        }

        return self::set_search_fields_default();
    }

     /**
     * 
     * Each Filter must have unique_filter_name and the options
     * unique_filter_name => ""; Unique filter name. :: REQUIRED
     * ----------------------------------------------------------------
     * Each "filter_name" values has following options $fields['unique_filter_name']
     * ----------------------------------------------------------------
     * is_main_query=>true or false; 
     * post_type=>"post"; //REQUIRED;  post type to filter
     * taxonomies=> ""; // seperate the multiple taxonomy by (,) comma
     * posts_per_page=>"12"; post per page in post wq query result page
     * field_relation=>"AND" or "OR" 
     * search_filter_title => ""; Search filter title in the search form
     * display_count => 1 or 0; OPTIONAL; default 0
     * result_filter_area => ''; // OPTIONAL; html id where result template is shown 
     * dynamic_filter_item=> true or false; ; OPTIONAL; default false; // To change/load filter form items on each form submit according to result or not.
     * result_template=>'archive/filter/post_name.php';OPTIONAL :: define the template file path for the current active theme
     * fields => []; // filter fields lists and its fields values as defined below.
     * fields_actions =>[] // Search filter action like auto submit, submit and reset button
     * free_search=>[] // define the meta_key and taxonomy to accept free text search; free_search will only work with field_relation="OR"
     * ------------------------------------------------------------------
     * Each filter "fields" values has following options $fields['unique_filter_name']['fields']
     * ------------------------------------------------------------------
     * display_name=>'Display name'
     * filter_term_type => 'taxonomy' or 'metadata'
     * filter_term_key => 'taxonomy_key' or 'metadata_key'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
     * metadata_reference => 'past_upcoming_date_compare','taxonomy,taxonomy_key,slug' or 'post' or 'function-name-as-defined'; only apply to filter_term_key metadata_key Where 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value .
     * search_field_type => 'dropdown' or 'checkbox' or 'search_text'; there can only be one 'search_text' on each filter
     * placeholder => 'free text' ;only apply to search_field_type search_text
     * filter_items=> [['slug'=>'slug','name'=>'name'], ['slug'=>'slug','name'=>'name']]; If this is defined, it will replace the filter items.
     * ------------------------------------------------------------------
     * Each filter "fields_actions" values has following options $fields['unique_filter_name']['fields_actions']
     * ------------------------------------------------------------------
     * auto_submit => true or false;
     * submit_btn_show => true or false;
     * submit_display_name => "Search"; // submit btn label
     * reset_btn_show => true or false;
     * reset_display_name => "Reset"; // Reset btn label
     * 
     */
    //search_fields
    public static function set_search_fields_default()
    {
        // initially define the fields

        $fields = array_merge(
            self::resource_filter_fields(),
            self::application_tool_filter_fields(),
            self::event_filter_fields(),

        );

        // return field settings
        return $fields;
    }

    /**
     * 
     */
    protected static function resource_filter_fields()
    {
        // For resource post type search filter
        $filter_fields = [];
        $filter_fields_name = 'resource'; //filter name should be post type to query and filter by main wp query 
        $filter_fields['post_type']  = $filter_fields_name; // post type to filter
        // $filter_fields['taxonomies']  = 'resource-rights'; // seperate the multiple taxonomy by (,) comma
        $filter_fields['is_main_query'] = true; // It may overried my others plugin or functions
        $filter_fields['posts_per_page'] = 24; // post per page in post wq query result page
        $filter_fields['search_filter_title'] = 'Filters'; // Search filter title in the search form
        $filter_fields['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search by keyword'],
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'filter_term_key' => 'sector', 'metadata_reference' => 'taxonomy,resource-sector', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Commodity', 'filter_term_type' => 'metadata', 'filter_term_key' => 'commodity_type', 'metadata_reference' => 'taxonomy,commodity-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Country', 'filter_term_type' => 'metadata', 'filter_term_key' => 'country_only', 'metadata_reference' => 'get_plghub_cache_all_unsd_countries', 'search_field_type' => 'checkbox'],
            // ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            //  search in both or more then one metadata is possible only in extera function search is developed; not in wp query.
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_only|region_unsd_region_only', 'metadata_reference' => 'taxonomy,png-region,slug|get_plghub_cache_all_unsd_region', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Topic', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,topic', 'filter_term_key' => 'topic_sub_topic_list_{array}_topic_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Project', 'filter_term_type' => 'metadata', 'metadata_reference' => 'post', 'filter_term_key' => 'project_name', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Resource type', 'filter_term_type' => 'metadata', 'filter_term_key' => 'resource_type', 'metadata_reference' => 'taxonomy,resource-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Organisation', 'filter_term_type' => 'metadata', 'filter_term_key' => 'organisation', 'metadata_reference' => 'taxonomy,organisation', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Publisher', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publisher', 'metadata_reference' => 'taxonomy,publisher', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Year', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publication_year_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Format', 'filter_term_type' => 'metadata', 'filter_term_key' => 'format', 'metadata_reference' => 'taxonomy,format', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Rights', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-rights', 'filter_term_key' => 'rights_license_type_rights_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Access', 'filter_term_type' => 'metadata', 'filter_term_key' => 'access', 'search_field_type' => 'checkbox'],
        ];
        $filter_fields['free_search']['meta_keys'] = ['description', 'publication_year_only']; //applied only in "OR" relation
        // $filter_fields['free_search']['post_taxonomies'] = ['keywords-tag', 'resource-type', 'commodity-type', 'resource-sector', 'format'];
        $filter_fields['field_relation'] = "AND";
        // $filter_fields['result_template'] = "";
        $filter_fields['dynamic_filter_item'] = true;
        $filter_fields['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];

        // return field settings
        return [
            $filter_fields_name => $filter_fields
        ];
    }

    /**
     * 
     */
    protected static function application_tool_filter_fields()
    {

        // Tools archive page Filter
        $filter_fields = [];
        $filter_fields_name = 'application-tool'; //filter name should be post type to query and filter by main wp query 
        $filter_fields['post_type']  = $filter_fields_name; // post type to filter
        // $filter_fields['taxonomies']  = 'tools-country'; // seperate the multiple taxonomy by (,) comma
        $filter_fields['is_main_query'] = true;
        $filter_fields['posts_per_page'] = 24; // post per page in post wq query result page
        $filter_fields['search_filter_title'] = ''; // Search filter title in the search form
        $filter_fields['field_relation'] = "AND";
        $filter_fields['dynamic_filter_item'] = true;
        $filter_fields['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        $filter_fields['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search by keyword'],
            ['display_name' => 'Service Area', 'filter_term_type' => 'metadata', 'filter_term_key' => 'related_service_area', 'metadata_reference' => 'post', 'search_field_type' => 'checkbox'],
            // ['display_name' => 'Year', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publication_year_only', 'search_field_type' => 'checkbox'],
            ['display_name' => ' Geographical Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'tools_country', 'metadata_reference' => 'taxonomy,tools-country', 'search_field_type' => 'checkbox',],
        ];
        // $filter_fields['free_search']['meta_keys'] = ['description', 'publication_year_only']; //applied only in "OR" relation
        // $filter_fields['free_search']['post_taxonomies'] = ['tools-country'];


        // return field settings
        $fields[$filter_fields_name]  = $filter_fields;
        return $fields;
    }

    /**
     * 
     */
    protected static function event_filter_fields()
    {
        // Update post archive page Filter
        $filter_fields = [];
        $filter_fields_name = 'event'; //filter name should be post type to query and filter by main wp query 
        $filter_fields['post_type']  = $filter_fields_name; // post type to filter
        $filter_fields['is_main_query'] = true;
        $filter_fields['posts_per_page'] = 4; // post per page in post wq query result page
        $filter_fields['search_filter_title'] = ''; // Search filter title in the search form
        $filter_fields['field_relation'] = "OR";
        $filter_fields['display_count'] = 0;
        $filter_fields['result_filter_area'] = ''; // section html id
        $filter_fields['dynamic_filter_item'] = true;
        $filter_fields['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        $filter_items_date =
            [
                [
                    'slug' => 'upcoming',
                    'name' => 'Upcoming Events'
                ],
                [
                    'slug' => 'past',
                    'name' => 'Past Events'
                ],

            ];

        $filter_fields['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search by keyword'],
            ['display_name' => 'Service Area', 'filter_term_type' => 'metadata', 'filter_term_key' => 'related_service_area', 'metadata_reference' => 'post', 'search_field_type' => 'checkbox'],

            ['display_name' => 'Event Type', 'filter_term_type' => 'metadata', 'filter_items' => $filter_items_date, 'filter_term_key' => 'end_date_and_time', 'metadata_reference' => 'past_upcoming_date_compare', 'search_field_type' => 'checkbox'],

        ];
        return [
            $filter_fields_name => $filter_fields
        ];
    }

    // End
}
