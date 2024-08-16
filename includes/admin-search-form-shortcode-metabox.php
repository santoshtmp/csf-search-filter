<?php

/**
 * 
 * @package   
 * @author    santoshtmp7
 * @link      
 * @copyright 2024 Search Filter
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	exit;
}

global $post;

?>

<div id="shortcode-info" class="widgets-search-filter-draggables ui-search-filter-sortable setup" data-allow-expand="0">
	<br />
	<strong><?php _e("Search Form Shortcode:", $this->slug); ?></strong>
	<br />
	<p class="description-inline">
		<label for="{0}[{1}][enable_auto_count]">
			<input class="" name="form_shortcode" type="text" size="21" value="<?php echo esc_attr('[customsearchfilter id="' . $post->ID . '"]');  ?>">
		</label>
		<br>
	<pre>echo do_shortcode('[customsearchfilter id="<?php echo $post->ID; ?>" ');</pre>


	</p>
	<div class="results-shortcode">
		<br />
		<strong><?php _e("Results Shortcode:", $this->slug); ?></strong>
		<br />
		<p class="description-inline">
			<input class="" name="results_shortcode" type="text" size="21" value="<?php echo esc_attr('[customsearchfilter id="' . $post->ID . '" result_show="true"]');  ?>">
			<br>
		<pre>echo do_shortcode('[customsearchfilter id="<?php echo $post->ID; ?>" result_show="true"]');</pre>
		</p>
	</div>
	<br />
</div>