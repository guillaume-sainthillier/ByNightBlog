<?php
/*
Plugin Name: Footer Mega Grid Columns
Plugin URL: http://wponlinesupport.com
Text Domain: footer-mega-grid-columns
Description: Footer Mega Grid Columns - Register a widget area for your theme and allow you to add and display widgets in grid view with multiple columns.
Domain Path: /languages/
Version: 1.0.1
Author: WP Online Support
Author URI: http://wponlinesupport.com
Contributors: WP Online Support
*/

if( !defined( 'FMGC_VERSION' ) ) {
	define( 'FMGC_VERSION', '1.0.1' ); // Version of plugin
}

add_action('plugins_loaded', 'fmgc_load_textdomain');
function fmgc_load_textdomain() {
	load_plugin_textdomain( 'footer-mega-grid-columns', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
} 

add_action( 'wp_enqueue_scripts','fmgc_script_free' );
function fmgc_script_free() {
    wp_enqueue_style( 'fmgc-css',  plugin_dir_url( __FILE__ ). 'css/fmgc-css.css', array(), FMGC_VERSION );  
}
/**
 * Register our main widget areas.
**/
function fmgc_widgets_init() {

		register_sidebar( array(
			'name' => __( 'Footer Mega Grid Columns', 'footer-mega-grid-columns' ),
			'id' => 'fmgc-footer-widget',
			'description' => __( 'Footer Mega Grid Columns- Register a widget area for your theme and allow you to add and display widgets in grid view.', 'footer-mega-grid-columns' ),
			'before_widget' => '<aside id="%1$s" class="widget fmgc-columns '. slbd_count_widgets( 'fmgc-footer-widget' ) .' %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h6 class="widget-title">',
			'after_title' => '</h6>',
		) );		
	}
	add_action( 'widgets_init', 'fmgc_widgets_init' );


/**
 * Count number of widgets in a sidebar
 */
function slbd_count_widgets( $sidebar_id ) {	
	global $_wp_sidebars_widgets;	
	
	$sidebars_widgets_count = $_wp_sidebars_widgets;	
	if ( isset( $sidebars_widgets_count[ $sidebar_id ] ) ) {
		$widget_count = count( $sidebars_widgets_count[ $sidebar_id ] );
		 $widget_classes = 'widget-count-' . count( $sidebars_widgets_count[ $sidebar_id ] );
		
		if ( $widget_count == 2 ) {			
			$widget_classes .= ' fmgc-per-row-6';
		} elseif  ( $widget_count == 3 ) {			
			$widget_classes .= ' fmgc-per-row-4';
		} elseif ( $widget_count == 4 ) {			
			$widget_classes .= ' fmgc-per-row-3';
		}  elseif ( $widget_count == 6 ) {			
			$widget_classes .= ' fmgc-per-row-2';
		} else {
			$widget_classes .= ' fmgc-per-row-12';
		}
		return $widget_classes;
		}
}

function slbd_display_widgets(){
	if ( is_active_sidebar( 'fmgc-footer-widget' ) ) :   ?>
	<div class="footer-mega-col">
		<div class="footer-mega-col-wrap">
           <?php  dynamic_sidebar( 'fmgc-footer-widget' );  ?> 
		</div>	
	</div>
	<?php endif;
}
//add_action( 'sldb_do_widget', 'slbd_display_widgets' );