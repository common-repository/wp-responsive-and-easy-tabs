<?php
/*
Plugin Name: WP Easy Tabs
Plugin URI: http://www.netattingo.com/
Description: This plugin provides features to add tabs to you post or pages just using shortcode. 
Author: NetAttingo Technologies
Version: 1.0.1
Author URI: http://www.netattingo.com/
*/

//initialize constant
define('WPET_DIR', plugin_dir_path(__FILE__));
define('WPET_URL', plugin_dir_url(__FILE__));
define('WPET_INCLUDE_DIR', plugin_dir_path(__FILE__).'pages/');
define('WPET_INCLUDE_URL', plugin_dir_url(__FILE__).'pages/');

// plugin activation hook called	
function wpet_install() {
   	global $wpdb;
}
register_activation_hook(__FILE__, 'wpet_install');

// plugin deactivation hook called	
function wpet_uninstall() {	
	global $wpdb;
}
register_deactivation_hook(__FILE__, 'wpet_uninstall');

//Include css and js file at particular location
function wpet_js_css_files() {
	wp_enqueue_style( 'wpet_css', plugins_url('includes/front-style.css',__FILE__ ));
}
add_action( 'wp_enqueue_scripts','wpet_js_css_files');

//add admin css
function wpet_admin_css() {
  wp_register_style('admin_css', plugins_url('includes/admin-style.css',__FILE__ ));
  wp_enqueue_style('admin_css');
}
add_action( 'admin_init','wpet_admin_css');


// admin menu
function wpet_menus() {
	add_submenu_page("edit.php?post_type=tabs-items", "About Us", "About Us", "administrator", "about-us", "wpet_pages");
}
add_action("admin_menu", "wpet_menus");


//function menu pages
function wpet_pages() {

   $setting = WPET_INCLUDE_DIR.$_GET["page"].'.php';
   include($setting);

}



//custom post  type and custom texonomy
add_action( 'init', 'wpet_slider_custom_post' );
function wpet_slider_custom_post() {

	register_post_type( 'tabs-items',
		array(
			'labels' => array(
				'name' => __( 'Easy Tabs' ),
				'singular_name' => __( 'Tab' ),
				'add_new_item' => __( 'Add New Tab' )
			),
			'public' => true,
            'menu_position' => 14,
			'supports' => array( 'title', 'editor', 'custom-fields'),
			'has_archive' => true,
			'menu_icon' => 'dashicons-editor-justify',
			'rewrite' => array('slug' => 'tabs-items'),
			
		)
	);
		
}

function wpet_slider_taxonomy() {
	register_taxonomy(
		'tabslider_cat',  
		'tabs-items',                  
		array(
			'hierarchical'          => true,
			'label'                         => 'Tab Category',  
			'query_var'             => true,
			'show_admin_column'			=> true,
			'rewrite'                       => array(
				'slug'                  => 'tab-category', 
				'with_front'    => true 
				)
			)
	);
}
add_action( 'init', 'wpet_slider_taxonomy');   


//shortcode for  easy tab

function wpet_tab_function_shortcode($atts){
	extract( shortcode_atts( array(
		'category' => '',
		'style' => '',
		'count' => '5',
		'id' => ''
	), $atts, 'projects' ) ); 
	
      $q = new WP_Query(
        array('posts_per_page' => $count, 'post_type' => 'tabs-items', 'tabslider_cat' => $category)
        );		
	
			$list='';
			
			//tabs heading start
			$list.= '<ul class="tab-cat-name"><li>';
			$i=1;
			while($q->have_posts()) : $q->the_post();
			$activclass='';
			if($i == 1){ $activclass= 'active'; }
			$list.=  '<a  id="tab_'.$i.'" class="'.$activclass.'">'.get_the_title().'</a>';
			$i=$i+1;
		    endwhile;
			wp_reset_query();
			$list.= '</li></ul>';
			//tabs heading end
			
			//tabs content start
			$i=1;
			while($q->have_posts()) : $q->the_post();
		    $displayclass= 'none';
			if($i == 1){ $displayclass= 'block'; }
			$list.=  '<div class="main-tab-content"><div class="tab-listing" id="show_hide_tab_'.$i.'" style="display:'.$displayclass.';"> '.get_the_content().'</div></div>';
			$i=$i+1;
		    endwhile;
			wp_reset_query();
			//tabs content end
		  
		    //tabs script start
			$list.='<script type="text/javascript">
			 jQuery(document).on("click", ".tab-cat-name li a", function() {
				 var currentid= this.id;
				   jQuery( ".tab-listing" ).each(function( index ) {
					var indexplus= index+1;
					var toshow= "tab_"+indexplus;   
						if(currentid==toshow){
						jQuery("#show_hide_"+toshow).fadeIn(1000);
						}else{
						jQuery("#show_hide_"+toshow).fadeOut(1000);
						}
					}); 
				   jQuery(".tab-cat-name li a").removeClass("active");
				   jQuery(this).addClass("active");
			  });			
			</script>';
			//tabs script end
			
			if($q->found_posts < 1){
			$list .='<strong>No data yet. </strong>';
			}
		
		return '<div class="wpet-tab-main-div">'.$list.'</div>';		
}
add_shortcode('wp-easy-tab', 'wpet_tab_function_shortcode');	



