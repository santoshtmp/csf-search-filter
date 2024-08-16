<?php


// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	exit;
}
global $post;
//define default settingd
$settings = [];
$settings['filter_setting_post_type'] = '';
$settings['posts_per_page'] = 12;
$settings['auto_submit'] = 1;
$settings['reset_submit_btn'] = 0;
// $settings['main_query'] = 1;
$settings['field_relation'] = 'AND';
$settings['fields_free_text_input'] = 1;
$settings['free-search-content'] = 0;

$default_field = [
	'search_field_data' => 'taxonomy',
	'search_field_taxonomy' => '',
	'search_field_metadata' => '',
	'search_field_type' => 'dropdown',
	'display_count' => 1,
	'display_name' => ''
];
$settings['fields'] = [$default_field];
$settings['apply_only_on_show_result'] = 0;
$settings['search_filter_result_template'] = '';
$settings['search_filter_css_class'] = '';

// get all post types
$post_types = get_post_types(['public'   => true], 'objects');
unset($post_types['attachment']);

// get the saved setting for this search filter
$meta_value = get_post_meta(get_the_ID(), $this->meta_key, true);
if ($meta_value) {
	// $settings = $meta_value;
	foreach ($meta_value as $key => $value) {
		$settings[$key] = $value;
	}
}
// set nonce for the setting
wp_nonce_field('search_filter_nonce', $this->slug . '_nonce', true, true);
?>

