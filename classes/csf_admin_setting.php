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

// class to handle Admin_setting
class CSF_Admin_setting
{

    public static $page_slug = 'search-filter-csf';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'this_plugin_settings_submenu']);
        add_action('admin_init', [$this, 'csf_settings_init']);
    }


    // Register the submenu page
    public function this_plugin_settings_submenu()
    {
        add_options_page(
            'CSF - Search Filter', // Page title
            'CSF - Search Filter', // Menu title
            'manage_options',     // Capability required to see the menu
            self::$page_slug, // Menu slug
            [$this, 'csf_setting_page_callback'] // Function to display the page content
        );
    }


    // Register and define the settings
    function csf_settings_init()
    {
        register_setting('search-filter-csf-setting', 'enable_csf_cache_meta');
        register_setting('search-filter-csf-setting', 'reset_csf_cache_meta');
        register_setting('search-filter-csf-setting', 'csf_set_search_fields');
        register_setting('search-filter-csf-setting', 'csf_cache_metadata_fields');


        $section_id = 'settigs_fields_section';
        // Register a new section in the "search-filter-csf" page
        add_settings_section(
            $section_id, // Section ID
            '', //'Post Title Required : General Setting', // Title of the section
            [$this, 'settings_section_callback'], // Callback function to render the section description
            self::$page_slug // Page slug
        );
        // Register a new field in the "settigs_fields_section" section
        add_settings_field(
            'csf_set_search_fields',
            'CSF Search Fields',
            [$this, 'csf_set_search_fields_callback'],
            self::$page_slug,
            $section_id
        );
        // 
    }

    // Callback function to display the content of the submenu page
    public function csf_setting_page_callback()
    {
?>
        <div class="wrap">
            <h1>CSF - Search Filter</h1>
            <form method="post" action="options.php">
                <?php
                // Output security fields for the registered setting
                settings_fields('search-filter-csf-setting');
                // Output setting sections and their fields
                do_settings_sections('search-filter-csf');
                // Output save settings button
                submit_button();
                ?>
            </form>
        </div>
        <!-- Script to initialize Ace Editor -->
        <style>
            #csf_set_search_fields_editor,
            #csf_cache_metadata_fields_editor {
                width: 100%;
                height: 400px;
            }

            .ace_print-margin {
                left: 0px !important;
            }
        </style>
    <?php
        \csf_search_filter\CSF_Enqueue::csf_admin_setting_js();
    }

    // Callback function to render the section description
    function settings_section_callback()
    {
        echo '';
    }


    // 
    public function csf_set_search_fields_callback()
    {
        $value = (get_option('csf_set_search_fields')) ?: '';
        $close_icon = csf_dir . 'assets/icon/close.svg';
        $close_icon_url = str_replace(rtrim(ABSPATH, '/'), home_url(), $close_icon);

    ?>
        <textarea id="csf_set_search_fields" name="csf_set_search_fields" style="display: none;"><?php echo esc_attr($value); ?></textarea>
        <div class="info" style="margin-bottom: 7px;">
            <button type="button" id="csf_set_search_fields_format">Format Code</button>
            <button type="button" class="btn btn-primary" data-action="csf_set_search_fields_default"> Set Default Value</button>
            <button type="button" class="help_btn" help-info-id="csf_search_fields_help_desc">
                Set Search Field Help
                <img src="<?php echo esc_attr($close_icon_url); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
            <button type="button" class="help_btn" help-info-id="csf_form_display_help_desc">
                Form Display Help
                <img src="<?php echo esc_attr($close_icon_url); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
            <button type="button" class="help_btn" help-info-id="csf_result_display_help_desc">
                CSF Result Display Help
                <img src="<?php echo esc_attr($close_icon_url); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
        </div>
        <div style="margin-bottom: 7px;">
            <div id="csf_search_fields_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Search Fields JSON format fields settings are as defined:</h4>
                <pre>csf_search_filter = {
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
                    }</pre>
                <ol>
                    <li>
                        csf_search_filter['unique_filter_name'] = Unique filter name. :: REQUIRED
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['is_main_query'] = true or false, this is to make the wp main query in archive or taxonomy page. This may be overried if other search is enable
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['post_type'] = post type to filter :: REQUIRED
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['taxonomies'] = taxonomy_slug ::define taxonomy page where, seperate the multiple taxonomy by (,) comma
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['posts_per_page'] = post per page in post wq query result page
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['search_filter_title'] = Search filter title in the search form
                    </li>
                    <li> csf_search_filter['unique_filter_name']['display_count'] => 1 or 0; OPTIONAL; default 0</li>
                    <li>
                        csf_search_filter['unique_filter_name']['result_filter_area'] => ""; OPTIONAL; html section id, where the result is shown.
                    </li>
                    <li>csf_search_filter['unique_filter_name']['field_relation'] = "OR / AND"; default "OR"; OPTIONAL
                    </li>
                    <li>csf_search_filter['unique_filter_name']['result_template'] => 'archive/filter/post_name.php';OPTIONAL :: define the template file path for the current active theme;</li>
                    <li> csf_search_filter['unique_filter_name']['dynamic_filter_item'] = true or false; ; OPTIONAL; default false; // To change/load filter form items on each form submit according to result or not.</li>
                    <li>
                        csf_search_filter['unique_filter_name']['fields'] = Each filter fields values has following options
                        <ol>
                            <li>display_name=>'Display name'</li>
                            <li>filter_term_type => 'taxonomy' or 'metadata'</li>
                            <li>
                                filter_term_key => 'taxonomy_key' or 'metadata_key' or 'metadata_key_1|metadata_key_2'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
                                <br>
                                Also define multiple key by seperating with "|", under same display name
                            </li>
                            <li>
                                metadata_reference => 'past_upcoming_date_compare','taxonomy,taxonomy_key,slug' or 'post' or 'function-name-as-defined' or 'taxonomy,taxonomy_key,slug|post' ; only apply to filter_term_key = 'metadata_key' Where ON 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value .
                                <br>
                                Also define multiple key by seperating with "|", under same display name
                            </li>
                            <li>search_field_type => 'dropdown' or 'checkbox' or 'search_text'; default dropdown; there can only be one 'search_text' on each filter</li>
                            <li>placeholder => 'free text' ;only apply to search_field_type search_text</li>
                            <li>filter_items => [['slug'=>'slug','name'=>'name'], ['slug'=>'slug','name'=>'name']]; OPTIONA; If this is defined, it will replace the filter items. </li>
                        </ol>
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['fields_actions'] =[] :: Search filter action like auto submit, submit and reset button
                        <ol>
                            <li>
                                ['unique_filter_name']['fields_actions']['auto_submit']=true or false;
                            </li>
                            <li>
                                ['unique_filter_name']['fields_actions']['submit_btn_show']=true or false;
                            </li>
                            <li>
                                ['unique_filter_name']['fields_actions']['submit_display_name']= "Search"; // submit btn label
                            </li>
                            <li>
                                ['unique_filter_name']['fields_actions']['reset_btn_show']=true or false;
                            </li>
                            <li>
                                ['unique_filter_name']['fields_actions']['reset_display_name']="Reset"; // Reset btn label
                            </li>
                        </ol>
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['free_search'] = define the meta_key and taxonomy to accept free text search; free_search will only work with field_relation="OR"
                    </li>

                </ol>
            </div>
            <div id="csf_form_display_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Form Display Setting</h4>
                <pre>
    $search_form = [
        filter_name => "unique_filter_name",
        post_type =>  "post_type"
    ];
    \csf_search_filter\CSF_Form::the_search_filter_form($search_form);

    OR 

    echo do_shortcode('[csf_searchfilter filter_name="unique_filter_name" post_type = "post_type" ]');

                    </pre>
                <ol>
                    <li> $search_form['filter_name'] = 'unique_filter_name'; default current_post_type</li>
                    <li> $search_form['form_class'] = 'form_id'; default 'search-filter-form' </li>
                    <li> $search_form['post_type'] 'post_type'; default current_post_type</li>
                    <li> $search_form['data_url'] = 'data_action_url'; default current_post_archive_url </li>
                </ol>
            </div>
            <div id="csf_result_display_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Filter Result Display Setting</h4>
                <pre>
        echo do_shortcode('[csf_searchfilter filter_name="unique_filter_name" result_show="true"]');  
        OR  
        &lt;div id="csf-result-area-filter_name" &gt; -- loop content -- &lt;/div&gt; 
                </pre>
                <p>
                    Use shortcode <br> OR <br>
                    CSF Search Filter Result area must be wrap by the id = "csf-result-area-filter_name" inorder to display/replace the result by ajax. Here filter_name should be replace.
                    <br>
                    For the result template csf query result is stored in variable $csf_query.
                </p>
            </div>
        </div>
        <div id="csf_set_search_fields_editor"><?php echo esc_attr(($value) ? $value : ''); ?></div>
<?php
    }

    // 
}
