<?php

/**
 * custom search filter result default template
 */
$result_area_id = isset($settings['search_filter_result_area_id']) ? $settings['search_filter_result_area_id'] : 'csf-filter-result-area';
$apply_only_on_show_result = (isset($settings['apply_only_on_show_result'])) ? $settings['apply_only_on_show_result'] : '';
if (!$apply_only_on_show_result || empty($apply_only_on_show_result)) {
    global $wp_query;
    if (! isset($wp_query)) {
        return false;
    }
    $csf_query = $wp_query;
    var_dump('apply_only_on_show_result faaaaaaaaa');
} else {
    var_dump('apply_only_on_show_result trrrrrrrrrrr');
    $csf_query = new WP_Query(['post_type' => $settings['filter_setting_post_type']]);
    $search_filter_query = new \custom_search_filter\search_filter_query();
    $csf_query = $search_filter_query->csf_query($settings, $csf_query, true);
}
// var_dump($csf_query);


if ($csf_query->have_posts()) {
?>
    <div class="post-card-wrapper">
        <?php
        while ($csf_query->have_posts()) {
            $csf_query->the_post();
            $id = get_the_ID();
        ?>
            <div class="post-card">
                <div class="title">
                    <a href="<?php echo get_permalink($id); ?>"><?php echo get_the_title($id); ?></a>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
<?php
} else {
?>
    <div>
        <p>No content found at the moment, please try to search with other keyword. </p>
    </div>
<?php
}
echo paginate_links();
?>