<?php

/**
 *
 * Plugin Name:       Genesis Jetpack Logo
 * Plugin URI:        http://wpzombies.com/genesis-jetpack-logo/
 * Description:       Connects the Jetpack logo feature to Genesis.
 * Version:           1.0.0
 * Author:            Juan Rangel
 * Author URI:        http://wpzombies.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       genesis-jetpack-logo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPZ_Genesis_Jetpack_Logo {
	var $instance;

	function __construct() {
		$this->instance =& $this;


		add_action( 'init', array( $this, 'init' ) );
		add_action( 'after_setup_theme', array( $this, 'gjl_jetpack_logo' ) );	

		register_activation_hook( __FILE__, array( $this, 'activation_hook') );
		add_action( 'genesis_site_title', array( $this, 'gjl_do_logo' ), 5);


	}

	function activation_hook() {
		if ( 'genesis' != basename( TEMPLATEPATH ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( 'Sorry, you cannot activate unless you have installed <a target="_blank" href="%s">Genesis</a>', 'http://www.studiopress.com' ) );
		}
	}

	function init() {
		$img_w = genesis_get_option( 'gjl_user_size_width', 'gjl_genesis_jetpack_logo' );
		$img_h = genesis_get_option( 'gjl_user_size_height', 'gjl_genesis_jetpack_logo' );

		if ( isset($img_w) && isset($img_h) )
			add_image_size( 'genesis-jetpack-logo', $img_w, $img_h );
	}

	function gjl_jetpack_logo() {
		$user_size = genesis_get_option('gjl_logo_size', 'gjl_genesis_jetpack_logo');
		$user_size = ( isset($user_size) ? $user_size : 'medium' );

		$args = array(
		    'header-text' => array(
		        'site-title',
		        'site-description',
		    ),
		    'size' => $user_size,
		);
		add_theme_support( 'site-logo', $args );
	}

	function gjl_do_logo() {
		if( function_exists( 'jetpack_the_site_logo' ) )
			jetpack_the_site_logo();
	}

}

new WPZ_Genesis_Jetpack_Logo;

function gjl_register_genesis_logo_settings() {

	if ( ! class_exists( 'Genesis_Admin_Boxes' ) )
		exit;

	class WPZ_Genesis_Jetpack_Logo_Settings extends Genesis_Admin_Boxes {

		function __construct() {
			$page_id = 'genesis-jetpack-logo';

			$menu_ops = array(
				'submenu' => array(
					'parent_slug' => 'genesis',
					'page_title' => 'Genesis Jetpack Logo',
					'menu_title' => 'Genesis Jetpack Logo'
				)
			);

			$page_ops = array();

			$settings_field = 'gjl_genesis_jetpack_logo';

			$default_settings = array(
				'gjl_logo_size' => 'medium',
				'gjl_user_size_width' => '',
				'gjl_user_size_height' => ''
			);

			$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

			add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitization_filters' ) );
		}

		function sanitization_filters() {
			genesis_add_option_filter( 'no_html', $this->settings_field,
			array( 
				'gjl_logo_size',
				'gjl_user_size_width',
				'gjl_user_size_height'
				) );
		}

		function metaboxes() {
			add_meta_box( 'genesis-jetpack-logo', 'Genesis Jetpack Logo', array( $this, 'genesis_jetpack_logo_options'), $this->pagehook, 'main', 'high' );
		}

		function genesis_jetpack_logo_options() {

			$sizes = genesis_get_image_sizes();
			
			echo '<p><strong>Logo Demensions: </strong><br>';			

			$width_name = $this->get_field_name( 'gjl_user_size_width' );
			$width_value = $this->get_field_value( 'gjl_user_size_width' );
			$height_name = $this->get_field_name( 'gjl_user_size_height' );
			$height_value = $this->get_field_value( 'gjl_user_size_height' );
			$select_size = $this->get_field_name( 'gjl_logo_size');

			printf('<input type="number" placeholder="width" name="%s" value="%s" > &#215; ',$width_name, $width_value );
			printf('<input type="number" placeholder="width" name="%s" value="%s" >',$height_name, $height_value );

			echo '<br><small>After specifying your logo dimensions you can save the settings and choose your new size below.</small></p>';

			echo '<p><strong>Logo Image Size:</strong><br>';
			echo '<select name="'.$select_size.'">';
			foreach ( (array) $sizes as $name => $size )
				echo '<option value="' . $name . '"' . selected( $this->get_field_value( 'gjl_logo_size' ), $name, FALSE ) . '>' . $name . ' (' . $size['width'] . ' &#215; ' . $size['height'] . ')</option>' . "\n";
			echo '</select></p>';

			echo '<p><small>Switching to a theme that uses a custom image size for site logos can result in the full size being displayed in the Customizer preview. This is because the thumbnails for that custom size may not have been generated if it’s the first time the site is using that theme. To correct this, install the Regenerate Thumbnails plugin and run it after the theme switch. After it completes, assigning an image to the site logo will result in the proper size being displayed in the Customizer preview pane.</small></p>';



		}

	}

	$genesis_jetpack_logo = new WPZ_Genesis_Jetpack_Logo_Settings;	 
}
add_action( 'genesis_admin_menu', 'gjl_register_genesis_logo_settings'  ); 