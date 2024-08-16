<?php



namespace custom_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class shortcode_customsearchfilter
{
    public function __construct()
    {
        add_shortcode('customsearchfilter', [$this, 'display_shortcode_customsearchfilter']);
    }


    public function display_shortcode_customsearchfilter($atts)
    {
        // Define default attributes
        $atts = shortcode_atts(
            array(
                'id' => 0,
                'result_show' => "false",
            ),
            $atts
        );
        $search_filter_id = (int)$atts['id'];
        if (!$search_filter_id) {
            return "customsearchfilter id is required";
        }
        if (!get_post($search_filter_id)) {
            return "customsearchfilter id is invalid";
        }
        // get custom_search-filter-settings for the given id
        $settings = \custom_search_filter\Helper::get_settings_meta($search_filter_id);

        if (!$settings) {
            echo "Filter setting is not defined";
            return "";
        }
        $fiter_title = get_the_title($search_filter_id);
        $post_type = ($settings['filter_setting_post_type']) ?: get_post_type();
        $fields_free_text_input = ($settings['fields_free_text_input']) ?: 0;
        $auto_submit = ($settings['auto_submit']) ?: 0;
        $reset_submit_btn = ($settings['reset_submit_btn']) ?: 0;
        $form_class = ($settings['search_filter_css_class']) ?: '';
        $fields =   ($settings['fields']) ?: [];
        $data_url =  home_url($post_type);
        $form_id = 'csf-' . $search_filter_id;
        $result_area_id = 'csf-filter-result-area-' . $search_filter_id;

        // show filter result
        $result_show = $atts['result_show'];
        if ($result_show === 'true') {
            $this->display_shortcode_customsearchfilter_result($result_area_id, $settings);
            return '';
        }
        // filter js
        if ($auto_submit) {
            $this->csf_srach_js($form_id, $result_area_id);
        }
        // Generate the form shortcode output
        ob_start();
?>
        <div class="csf-form-wrapper">
            <div class='csf-title'><?php echo $fiter_title; ?></div>
            <form id="<?php echo esc_html($form_id); ?>" action="" method="GET" class=' <?php echo esc_html($form_class); ?> ' data-url=<?php echo $data_url; ?> role="search">
                <?php
                // free text search box
                if ($fields_free_text_input) {
                    $search = isset($_GET['csf_search']) ? $_GET['csf_search'] : '';
                ?>
                    <div class="filter-block search-box" filter-action="free-search">
                        <input type="text" id="search_input" class='search-input' name="csf_search" value="<?php echo $search; ?>" placeholder="Search with title or keyword.">
                    </div>
                <?php
                }

                // fields
                foreach ($fields as $key => $field) {
                    $filter_title = $field['display_name'];
                    $field_name = \custom_search_filter\Helper::get_csf_field_name($filter_title);
                    $show_count = isset($field['display_count']) ? 1 : 0;
                    $filter_items = [];
                    // check search_field_data and define filter items
                    $search_field_data = $field['search_field_data'];
                    if ($search_field_data === 'taxonomy') {
                        $taxo_slug_key = $field['search_field_taxonomy'];
                        $filter_items = get_terms(['taxonomy' => $taxo_slug_key, 'hide_empty' => true]);
                    }
                    if ($search_field_data === 'metadata') {
                        $meta_key = $field['search_field_metadata'];
                        $filter_items = \custom_search_filter\Helper::get_unique_post_meta_count_info($post_type, $meta_key);
                    }
                    // check search_field_type
                    $search_field_type = $field['search_field_type'];
                    if ($search_field_type == 'dropdown') {
                        $this->dropdown_field($filter_title, $field_name, $filter_items);
                    }
                    if ($search_field_type == 'checkbox') {
                        $this->checkbox_field($filter_title, $field_name, $filter_items, $show_count);
                    }
                }

                ?>
                <div class="btn-wrapper">
                    <?php
                    if (!$auto_submit) {
                    ?>
                        <div class="filter-search-btn ">
                            <input type="submit" value="Search">
                        </div>
                    <?php
                    }
                    if ($reset_submit_btn) {
                    ?>
                        <div class="filter-reset-btn ">
                            <a href="<?php echo $data_url; ?>">
                                <?php echo __('Reset'); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

            </form>
        </div>
    <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    // 
    public function csf_srach_js($form_id, $result_area_id = 'csf-filter-result-area')
    {
        $js_file_path = csf_path . 'assets/js/custom-search-filter.js';
        wp_enqueue_script(
            'csf-filter',
            $js_file_path,
            array('jquery'),
            filemtime(get_stylesheet_directory($js_file_path)),
            array(
                'in_footer' => true,
                'strategy' => 'defer'
            )
        );
        wp_localize_script('csf-filter', 'csf_obj', [
            'form_id' => $form_id,
            'result_area_id' => $result_area_id
        ]);
    }



    // 
    public function dropdown_field($filter_title, $name, $each_taxonomy_term)
    {
        $select_dropdown = isset($_GET[$name]) ? $_GET[$name] : '';
    ?>
        <div class="filter-block <?php echo ($select_dropdown) ? 'active' : ''; ?>">
            <select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
                <option value="">
                    <?php echo $filter_title; ?>
                </option>
                <?php
                foreach ($each_taxonomy_term as $each_taxo_key => $value) {
                    $value = (array) $value;
                    $search_drop_select = '';
                    if (isset($value['slug'])) {
                        $search_drop_select = ($select_dropdown == $value['slug']) ? 'selected' : '';
                    }
                ?>
                    <option value="<?php echo (isset($value['slug'])) ? $value['slug'] : ''; ?>" <?php echo  $search_drop_select; ?>>
                        <span class="label-name"> <?php echo (isset($value['name'])) ? $value['name'] : ''; ?> </span>
                        <span class="label-count">(<?php echo (isset($value['count'])) ? $value['count'] : ''; ?>)</span>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
    <?php
    }

    // 
    public function checkbox_field($filter_title, $field_name, $filter_items, $show_count = 0)
    {
        $select_checkbox = isset($_GET[$field_name]) ? $_GET[$field_name] : [];
        $filter_active_class = ($select_checkbox) ? 'active' : '';
    ?>

        <div class="filter-block <?php echo $filter_active_class; ?>">
            <div class="filter-title ">
                <span class='title'>
                    <?php echo $filter_title; ?>
                </span>
            </div>
            <div class="filter-item-list ">
                <div class="item-list-wrapper">
                    <?php
                    foreach ($filter_items as $each_taxo_key => $value) {
                        $value = (array) $value;
                        $checkbox_checked = '';
                        if (is_array($select_checkbox) && in_array($value['slug'], $select_checkbox)) {
                            $checkbox_checked = 'checked';
                        }
                    ?>
                        <div class="filter-item">
                            <div class="checkbox-input-wrapper ">
                                <div class='filter-input-wrapper'>
                                    <input type="checkbox" name="<?php echo $field_name . '[]'; ?>" id="<?php echo $field_name . '-' . $value['slug']; ?>" value="<?php echo $value['slug']; ?>" <?php echo  $checkbox_checked; ?>>
                                    <span class='checkmark'></span>
                                </div>
                                <label class='filter-item-label' for="<?php echo $field_name . '-' . $value['slug']; ?>">
                                    <span class="label-name"> <?php echo $value['name']; ?> </span>
                                    <?php
                                    if ($show_count) {
                                    ?>
                                        <span class="label-count">(<?php echo $value['count']; ?>)</span>
                                    <?php
                                    }
                                    ?>
                                </label>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
<?php
    }

    // 
    public function display_shortcode_customsearchfilter_result($result_area_id, $settings)
    {
        $template_path = isset($settings['search_filter_result_template']) ? $settings['search_filter_result_template'] : '';
        if (empty($template_path) || $template_path == '') {
            $template_path = csf_dir . 'includes/csf-result-template.php';
        } else {
            $template_path = get_template_directory() . '/' . $template_path;
        }
        if ($template_path) {
            $template_path = ltrim($template_path, '/');
            echo '<div class="csf-search-result" data-region="csf-search-filter-result"> ';
            echo '<div id="' . $result_area_id . '"> ';
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                $load_template = get_template_part($template_path);
                if ($load_template === false) {
                    echo "invalid search filter result template.";
                }
            }
            echo "</div> ";
            echo "</div> ";
        }
    }

    // 
}


$shortcode_customsearchfilter = new shortcode_customsearchfilter();
