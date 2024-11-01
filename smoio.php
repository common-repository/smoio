<?php

/*
Plugin Name: smoio
Plugin URI:
Description: syndicate, share, subscribe & engage
Version: 0.9.1
Author: Stefan Boeck
Author URI: http://stefan.boeck.name/
License: GPLv2
Site Wide Only: true
*/

require_once('smoio_publisher.class.php');


/*
 * publish
 */
function smoio_publish_to_hub($post_id)  {
	
	$post_data = get_post( $post_id, ARRAY_A );
	
	if(in_array($post_data['post_status'], array('future', 'publish'))) {

		$post_title = $post_data['post_title'];
		$post_content = $post_data['post_content'];
		$post_link = get_permalink($post_id);

		$post_categories_array = array();
		$post_categories_data = get_the_category( $post_id );
		foreach($post_categories_data as $category) {
			$post_categories_array[] = $category->cat_name;
		}
		$post_categories = $post_categories_array;

		$post_tags_array = array();
		$post_tags_data = wp_get_post_tags( $post_id );
		foreach($post_tags_data as $tag) {
			$post_tags_array[] = $tag->name;
		}
		$post_tags = $post_tags_array;
		
		$post_geotag = array();
		if(function_exists('get_wpgeo_latitude')) {
			if(get_wpgeo_latitude( $post_id ) and get_wpgeo_longitude( $post_id )) {
				$post_geotag = array(get_wpgeo_latitude($post_id), get_wpgeo_longitude($post_id));
			}
		}	
	}
	
	$item->permalinkUrl = $post_link;
	$item->title = $post_title;
	$item->content = $post_content;
	$item->postedTime = time();
	$item->categories = $post_categories;
	$item->geo->type = "point";
	$item->geo->coordinates = $post_geotag;
	
	$data->status->feed = get_bloginfo('rss2_url');
	$data->items = $item;
	
	$data_json = json_encode($data);	
	
	$secret = get_option('smoio_setting_secret');
	$smoio_publisher_obj = new smoio_publisher($secret);
	$smoio_publisher_obj->publish($data_json);
    
	return $post_id;
}
add_action('publish_post', 'smoio_publish_to_hub');

/*
 * admin
 */
function smoio_admin_add_page() {
	add_options_page('Custom Plugin Page', 'Smoio', 'manage_options', 'smoio', 'smoio_options_page');
}
add_action('admin_menu', 'smoio_admin_add_page');


function smoio_options_page() {
?>
<div class="wrap">
<h2>Smoio Settings</h2>
Options relating to the Custom Plugin.
<form action="options.php" method="post">
<?php settings_fields('smoio'); ?>
<?php do_settings_sections('smoio'); ?>
<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  /></p></form>
</div>
<?php
}

function smoio_settings_api_init() {
 	add_settings_section('smoio_setting_section',
		'Smoio settings section',
		'smoio_setting_section_callback_function',
		'smoio');
 	add_settings_field('smoio_setting_secret',
		'Smoio setting secret',
		'smoio_setting_callback_function',
		'smoio',
		'smoio_setting_section');
 	register_setting('smoio', 'smoio_setting_secret');
 	#do_settings_sections('smoio');
}
function smoio_setting_section_callback_function() {
	echo '<p>Intro text for smoio settings section</p>';
}
function smoio_setting_callback_function() {
	echo "<input id='plugin_text_string' name='smoio_setting_secret' size='50' type='text' value='".get_option('smoio_setting_secret')."' />";
}
add_action('admin_init', 'smoio_settings_api_init');