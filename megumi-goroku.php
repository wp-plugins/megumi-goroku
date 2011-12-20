<?php
/*
Plugin Name: Megumi Goroku
Plugin URI: http://www.megumi-goroku.com
Version: 1.3.5
Description: This is not just a plug-in. Words that are used in pairs because the business. If this plugin is enabled and displays a random sayings from the Megumi Goroku on all management right of the screen.
Author: Webnist
Author URI: http://webnist.jp
Text Domain: megumi-goroku
Domain Path: /languages/
*/

if ( ! defined( 'MEGUMI_GOROKU_DIR' ) )
	define( 'MEGUMI_GOROKU_DIR', WP_PLUGIN_DIR . '/megumi-goroku' );

if ( ! defined( 'MEGUMI_GOROKU_URL' ) )
	define( 'MEGUMI_GOROKU_URL', WP_PLUGIN_URL . '/megumi-goroku' );

load_plugin_textdomain( 'megumi-goroku', false, '/megumi-goroku/languages' );

/* *** Get Megumi Goroku *** */
function get_megumi_goroku() {
	global $wp_version;
	$rss_content = get_transient( 'megumi_goroku_key' );
	if ( $rss_content === false ) {
		$rss_url = 'http://www.megumi-goroku.com/feed/'; // Delivery number 100.
		$rss_data = simplexml_load_file( $rss_url );
		$items = $rss_data->channel->item;
		$rss_content = array();
	
		foreach ( $rss_data->channel->item as $rss ) {
			$title = (string) $rss->title;
			$link  = (string) $rss->link;
			$rss_content[] = array( 'title' => $title, 'link' => $link );
		}
		set_transient( 'megumi_goroku_key', $rss_content, 60 * 60 * 24 );
	}
	$key = array_rand( $rss_content );
	$title = $rss_content[$key]['title'];
	$link = $rss_content[$key]['link'];
	if ( version_compare( $wp_version, '3.3', '<' ) ) {
		$output = '<p id="megumi-goroku-text"><a href="' . $link . '" target="_blank">' . $title . '</a></p>';
	} else {
		$output = '<div id="megumi-goroku-text"><a href="' . $link . '" target="_blank">' . $title . '</a></div>';
	}
	return $output;
}

/* *** Admin Page *** */
add_action('in_admin_header', 'admin_megumi_goroku');
function admin_megumi_goroku() {
	echo get_megumi_goroku();
}

add_action( 'admin_print_styles', 'add_admin_goroku_style' );
function add_admin_goroku_style() {
	wp_enqueue_style( 'megumi-goroku-admin-style', MEGUMI_GOROKU_URL . '/css/admin_goroku_style.css' );
}

/* *** Page *** */
add_action( 'wp_print_styles', 'add_goroku_style' );
function add_goroku_style() {
	if ( !is_admin() ) {
		wp_enqueue_style( 'megumi-goroku-style', MEGUMI_GOROKU_URL . '/css/goroku_style.css' );
	}
}

class MG_Widget_Goroku extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_goroku', 'description' => __( 'To view the Megumi Goroku', 'megumi-goroku' ) );
		parent::__construct( 'megumi-goroku', __( 'Megumi Goroku', 'megumi-goroku' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Megumi Goroku', 'megumi-goroku' ) : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		// Use current theme search form if it exists
		echo get_megumi_goroku();

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:') . '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '" /></label></p>';
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}

function mg_widgets_init() {
	if ( !is_blog_installed() )
		return;

	register_widget('MG_Widget_Goroku');

}

add_action('init', 'mg_widgets_init', 1);