<div id="settings-defaults" class="widgets-search-filter-draggables ui-search-filter-sortable setup" data-allow-expand="0">

	<p class="description"><?php _e("Settings &amp; Default Conditions for this Search Form.", $this->slug); ?></p>

	<div class="tabs-container">

		<div class="sf_field_data sf_tab_content_settings" style="display: block;">

			<!-- Post Types -->
			<table>
				<tr>
					<td width="275">
						<p><strong><?php _e("Search in the Post type:", $this->slug); ?></strong></p>
					</td>
					<td>
						<div class="csf_post_types">
							<p>
								<select name="filter_setting_post_type" id="filter_setting_post_type" required>
									<option value="">Select Post Type</option>
									<?php
									foreach ($post_types as $post_type) {
										$selected = "";
										if ($settings['filter_setting_post_type'] === $post_type->name) {
											$selected = "selected";
										}
									?>
										<option value="<?php echo $post_type->name; ?>" <?php echo $selected; ?>>
											<?php _e($post_type->labels->name, $this->slug); ?>
										</option>
									<?php

									}
									?>
								</select>
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="posts_per_page"><?php _e("Results per page:", $this->slug); ?></label>
					</td>
					<td>
						<input class="" id="posts_per_page" name="posts_per_page" type="number" min="-1" size="2" value="<?php echo esc_attr($settings['posts_per_page']); ?>" required>
					</td>
				</tr>
				<!-- <tr>
					<td style="vertical-align: top;">
						<label for="main_query"><?php _e("Query on main post type?", $this->slug); ?><span class="hint--top hint--info" data-hint="<?php _e("Update the results whenever a user changes a value - no need for a submit button", $this->slug); ?>"></span></label>
					</td>
					<td>
						<input class="checkbox main_query" type="checkbox" id="main_query" name="main_query" value="1" <?php $this->set_checked($settings['main_query']); ?>>
						<p class="description"><?php _e("This will query and filter on main post type else only in show_result", $this->slug); ?></p>
					</td>
				</tr> -->

				<tr>
					<td style="vertical-align: top;">
						<label for="auto_submit"><?php _e("Auto submit form?", $this->slug); ?><span class="hint--top hint--info" data-hint="<?php _e("Update the results whenever a user changes a value - no need for a submit button", $this->slug); ?>"></span></label>
					</td>
					<td>
						<input class="checkbox auto_submit" type="checkbox" id="auto_submit" name="auto_submit" value="1" <?php $this->set_checked($settings['auto_submit']); ?>>
					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="reset_submit_btn"><?php _e("Show Re-set form button?", $this->slug); ?></label>
					</td>
					<td>
						<input class="checkbox reset_submit_btn" type="checkbox" id="reset_submit_btn" value="1" name="reset_submit_btn" <?php $this->set_checked($settings['reset_submit_btn']); ?>>
					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="field_relation"><?php _e("Field relationships:", $this->slug); ?></label>
					</td>
					<td>
						<select name="field_relation" id="field_relation">
							<option value="and" <?php $this->set_selected($settings['field_relation'], "and"); ?>><?php _e("AND", $this->slug); ?></option>
							<option value="or" <?php $this->set_selected($settings['field_relation'], "or"); ?>><?php _e("OR", $this->slug); ?></option>
						</select>
						<p class="description"><?php _e("AND - posts shown will match all fields, OR - posts shown will match any of the fields", $this->slug); ?></p>
					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="fields_free_text_input">
							<?php _e("Show search input field?", $this->slug); ?>
						</label>
					</td>
					<td>
						<div class="free-input-search">
							<input class="checkbox fields_free_text_input" type="checkbox" id="fields_free_text_input" value="1" name="fields_free_text_input" <?php $this->set_checked($settings['fields_free_text_input']); ?>>
							<button id="free-search-setting" type="button">setting</button>
						</div>
						<div id="free-searh-setting-list">
							<label for="free-search-content">
								<span>Text Search on content</span>
								<input class="checkbox free-search-content" type="checkbox" id="free-search-content" value="1" name="free-search-content" <?php $this->set_checked($settings['free-search-content']); ?>>
							</label>
							<div>
								<p>Text Search on taxonomies</p>
								<label>
									<span>Search on Content</span>
									<input class="checkbox free-search-content" type="checkbox" id="free-search-content" value="1" name="free-search-content" <?php $this->set_checked($settings['free-search-content']); ?>>
								</label>
								<label>
									<span>Search on Content</span>
									<input class="checkbox free-search-content" type="checkbox" id="free-search-content" value="1" name="free-search-content" <?php $this->set_checked($settings['free-search-content']); ?>>
								</label>
							</div>
						</div>

					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="search_fields">
							<?php _e("Search Field", $this->slug); ?>
						</label>
					</td>
					<td id="search-field-dynamic">
						<div class="search-fields" data-region="search-fields" style="margin-top: 12px;">
							<?php
							if ($settings['filter_setting_post_type']) {
								foreach ($settings['fields'] as $key => $field_values) {
									$output_fields =  \custom_search_filter\Helper::load_search_add_field($settings['filter_setting_post_type'], $key, $field_values);
									echo $output_fields;
								}
							}
							?>
						</div>
						<div class="add-new-field">
							<button type="button" data-action="add-field">Add Field</button>
						</div>
					</td>
				</tr>

				<tr>
					<td>
						<label for="search-filter-css-class">
							<?php _e("Search Filter Form CSS Class", $this->slug); ?>
						</label>
					</td>
					<td>
						<input class="" name="search_filter_css_class" type="text" size="21" value="<?php echo _e($settings['search_filter_css_class']); ?>" placeholder="Search Filter css class" id="search-filter-css-class-input" style="width:100%;">
					</td>
				</tr>

				<tr>
					<td style="vertical-align: top;">
						<label for="search-filter-template">
							<?php _e("Search Filter Result Template path", $this->slug); ?>
						</label>
					</td>
					<td>
						<input class="" name="search_filter_result_template" type="text" value="<?php echo _e($settings['search_filter_result_template']); ?>" placeholder="Result template" id="search-filter-result-template-input" style="width:100%;">
						<p class="description">Example template : current theme template part or file path for the result show.</p>
					</td>
				</tr>

				<tr>
					<td>
						<label for="apply_only_on_show_result">
							<?php _e("Apply query only on show result area", $this->slug); ?>
						</label>
					</td>
					<td>
						<input class="checkbox apply_only_on_show_result" type="checkbox" id="apply_only_on_show_result" value="1" name="apply_only_on_show_result" <?php $this->set_checked($settings['apply_only_on_show_result']); ?>>
					</td>
				</tr>

			</table>

		</div>

	</div>

</div>