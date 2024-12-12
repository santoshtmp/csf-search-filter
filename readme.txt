=== CSF - Search Filter ===
Tags: CSF, form, query
Author: santoshtmp
Author URI: https://github.com/santoshtmp
Plugin URI: https://github.com/santoshtmp/csf-search-filter
Requires WP: 6.5
Requires at least: 6.5
Tested up to: 6.6.1
Requires PHP: 8.0
Domain Path: languages
Text Domain: csf-search-filter
Stable tag: 1.0
Version: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

To make search filter form and query easy.

== Description ==
CSF - Search Filter library plugin purpose to make search filter easier for admin and developer by providing form and query it.

== Screenshots ==
1. csf-admin-setting-page.png

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/csf-search-filter` directory, or install the plugin through the WordPress plugins screen directly.
or
Use as theme functions after integration it must be build.
By require_once dirname(__FILE__) . '/csf-search-filter/csf-search-filter.php'; in theme function.php file.

2. Navigate to the settings > WP Required Post Title or link `/wp-admin/options-general.php?page=search-filter-csf` to the pugin setting page

== Frequently Asked Questions ==
= Can we apply to particular post type? =
Yes, we can apply to only selected post type.

== Release ==
1. Initial release 1.0.0

== Changelog ==
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
Initial release.

== License ==
This plugin is licensed under the GPLv2 or later. For more information, see http://www.gnu.org/licenses/gpl-2.0.html.


## You can find the description in wp-admin csf-search-filter setting page as below.
![ wp-admin csf-search-filter setting page](./assets/csf-admin-setting-page.png)

## ===== Set Search Form Field =====
CSF Search Fields JSON format fields settings are as defined
    csf_search_filter = {
        "unique_filter_name":{
            "is_main_query": true,
            "post_type":"post_type",
            "taxonomies": "taxonomy_slug",
            "posts_per_page":12,
            "search_filter_title":"Text Title",
            "display_count":1
            "result_filter_area":"",
            "field_relation":"OR",
            "result_template":"",
            "dynamic_filter_item":true,
            "default_asc_desc_sort_by":{
                "order": "DESC",
                "orderby": "date"
            }
            "fields":[
                {
                    "display_name": "Region",
                    "filter_term_type": "metadata",
                    "filter_term_key": "region_png_region_only",
                    "metadata_reference": "taxonomy,png-region,slug",
                    "search_field_type": "checkbox",
                    "placeholder":"",
                    "filter_items":[[]]
                }
            ],
            "fields_actions": {
                "auto_submit": true,
                "submit_btn_show": true,
                "submit_display_name": "Search",
                "reset_btn_show": true,
                "reset_display_name": "Reset"
            }
            "free_search": {
                "meta_keys": [
                    "meta_key"
                ],
                "post_taxonomies": [
                    "taxonomy"
                ]
            },
        }   
    }
                    
/**
*
* Each Filter must have unique_filter_name and the options
* unique_filter_name => ""; Unique filter name. :: REQUIRED
* ----------------------------------------------------------------
* Each "filter_name" values has following options $fields['unique_filter_name']
* ----------------------------------------------------------------
* is_main_query=>true or false                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  ;
* post_type=>"post"; //REQUIRED                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 ;  post type to filter
* taxonomies=> ""; // seperate the multiple taxonomy by (,) comma
* posts_per_page=>"12"; post per page in post wq query result page
* field_relation=>"AND" or "OR"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 ; Default "AND"
* search_filter_title => ""; Search filter title in the search form
* display_count => 1 or 0                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       ; OPTIONAL; default 0
* result_filter_area => ''; // OPTIONAL                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         ; html id where result template is shown
* dynamic_filter_item=> true or false                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ; ; OPTIONAL; default false; // To change/load filter form items on each form submit according to result or not.
* default_asc_desc_sort_by = [ "order"=>"ASC", "orderby"=>"", "meta_key"=>""]; OPTIONAL
* result_template=>'archive/filter/post_name.php';OPTIONAL :: define the template file path for the current active theme
* fields => []                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  ; // filter fields lists and its fields values as defined below.
* fields_actions =>[] // Search filter action like auto submit, submit and reset button
* free_search=>[] // define the meta_key and taxonomy to accept free text search                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ; free_search will only work with field_relation="OR"
* ------------------------------------------------------------------
* Each filter "fields" values has following options $fields['unique_filter_name']['fields']
* ------------------------------------------------------------------
* display_name=>'Display name'
* filter_term_type => 'taxonomy' or 'metadata'
* filter_term_key => 'taxonomy_key' or 'metadata_key'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
* metadata_reference => 'asc_desc_sort_by,meta_key', 'past_upcoming_date_compare', 'taxonomy,taxonomy_key,slug' or 'post' or 'function-name-as-defined'; This reference only apply to filter_term_key = metadata_key, For 'asc_desc_sort_by,meta_key' filter_items must be provided with slug 'ASC' and 'DESC' also it can be used only once in one form, meta_key is the custom_meta_key and filter_term_key is orderby value., For 'past_upcoming_date_compare' filter_items must be provided with slug 'past' and 'upcoming' ,For 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value, For 'post' it will give post name where metadata_key must return post id.
* search_field_type => 'dropdown' or 'checkbox' or 'search_text' or 'radio'; there can only be one 'search_text' on each filter
* placeholder => 'free text'                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ;only apply to search_field_type search_text
* filter_items=> [['slug'=>'slug','name'=>'name'], ['slug'=>'slug','name'=>'name']]; If this is defined, it will replace the filter items.
* ------------------------------------------------------------------
* Each filter "fields_actions" values has following options $fields['unique_filter_name']['fields_actions']
* ------------------------------------------------------------------
* auto_submit => true or false                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  ;
* submit_btn_show => true or false                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              ;
* submit_display_name => "Search"; // submit btn label
* reset_btn_show => true or false                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               ;
* reset_display_name => "Reset"; // Reset btn label
*
*/


## ===== CSF Form Display Setting =====
$search_form = [
    filter_name => "unique_filter_name",
    post_type =>  "post_type",
    form_class => "",
    data_url => "",
    all_post_ids => []
];
\csf_search_filter\CSF_Form::the_search_filter_form($search_form);
OR 
echo do_shortcode('[csf_searchfilter filter_name="unique_filter_name" post_type = "post_type" ]');
