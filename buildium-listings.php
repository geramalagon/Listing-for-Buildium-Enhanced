<?php
/**
* Plugin Name: Listings for Buildium Pro
* Description: This is a Pro solution to your Buildium Listings integration with your WordPress website
* Version: 2.0.3
* Author: Listings for Buildium
* Author URI: https://listingsforbuildium.com/
* License: GPL+2
* Text Domain: listings-for-buildium-pro
* Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

define ( 'BLDM_PRO_CURR_VER', '2.0.3' );

include_once dirname( __FILE__ ) . '/bldm_actdeact.php';
register_activation_hook( __FILE__, array( 'Bldm_Actdeact', 'bldm_plugin_activate' ) );

add_action( 'init', 'bldm_pp_init_plugin', 1 );
if (!function_exists('bldm_pp_init_plugin')) {
	function bldm_pp_init_plugin(){
		global $bldm_plugin_url;
		global $bldm_listings_url;
		$bldm_plugin_url = plugin_dir_url( __FILE__ );
		$bldm_listings_url = get_option('bldm_url');
		
		if ( is_admin() ){
			add_action( 'admin_enqueue_scripts', 'bldm_ppp_admin_styles_scripts' );
		}
		
		global $bldm_item_reference, $bldm_license_server_url, $bldm_sales_site_url, $bldm_special_secretkey;
		$bldm_item_reference = 'buildium-listings';
		$bldm_license_server_url = 'https://listingsforbuildium.com';
		$bldm_sales_site_url = "https://listingsforbuildium.com";
		$bldm_special_secretkey = '6055f16d4e4e60.82251702';

		global $blocked_msg;
		global $is_expired;
		$is_expired = false;
		$licensed = get_option('bldm_licensed');
		
		if (!$licensed || is_admin()) {

			$lic_checked = get_transient('bldm_lic_checked');
			if (false == $lic_checked) {

				$slmclient = new BldmSlmClient();
				$license_array = array(
					'item_reference' => $bldm_item_reference,
					'license_server_url' => $bldm_license_server_url,
					'sales_site_url' => $bldm_sales_site_url,
					'special_secretkey' => $bldm_special_secretkey,
				);
				// $licensed = $slmclient->is_licensed( $license_array );

				$updatable = false;
				$licensed_array = $slmclient->is_licensed($license_array);
				if ($licensed_array) {

					if ('blocked' == $licensed_array->status) {
						$licensed = false;
						$blocked_msg = '<div class="notice notice-warning"><p>Your license key is blocked. Please contact us <a href="https://listingsforbuildium.com/" target="_blank">Here</a></p></div>';
					} else if ('expired' == $licensed_array->status) {
						$licensed = true;
						$is_expired = true;
					} else // pending / active
					{
						$licensed = true;
						if ('active' == $licensed_array->status) {
							$updatable = true;
						}
						set_transient('bldm_lic_checked', true, 43200); // 12 hours cache if license key is true
					}
				} else {
					$licensed = false;
				}
				update_option('bldm_licensed', $licensed);
				update_option('bldm_licensed_updatable', $updatable);
			}

		}
		
		if($licensed){

			add_action( 'wp_enqueue_scripts', 'bldm_pp_styles_scripts' );
			
			// Including main functions
			if(!class_exists ('simple_html_dom')){
				require(plugin_dir_path(__FILE__ ) . 'inc/simple_html_dom.php');
			}
			
			include(plugin_dir_path(__FILE__ ) . 'inc/customizer.php');
			include(plugin_dir_path(__FILE__ ) . 'inc/single-listing.php');
			include(plugin_dir_path(__FILE__ ) . 'inc/listings.php');
			include(plugin_dir_path(__FILE__ ) . 'inc/admin-functions.php');
			include(plugin_dir_path(__FILE__ ) . 'config.php');
			
			// Shortcodes
			add_shortcode('bldm_listings', 'bldm_pp_display_all_listings');
			
		}
	}
}

if (!function_exists('bldm_pp_styles_scripts')) {
	function bldm_pp_styles_scripts(){
		wp_enqueue_style(
			'bldm-pp-style',
			plugin_dir_url( __FILE__ ) . 'css/style.css'
		);
		wp_enqueue_style(
			'bldm-pp-gall-style',
			plugin_dir_url( __FILE__ ) . 'css/gallery.css'
		);
		wp_enqueue_script(
			'bldm-pp-script',
			plugins_url('js/main.js',__FILE__ ),
			array('jquery')
		);
	}
}

if (!function_exists('bldm_ppp_admin_styles_scripts')) {
	function bldm_ppp_admin_styles_scripts(){
		wp_enqueue_style(
			'bldm-pp-admin-style',
			plugin_dir_url( __FILE__ ) . 'css/admin-style.css'
		);
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'bldm-pp-admin-script',
			plugins_url('js/admin-main.js',__FILE__ ),
			array('jquery', 'wp-color-picker')
		);
		wp_localize_script(
			'bldm-pp-admin-script',
			'bldm_admin_obj',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'bldm_pro_plugin_url' => plugin_dir_url(__FILE__),
				'nonce' => wp_create_nonce('bldm_ajax_nonce')
			)
		);
	}
}

if ( ! class_exists( 'BldmSlmClient' ) ) {
	require_once dirname( __FILE__ ) . '/inc/license-checker/class-bldm-slmclient.php';
}

// Add notice for expired license
function bldm_pp_admin_notice()
{
	global $is_expired;
	if ($is_expired) {
		echo '<div class="notice notice-warning is-dismissible">
		 <p>Your license key for Listings for Buildium has expired. Please get a new key to get automatic updates and ensure the plugin is working fine. <a href="https://listingsforbuildium.com/" target="_blank">More Info</a></p>
	 </div>';
	}
}
add_action('admin_notices', 'bldm_pp_admin_notice');

// Plugin Configuration Page
if(is_admin()){
	add_action('admin_menu', 'bldm_pp_admin_config');
	if (!function_exists('bldm_pp_admin_config')) {
		function bldm_pp_admin_config() {
			add_menu_page('Listings for Buildium', 'Buildium', 'manage_options', 'bldm-pp', 'bldm_pp_config_callback', 'dashicons-admin-home');
			add_submenu_page('bldm-pp', 'Settings', 'Settings', 'manage_options', 'bldm-pp', 'bldm_pp_config_callback', 1);
			add_submenu_page('bldm-pp', 'Buildium Customizer', 'Customizer', 'manage_options', 'bldm-pp-builder', 'bldm_pp_builder_callback', 3);
		}
	}
	
	if (!function_exists('bldm_pp_config_callback')) {
		function bldm_pp_config_callback()
		{
			if (!current_user_can('manage_options')){
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			global $bldm_item_reference, $bldm_license_server_url, $bldm_sales_site_url, $bldm_special_secretkey;
			if ($_POST) {
				if (isset($_POST['bldm_remove_lic'])) {
					$arg = array('item_reference' => $bldm_item_reference, 'sales_site_url' => $bldm_sales_site_url, 'license_server_url' => $bldm_license_server_url, 'special_secretkey' => $bldm_special_secretkey);
					$removed = do_action('deactive_slm_key', $arg);
					update_option('bldm_licensed', false);
					update_option('bldm_licensed_updatable', false);
					delete_transient( 'bldm_lic_checked' );
				}
			}

			echo do_shortcode('[bldm_slmcl item_reference="' . $bldm_item_reference . '" sales_site_url="' . $bldm_sales_site_url . '" license_server_url="' . $bldm_license_server_url . '" special_secretkey="' . $bldm_special_secretkey . '"]');
			
			$licensed = get_option('bldm_licensed');
			
			if (!$licensed) {
				global $blocked_msg;
				if ($blocked_msg) {
					echo $blocked_msg;
				}
			}
			
			if ( $licensed ) {
				
				echo '<form method="POST" action="" class="bldm_lic_btn">
						<input type="submit" name="bldm_remove_lic" value="Deactivate">
					</form>';
				
				if($_POST){
					if(isset($_POST['bldm_config_submit'])){
						
						if(isset($_POST['bldm_config_url'])){
							$bldm_url = sanitize_text_field($_POST['bldm_config_url']);
							$bldm_url_updated = update_option('bldm_url', $bldm_url);
						}
						
						// Saved message
						echo '<div class="notice notice-success is-dismissible"><p>Settings Saved!</p></div>';
						
					}
				}
				?>

				<div class="wrap" style="margin: 0 20px 0 2px;">
					<div id="bldm_pro_settings">
						<form method='POST' action="" style="border: 1px solid #154e6a;">
							<h1 class="bldm_admin_h1">Buildium Account Settings<p class="bldm_sctxt">Shortcode - <span>[bldm_listings]</span></p></h1>
							<table class="form-table bldm_url_config">
								<tr>
									<th>
										<?php $bldm_listing_url = get_option('bldm_url'); ?>
										<label for="bldm_config_url">Enter Buildium URL to fetch listings: </label>
									</th>
									<td>
										<input type="text" name="bldm_config_url" id="bldm_config_url" style="min-width: 350px;" placeholder="For Example - https://example.managebuilding.com" value="<?php echo $bldm_listing_url; ?>">
									</td>
								</tr>
							</table>
							<p class="submit bldm_config_save">
								<input type="submit" name="bldm_config_submit" id="bldm_config_submit" class="button-primary" value="Save"/>
							</p>
						</form>
					</div>
				</div>
			<?php
			}
		}
	}
	
	// Content Builder
	if (!function_exists('bldm_pp_builder_callback')) {
		function bldm_pp_builder_callback(){
			
			if($_POST){
				if(isset($_POST['bldm_cstmzr_sbmt'])){
					if(isset($_POST['bldm_columns_cnt'])){
						$bldm_columns_cnt = sanitize_text_field($_POST['bldm_columns_cnt']);
						update_option('bldm_columns_cnt', $bldm_columns_cnt);
					}
					
					if (isset($_POST['bldm_page_sub_hdng'])) {
						$bldm_page_sub_hdng = strip_tags($_POST['bldm_page_sub_hdng']);
						update_option('bldm_page_sub_hdng', $bldm_page_sub_hdng);
					}
					
					if (isset($_POST['bldm_listings_banner_bg'])) {
						$bldm_listings_banner_bg = sanitize_text_field($_POST['bldm_listings_banner_bg']);
						update_option('bldm_listings_banner_bg', $bldm_listings_banner_bg);
					}
					
					if (isset($_POST['bldm_listings_banner_image'])) {
						$bldm_listings_banner_image = filter_var($_POST['bldm_listings_banner_image'], FILTER_UNSAFE_RAW);
						$value_to_store = ($bldm_listings_banner_image == 'on') ? 'show' : 'hide';
						update_option('bldm_listings_banner_image', $value_to_store);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_font_size'])) {
						$bldm_listings_banner_heading_font_size = sanitize_text_field($_POST['bldm_listings_banner_heading_font_size']);
						update_option('bldm_listings_banner_heading_font_size', $bldm_listings_banner_heading_font_size);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_font_weight'])) {
						$bldm_listings_banner_heading_font_weight = sanitize_text_field($_POST['bldm_listings_banner_heading_font_weight']);
						update_option('bldm_listings_banner_heading_font_weight', $bldm_listings_banner_heading_font_weight);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_color'])) {
						$bldm_listings_banner_heading_color = sanitize_text_field($_POST['bldm_listings_banner_heading_color']);
						update_option('bldm_listings_banner_heading_color', $bldm_listings_banner_heading_color);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_line_height'])) {
						$bldm_listings_banner_heading_line_height = sanitize_text_field($_POST['bldm_listings_banner_heading_line_height']);
						update_option('bldm_listings_banner_heading_line_height', $bldm_listings_banner_heading_line_height);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_text_transform'])) {
						$bldm_listings_banner_heading_text_transform = sanitize_text_field($_POST['bldm_listings_banner_heading_text_transform']);
						update_option('bldm_listings_banner_heading_text_transform', $bldm_listings_banner_heading_text_transform);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_text_align'])) {
						$bldm_listings_banner_heading_text_align = sanitize_text_field($_POST['bldm_listings_banner_heading_text_align']);
						update_option('bldm_listings_banner_heading_text_align', $bldm_listings_banner_heading_text_align);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_padding_top'])) {
						$bldm_listings_banner_heading_padding_top = sanitize_text_field($_POST['bldm_listings_banner_heading_padding_top']);
						update_option('bldm_listings_banner_heading_padding_top', $bldm_listings_banner_heading_padding_top);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_padding_bottom'])) {
						$bldm_listings_banner_heading_padding_bottom = sanitize_text_field($_POST['bldm_listings_banner_heading_padding_bottom']);
						update_option('bldm_listings_banner_heading_padding_bottom', $bldm_listings_banner_heading_padding_bottom);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_padding_left'])) {
						$bldm_listings_banner_heading_padding_left = sanitize_text_field($_POST['bldm_listings_banner_heading_padding_left']);
						update_option('bldm_listings_banner_heading_padding_left', $bldm_listings_banner_heading_padding_left);
					}
					
					if (isset($_POST['bldm_listings_banner_heading_padding_right'])) {
						$bldm_listings_banner_heading_padding_right = sanitize_text_field($_POST['bldm_listings_banner_heading_padding_right']);
						update_option('bldm_listings_banner_heading_padding_right', $bldm_listings_banner_heading_padding_right);
					}
					
					if(isset($_POST['bldm_custom_apply_lnk'])){
						$bldm_custom_apply_lnk = sanitize_text_field($_POST['bldm_custom_apply_lnk']);
						$bldm_custom_apply_lnk_updated = update_option('bldm_custom_apply_lnk', $bldm_custom_apply_lnk);
					}
					if(isset($_POST['bldm_page_hdng'])){
						$bldm_page_hdng = wp_kses_post($_POST['bldm_page_hdng']);
						$bldm_page_hdng_updated = update_option('bldm_page_hdng', $bldm_page_hdng);
					}
					
					if(isset($_POST['bldm_pro_zip_filter'])){
						$bldm_filters_zip = filter_var($_POST['bldm_pro_zip_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_zip == 'on'){
							update_option('bldm_filters_zip', 'show');
						}
					} else{ update_option('bldm_filters_zip', 'hide'); }
					
					if(isset($_POST['bldm_pro_minrent_filter'])){
						$bldm_filters_minrent = filter_var($_POST['bldm_pro_minrent_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_minrent == 'on'){
							update_option('bldm_filters_minrent', 'show');
						}
					} else{ update_option('bldm_filters_minrent', 'hide'); }
					
					if(isset($_POST['bldm_pro_maxrent_filter'])){
						$bldm_filters_maxrent = filter_var($_POST['bldm_pro_maxrent_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_maxrent == 'on'){
							update_option('bldm_filters_maxrent', 'show');
						}
					} else{ update_option('bldm_filters_maxrent', 'hide'); }
					
					if(isset($_POST['bldm_pro_bed_filter'])){
						$bldm_filters_bed = filter_var($_POST['bldm_pro_bed_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_bed == 'on'){
							update_option('bldm_filters_bed', 'show');
						}
					} else{ update_option('bldm_filters_bed', 'hide'); }
					
					if(isset($_POST['bldm_pro_bath_filter'])){
						$bldm_filters_bath = filter_var($_POST['bldm_pro_bath_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_bath == 'on'){
							update_option('bldm_filters_bath', 'show');
						}
					} else{ update_option('bldm_filters_bath', 'hide'); }
					
					if(isset($_POST['bldm_pro_type_filter'])){
						$bldm_filters_type = filter_var($_POST['bldm_pro_type_filter'], FILTER_SANITIZE_STRING);
						if($bldm_filters_type == 'on'){
							update_option('bldm_filters_type', 'show');
						}
					} else{ update_option('bldm_filters_type', 'hide'); }
					
					if(isset($_POST['bldm_listings_search_color'])){
						$bldm_listings_search_color = sanitize_text_field($_POST['bldm_listings_search_color']);
						update_option('bldm_listings_search_color', $bldm_listings_search_color);
					}
					if(isset($_POST['bldm_listings_search_bg'])){
						$bldm_listings_search_bg = sanitize_text_field($_POST['bldm_listings_search_bg']);
						update_option('bldm_listings_search_bg', $bldm_listings_search_bg);
					}
					
					if(isset($_POST['bldm_listings_display_price'])){
						$bldm_listings_display_price = filter_var($_POST['bldm_listings_display_price'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_price == 'on'){
							update_option('bldm_listings_display_price', 'show');
						}
					} else{ update_option('bldm_listings_display_price', 'hide'); }
					if(isset($_POST['bldm_listings_price_pos'])){
						$bldm_listings_price_pos = sanitize_text_field($_POST['bldm_listings_price_pos']);
						update_option('bldm_listings_price_pos', $bldm_listings_price_pos);
					}
					if(isset($_POST['bldm_listings_price_color'])){
						$bldm_listings_price_color = sanitize_text_field($_POST['bldm_listings_price_color']);
						update_option('bldm_listings_price_color', $bldm_listings_price_color);
					}
					if(isset($_POST['bldm_listings_price_bg'])){
						$bldm_listings_price_bg = sanitize_text_field($_POST['bldm_listings_price_bg']);
						update_option('bldm_listings_price_bg', $bldm_listings_price_bg);
					}
					
					if(isset($_POST['bldm_listings_display_avail'])){
						$bldm_listings_display_avail = filter_var($_POST['bldm_listings_display_avail'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_avail == 'on'){
							update_option('bldm_listings_display_avail', 'show');
						}
					} else{ update_option('bldm_listings_display_avail', 'hide'); }
					if(isset($_POST['bldm_listings_avail_pos'])){
						$bldm_listings_avail_pos = sanitize_text_field($_POST['bldm_listings_avail_pos']);
						update_option('bldm_listings_avail_pos', $bldm_listings_avail_pos);
					}
					if(isset($_POST['bldm_listings_avail_color'])){
						$bldm_listings_avail_color = sanitize_text_field($_POST['bldm_listings_avail_color']);
						update_option('bldm_listings_avail_color', $bldm_listings_avail_color);
					}
					if(isset($_POST['bldm_listings_avail_bg'])){
						$bldm_listings_avail_bg = sanitize_text_field($_POST['bldm_listings_avail_bg']);
						update_option('bldm_listings_avail_bg', $bldm_listings_avail_bg);
					}
					
					if(isset($_POST['bldm_listings_display_ttl'])){
						$bldm_listings_display_ttl = filter_var($_POST['bldm_listings_display_ttl'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_ttl == 'on'){
							update_option('bldm_listings_display_ttl', 'show');
						}
					} else{ update_option('bldm_listings_display_ttl', 'hide'); }
					if(isset($_POST['bldm_listings_ttl_tag'])){
						$bldm_listings_ttl_tag = sanitize_text_field($_POST['bldm_listings_ttl_tag']);
						update_option('bldm_listings_ttl_tag', $bldm_listings_ttl_tag);
					}
					
					if(isset($_POST['bldm_listings_display_address'])){
						$bldm_listings_display_address = filter_var($_POST['bldm_listings_display_address'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_address == 'on'){
							update_option('bldm_listings_display_address', 'show');
						}
					} else{ update_option('bldm_listings_display_address', 'hide'); }
					if(isset($_POST['bldm_listings_address_tag'])){
						$bldm_listings_address_tag = sanitize_text_field($_POST['bldm_listings_address_tag']);
						update_option('bldm_listings_address_tag', $bldm_listings_address_tag);
					}
					
					if(isset($_POST['bldm_listings_display_beds'])){
						$bldm_listings_display_beds = filter_var($_POST['bldm_listings_display_beds'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_beds == 'on'){
							update_option('bldm_listings_display_beds', 'show');
						}
					} else{ update_option('bldm_listings_display_beds', 'hide'); }
					if(isset($_POST['bldm_listings_bed_img'])){
						$bldm_listings_bed_img = sanitize_text_field($_POST['bldm_listings_bed_img']);
						update_option('bldm_listings_bed_img', $bldm_listings_bed_img);
					}
					
					if(isset($_POST['bldm_listings_display_baths'])){
						$bldm_listings_display_baths = filter_var($_POST['bldm_listings_display_baths'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_baths == 'on'){
							update_option('bldm_listings_display_baths', 'show');
						}
					} else{ update_option('bldm_listings_display_baths', 'hide'); }
					if(isset($_POST['bldm_listings_bath_img'])){
						$bldm_listings_bath_img = sanitize_text_field($_POST['bldm_listings_bath_img']);
						update_option('bldm_listings_bath_img', $bldm_listings_bath_img);
					}
					
					if(isset($_POST['bldm_listings_display_detail'])){
						$bldm_listings_display_detail = filter_var($_POST['bldm_listings_display_detail'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_detail == 'on'){
							update_option('bldm_listings_display_detail', 'show');
						}
					} else{ update_option('bldm_listings_display_detail', 'hide'); }
					if(isset($_POST['bldm_listings_detail_color'])){
						$bldm_listings_detail_color = sanitize_text_field($_POST['bldm_listings_detail_color']);
						update_option('bldm_listings_detail_color', $bldm_listings_detail_color);
					}
					if(isset($_POST['bldm_listings_detail_bg'])){
						$bldm_listings_detail_bg = sanitize_text_field($_POST['bldm_listings_detail_bg']);
						update_option('bldm_listings_detail_bg', $bldm_listings_detail_bg);
					}
					if(isset($_POST['bldm_listings_detail_hover_color'])){
						$bldm_listings_detail_hover_color = sanitize_text_field($_POST['bldm_listings_detail_hover_color']);
						update_option('bldm_listings_detail_hover_color', $bldm_listings_detail_hover_color);
					}
					if(isset($_POST['bldm_listings_detail_hover_bg'])){
						$bldm_listings_detail_hover_bg = sanitize_text_field($_POST['bldm_listings_detail_hover_bg']);
						update_option('bldm_listings_detail_hover_bg', $bldm_listings_detail_hover_bg);
					}
					
					if(isset($_POST['bldm_listings_display_apply'])){
						$bldm_listings_display_apply = filter_var($_POST['bldm_listings_display_apply'], FILTER_SANITIZE_STRING);
						if($bldm_listings_display_apply == 'on'){
							update_option('bldm_listings_display_apply', 'show');
						}
					} else{ update_option('bldm_listings_display_apply', 'hide'); }
					if(isset($_POST['bldm_listings_apply_color'])){
						$bldm_listings_apply_color = sanitize_text_field($_POST['bldm_listings_apply_color']);
						update_option('bldm_listings_apply_color', $bldm_listings_apply_color);
					}
					if(isset($_POST['bldm_listings_apply_bg'])){
						$bldm_listings_apply_bg = sanitize_text_field($_POST['bldm_listings_apply_bg']);
						update_option('bldm_listings_apply_bg', $bldm_listings_apply_bg);
					}
					if(isset($_POST['bldm_listings_apply_hover_color'])){
						$bldm_listings_apply_hover_color = sanitize_text_field($_POST['bldm_listings_apply_hover_color']);
						update_option('bldm_listings_apply_hover_color', $bldm_listings_apply_hover_color);
					}
					if(isset($_POST['bldm_listings_apply_hover_bg'])){
						$bldm_listings_apply_hover_bg = sanitize_text_field($_POST['bldm_listings_apply_hover_bg']);
						update_option('bldm_listings_apply_hover_bg', $bldm_listings_apply_hover_bg);
					}
					
					// Saved message
					echo '<div class="notice notice-success is-dismissible"><p>Settings Updated!</p></div>';
				}
			}
			?>
			<div class="wrap">
				<div id="bldm-pro-customizer">
					<form method='POST' action="">
						<br>
						<h1>Buildium Listings Customizer</h1>
						<table class="form-table">
							
							<tr>
								<th>
									<?php $bldm_page_hdng = get_option('bldm_page_hdng'); ?>
									<label for="bldm_page_hdng">Listings Page Heading<br>(You can use html)</label>
								</th>
								<td>
									<input type="text" name="bldm_page_hdng" id="bldm_page_hdng" style="min-width: 350px;" placeholder="e.g. <h2>Find a Property for Rent</h2>" value="<?php echo $bldm_page_hdng; ?>">
								</td>
							</tr>

							<tr>
									<th>
										<label>Heading banner</label>
									</th>
									<td>
										<span>
											<?php
											$bldm_listings_banner_bg = get_option('bldm_listings_banner_bg');
											if( !$bldm_listings_banner_bg ){
												$bldm_listings_banner_bg = '#17506a';
											}
											
											?>
											Background color:
											<input type="text" name="bldm_listings_banner_bg"
												value="<?php echo esc_attr($bldm_listings_banner_bg); ?>" class="bldm-listings-color" />
										</span>

									</td>
								</tr>

								<tr>
									<th>
										<label>Heading Banner Image<br>(Suggested dimentions 1300x250px)</label>
									</th>
									<td class="bldm_custom_option">
										<?php
										
											$bldm_listings_banner_image = get_option('bldm_listings_banner_image');
											if(!$bldm_listings_banner_image){
												$bldm_listings_banner_image = 'hide';
											}
										
										?>
										<span><input type="checkbox" name="bldm_listings_banner_image" id="bldm_listings_banner_image"
												<?php echo ($bldm_listings_banner_image == 'show') ? 'checked' : ''; ?>>
											Use image</span>
										<span id="bldm_banner_image" class="bldm-adm-more-options"
											style="<?php echo ($bldm_listings_banner_image == 'show') ? '' : 'display:none'; ?>">
											<?php

											$bldm_listings_banner_image_url = get_option('bldm_listings_banner_image_url');
											if ($bldm_listings_banner_image_url) {
												?>
												<img src="<?php echo $bldm_listings_banner_image_url; ?>" alt="Banner Image Preview"
													id="bldm-banner-image-preview" style=" display: block;" />
												<div id="bldm-remove-banner-image" class="bldm-banner-button"
													file-src="<?php echo $bldm_listings_banner_image_url; ?>">Remove</div>
											<?php } else { ?>
												<img src="" alt="Banner Image Preview" id="bldm-banner-image-preview"
													style=" display: none;" />
												<div id="bldm-remove-banner-image" class="bldm-banner-button" style=" display: none; ">Remove</div>
											<?php } ?>

											<input type="file" name="bldm_listings_banner_image_upload"
												id="bldm_listings_banner_image_upload" accept="image/*">
											<div id="bldm-upload-banner-image" class="bldm-banner-button"
												>Upload</div>
											<div id="bldm-upload-msg"></div>

										</span>
									</td>
								</tr>

								<tr>
									<th>
										<?php
										
											$bldm_page_sub_hdng = get_option('bldm_page_sub_hdng');
											if(!$bldm_page_sub_hdng ){
												$bldm_page_sub_hdng = '';
											}
										
										?>
										<label for="bldm_page_sub_hdng">Listings Page Sub Heading<br />(Do not use html)</label>
									</th>
									<td>
										<input type="text" name="bldm_page_sub_hdng" id="bldm_page_sub_hdng" style="min-width: 350px;"
											placeholder="e.g. Find a Property for Rent"
											value="<?php echo strip_tags($bldm_page_sub_hdng); ?>">
									</td>
								</tr>

								<tr>
									<th>
										<label>Sub Heading</label>
									</th>
									<td class="bldm_custom_option bldm_sub_heading_option">
										<span>
											<?php
											
												$bldm_listings_banner_heading_font_size = get_option('bldm_listings_banner_heading_font_size');
												if(!$bldm_listings_banner_heading_font_size) {
													$bldm_listings_banner_heading_font_size = '50px';
												}
											
											?>
											Font Size:
											<input type="text" name="bldm_listings_banner_heading_font_size"
												value="<?php echo esc_attr($bldm_listings_banner_heading_font_size); ?>" />
										</span>
										<span>
											<?php
											
												$bldm_listings_banner_heading_font_weight = get_option('bldm_listings_banner_heading_font_weight');
												if(! $bldm_listings_banner_heading_font_weight) {
													$bldm_listings_banner_heading_font_weight = '400';
												}
											
											?>
											Font weight:
											<select name="bldm_listings_banner_heading_font_weight">
												<?php
												$font_weight_options = array(
													'100',
													'200',
													'300',
													'400',
													'500',
													'600',
													'700',
													'800',
													'900',
													'bold',
													'bolder',
													'lighter',
													'normal'
												);

												foreach ($font_weight_options as $value) {
													$selected = ($bldm_listings_banner_heading_font_weight == $value) ? 'selected' : '';
													echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
												}
												?>
											</select>
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_color = get_option('bldm_listings_banner_heading_color');
												if(!$bldm_listings_banner_heading_color) {
													$bldm_listings_banner_heading_color = '400';
												}
											
											?>
											Font color:
											<input type="text" name="bldm_listings_banner_heading_color"
												value="<?php echo esc_attr($bldm_listings_banner_heading_color); ?>"
												class="bldm-listings-color" />
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_line_height = get_option('bldm_listings_banner_heading_line_height');
												if(!$bldm_listings_banner_heading_line_height) {
													$bldm_listings_banner_heading_line_height = '1';
												}
											
											?>
											Line height:
											<input type="text" name="bldm_listings_banner_heading_line_height"
												value="<?php echo esc_attr($bldm_listings_banner_heading_line_height); ?>" />
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_text_transform = get_option('bldm_listings_banner_heading_text_transform');
												if(!$bldm_listings_banner_heading_text_transform){
													$bldm_listings_banner_heading_text_transform = 'uppercase';
												}
											
											?>
											Text transform:
											<select name="bldm_listings_banner_heading_text_transform">
												<?php
												$text_transform_options = array(
													'capitalize',
													'lowercase',
													'uppercase',
													'none',
													'math-auto'
												);

												foreach ($text_transform_options as $value) {
													$selected = ($bldm_listings_banner_heading_text_transform == $value) ? 'selected' : '';
													echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
												}
												?>
											</select>
										</span>
									</td>
								</tr>
								<tr>
									<th></th>
									<td class="bldm_custom_option bldm_sub_heading_option">
										<span>
											<?php
											
												$bldm_listings_banner_heading_text_align = get_option('bldm_listings_banner_heading_text_align');
												if(!$bldm_listings_banner_heading_text_align){
													$bldm_listings_banner_heading_text_align = 'center';
												}
											
											?>
											Text align:
											<input type="text" name="bldm_listings_banner_heading_text_align"
												value="<?php echo esc_attr($bldm_listings_banner_heading_text_align); ?>" />
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_padding_top = get_option('bldm_listings_banner_heading_padding_top');
												if(!$bldm_listings_banner_heading_padding_top){
													$bldm_listings_banner_heading_padding_top = '0px';
												}
											
											?>
											Padding top:
											<input type="text" name="bldm_listings_banner_heading_padding_top"
												value="<?php echo esc_attr($bldm_listings_banner_heading_padding_top); ?>" />
										</span>


										<span>
											<?php
											
												$bldm_listings_banner_heading_padding_bottom = get_option('bldm_listings_banner_heading_padding_bottom');
												if(!$bldm_listings_banner_heading_padding_bottom ){
													$bldm_listings_banner_heading_padding_bottom = '0px';
												}
											
											?>
											Padding bottom:
											<input type="text" name="bldm_listings_banner_heading_padding_bottom"
												value="<?php echo esc_attr($bldm_listings_banner_heading_padding_bottom); ?>" />
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_padding_left = get_option('bldm_listings_banner_heading_padding_left');
												if(!$bldm_listings_banner_heading_padding_left){
													$bldm_listings_banner_heading_padding_left = '0px';
												}
											
											?>
											Padding left:
											<input type="text" name="bldm_listings_banner_heading_padding_left"
												value="<?php echo esc_attr($bldm_listings_banner_heading_padding_left); ?>" />
										</span>

										<span>
											<?php
											
												$bldm_listings_banner_heading_padding_right = get_option('bldm_listings_banner_heading_padding_right');
												if(!$bldm_listings_banner_heading_padding_right){
													$bldm_listings_banner_heading_padding_right = '0px';
												}
											

											?>
											Padding right:
											<input type="text" name="bldm_listings_banner_heading_padding_right"
												value="<?php echo esc_attr($bldm_listings_banner_heading_padding_right); ?>" />
										</span>
									</td>
								</tr>
							
							<tr>
								<th>
									<label>Display filters</label>
								</th>
								<td class="bldm-admin-fltrs">
									<?php $bldm_filters_zip = get_option('bldm_filters_zip'); ?>
									<?php $bldm_filters_minrent = get_option('bldm_filters_minrent'); ?>
									<?php $bldm_filters_maxrent = get_option('bldm_filters_maxrent'); ?>
									<?php $bldm_filters_bed = get_option('bldm_filters_bed'); ?>
									<?php $bldm_filters_bath = get_option('bldm_filters_bath'); ?>
									<?php $bldm_filters_type = get_option('bldm_filters_type'); ?>
									<span><input type="checkbox" name="bldm_pro_zip_filter" id="bldm_pro_zip_filter" <?php echo ($bldm_filters_zip == 'show')?'checked':''; ?>>Zip </span>
									<span><input type="checkbox" name="bldm_pro_minrent_filter" id="bldm_pro_minrent_filter" <?php echo ($bldm_filters_minrent == 'show')?'checked':''; ?>>Min Rent </span>
									<span><input type="checkbox" name="bldm_pro_maxrent_filter" id="bldm_pro_maxrent_filter" <?php echo ($bldm_filters_maxrent == 'show')?'checked':''; ?>>Max Rent </span>
									<span><input type="checkbox" name="bldm_pro_bed_filter" id="bldm_pro_bed_filter" <?php echo ($bldm_filters_bed == 'show')?'checked':''; ?>>Beds </span>
									<span><input type="checkbox" name="bldm_pro_bath_filter" id="bldm_pro_bath_filter" <?php echo ($bldm_filters_bath == 'show')?'checked':''; ?>>Baths </span>
									<span><input type="checkbox" name="bldm_pro_type_filter" id="bldm_pro_type_filter" <?php echo ($bldm_filters_type == 'show')?'checked':''; ?>>Type </span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Search Button</label>
								</th>
								<td class="bldm_custom_option">
									<span>
										<?php $bldm_listings_search_color = get_option('bldm_listings_search_color');
											if(!$bldm_listings_search_color){ $bldm_listings_search_color = '#ffffff'; }
										?>
										Text Color:
										<input type="text" name="bldm_listings_search_color" value="<?php echo $bldm_listings_search_color; ?>" class="bldm-listings-color" />
									</span>
									<span>
										<?php $bldm_listings_search_bg = get_option('bldm_listings_search_bg'); ?>
										Background: 
										<input type="text" name="bldm_listings_search_bg" value="<?php echo $bldm_listings_search_bg; ?>" class="bldm-listings-color" />
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<?php 
										$bldm_columns_cnt = get_option('bldm_columns_cnt');
										if(!$bldm_columns_cnt){ $bldm_columns_cnt = 3; } // setting default value
									?>
									<label for="bldm_columns_cnt">Listings Page Layout</label>
								</th>
								<td>
									<select name="bldm_columns_cnt" id="bldm_columns_cnt">
										<option value="1" <?php echo ($bldm_columns_cnt == 1)?'selected':''; ?>>1 Column</option>
										<option value="2" <?php echo ($bldm_columns_cnt == 2)?'selected':''; ?>>2 Columns</option>
										<option value="3" <?php echo ($bldm_columns_cnt == 3)?'selected':''; ?>>3 Columns</option>
									</select>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Rent Price</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_price = get_option('bldm_listings_display_price'); ?>
									<span><input type="checkbox" name="bldm_listings_display_price" id="bldm_listings_display_price" <?php echo ($bldm_listings_display_price == 'show')?'checked':''; ?>></span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_price == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_price_pos = get_option('bldm_listings_price_pos'); 
											if(!$bldm_listings_price_pos){ $bldm_listings_price_pos = 'onimage'; }
										?>
										Position: 
										<select name="bldm_listings_price_pos">
											<option value="onimage" <?php echo ($bldm_listings_price_pos == 'onimage')?'selected':''; ?>>On Image</option>
											<option value="offimage" <?php echo ($bldm_listings_price_pos == 'offimage')?'selected':''; ?>>Below Image</option>
										</select>
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_price == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_price_color = get_option('bldm_listings_price_color');
											if(!$bldm_listings_price_color){ $bldm_listings_price_color = '#ffffff'; }
										?>
										Text Color:
										<input type="text" name="bldm_listings_price_color" value="<?php echo $bldm_listings_price_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_price == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_price_bg = get_option('bldm_listings_price_bg'); ?>
										Background: 
										<input type="text" name="bldm_listings_price_bg" value="<?php echo $bldm_listings_price_bg; ?>" class="bldm-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Availability</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_avail = get_option('bldm_listings_display_avail'); ?>
									<span><input type="checkbox" name="bldm_listings_display_avail" id="bldm_listings_display_avail" <?php echo ($bldm_listings_display_avail == 'show')?'checked':''; ?>></span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_avail == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_avail_pos = get_option('bldm_listings_avail_pos'); 
											if(!$bldm_listings_avail_pos){ $bldm_listings_avail_pos = 'onimage'; }
										?>
										Position: 
										<select name="bldm_listings_avail_pos">
											<option value="onimage" <?php echo ($bldm_listings_avail_pos == 'onimage')?'selected':''; ?>>On Image</option>
											<option value="offimage" <?php echo ($bldm_listings_avail_pos == 'offimage')?'selected':''; ?>>Below Image</option>
										</select>
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_avail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_avail_color = get_option('bldm_listings_avail_color');
											if(!$bldm_listings_avail_color){ $bldm_listings_avail_color = '#ffffff'; }
										?>
										Text Color:
										<input type="text" name="bldm_listings_avail_color" value="<?php echo $bldm_listings_avail_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_avail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_avail_bg = get_option('bldm_listings_avail_bg'); ?>
										Background: 
										<input type="text" name="bldm_listings_avail_bg" value="<?php echo $bldm_listings_avail_bg; ?>" class="bldm-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Listing Title</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_ttl = get_option('bldm_listings_display_ttl'); ?>
									<span><input type="checkbox" name="bldm_listings_display_ttl" id="bldm_listings_display_ttl" <?php echo ($bldm_listings_display_ttl == 'show')?'checked':''; ?>></span>
									<span id="ttl_tag" class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_ttl == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_ttl_tag = get_option('bldm_listings_ttl_tag'); 
											if(!$bldm_listings_ttl_tag){ $bldm_listings_ttl_tag = 'h2'; }
										?>
										Tag: 
										<select name="bldm_listings_ttl_tag">
											<option value="h1" <?php echo ($bldm_listings_ttl_tag == 'h1')?'selected':''; ?>>h1</option>
											<option value="h2" <?php echo ($bldm_listings_ttl_tag == 'h2')?'selected':''; ?>>h2</option>
											<option value="h3" <?php echo ($bldm_listings_ttl_tag == 'h3')?'selected':''; ?>>h3</option>
											<option value="h4" <?php echo ($bldm_listings_ttl_tag == 'h4')?'selected':''; ?>>h4</option>
											<option value="h5" <?php echo ($bldm_listings_ttl_tag == 'h5')?'selected':''; ?>>h5</option>
											<option value="h6" <?php echo ($bldm_listings_ttl_tag == 'h6')?'selected':''; ?>>h6</option>
											<option value="p" <?php echo ($bldm_listings_ttl_tag == 'p')?'selected':''; ?>>p</option>
										</select>
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Listing Address</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_address = get_option('bldm_listings_display_address'); ?>
									<span><input type="checkbox" name="bldm_listings_display_address" id="bldm_listings_display_address" <?php echo ($bldm_listings_display_address == 'show')?'checked':''; ?>></span>
									<span id="address_tag" class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_address == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_address_tag = get_option('bldm_listings_address_tag'); 
											if(!$bldm_listings_address_tag){ $bldm_listings_address_tag = 'h3'; }
										?>
										Tag: 
										<select name="bldm_listings_address_tag">
											<option value="h1" <?php echo ($bldm_listings_address_tag == 'h1')?'selected':''; ?>>h1</option>
											<option value="h2" <?php echo ($bldm_listings_address_tag == 'h2')?'selected':''; ?>>h2</option>
											<option value="h3" <?php echo ($bldm_listings_address_tag == 'h3')?'selected':''; ?>>h3</option>
											<option value="h4" <?php echo ($bldm_listings_address_tag == 'h4')?'selected':''; ?>>h4</option>
											<option value="h5" <?php echo ($bldm_listings_address_tag == 'h5')?'selected':''; ?>>h5</option>
											<option value="h6" <?php echo ($bldm_listings_address_tag == 'h6')?'selected':''; ?>>h6</option>
											<option value="p" <?php echo ($bldm_listings_address_tag == 'p')?'selected':''; ?>>p</option>
										</select>
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Beds</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_beds = get_option('bldm_listings_display_beds'); ?>
									<span><input type="checkbox" name="bldm_listings_display_beds" id="bldm_listings_display_beds" <?php echo ($bldm_listings_display_beds == 'show')?'checked':''; ?>></span>
									<span id="bed_img" class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_beds == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_bed_img = get_option('bldm_listings_bed_img'); ?>
										Image URL: 
										<input type="text" name="bldm_listings_bed_img" id="bldm_listings_bed_img" style="width: 450px" value="<?php echo ($bldm_listings_bed_img)?$bldm_listings_bed_img:''; ?>" placeholder="Leave blank to hide image">
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Baths</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_baths = get_option('bldm_listings_display_baths'); ?>
									<span><input type="checkbox" name="bldm_listings_display_baths" id="bldm_listings_display_baths" <?php echo ($bldm_listings_display_baths == 'show')?'checked':''; ?>></span>
									<span id="bath_img" class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_baths == 'show')?'':'display:none'; ?>">
										<?php $bldm_listings_bath_img = get_option('bldm_listings_bath_img'); ?>
										Image URL: 
										<input type="text" name="bldm_listings_bath_img" id="bldm_listings_bath_img" style="width: 450px" value="<?php echo ($bldm_listings_bath_img)?$bldm_listings_bath_img:''; ?>" placeholder="Leave blank to hide image">
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Details Button</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_detail = get_option('bldm_listings_display_detail'); ?>
									<span><input type="checkbox" name="bldm_listings_display_detail" id="bldm_listings_display_detail" <?php echo ($bldm_listings_display_detail == 'show')?'checked':''; ?>></span>
									
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_detail_color = get_option('bldm_listings_detail_color');
											if(!$bldm_listings_detail_color){ $bldm_listings_detail_color = '#ffffff'; }
										?>
										Text Color:
										<input type="text" name="bldm_listings_detail_color" value="<?php echo $bldm_listings_detail_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_detail_bg = get_option('bldm_listings_detail_bg'); ?>
										Background: 
										<input type="text" name="bldm_listings_detail_bg" value="<?php echo $bldm_listings_detail_bg; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_detail_hover_color = get_option('bldm_listings_detail_hover_color');
											if(!$bldm_listings_detail_hover_color){ $bldm_listings_detail_hover_color = '#ffffff'; }
										?>
										Hover Text Color:
										<input type="text" name="bldm_listings_detail_hover_color" value="<?php echo $bldm_listings_detail_hover_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_detail_hover_bg = get_option('bldm_listings_detail_hover_bg'); ?>
										Hover Background: 
										<input type="text" name="bldm_listings_detail_hover_bg" value="<?php echo $bldm_listings_detail_hover_bg; ?>" class="bldm-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Apply Button</label>
								</th>
								<td class="bldm_custom_option">
									<?php $bldm_listings_display_apply = get_option('bldm_listings_display_apply'); ?>
									<span><input type="checkbox" name="bldm_listings_display_apply" id="bldm_listings_display_apply" <?php echo ($bldm_listings_display_apply == 'show')?'checked':''; ?>></span>
									
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_apply == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_apply_color = get_option('bldm_listings_apply_color');
											if(!$bldm_listings_apply_color){ $bldm_listings_apply_color = '#ffffff'; }
										?>
										Text Color:
										<input type="text" name="bldm_listings_apply_color" value="<?php echo $bldm_listings_apply_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_apply == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_apply_bg = get_option('bldm_listings_apply_bg'); ?>
										Background: 
										<input type="text" name="bldm_listings_apply_bg" value="<?php echo $bldm_listings_apply_bg; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_apply_hover_color = get_option('bldm_listings_apply_hover_color');
											if(!$bldm_listings_apply_hover_color){ $bldm_listings_apply_hover_color = '#ffffff'; }
										?>
										Hover Text Color:
										<input type="text" name="bldm_listings_apply_hover_color" value="<?php echo $bldm_listings_apply_hover_color; ?>" class="bldm-listings-color" />
									</span>
									<span class="bldm-adm-more-options" style="<?php echo ($bldm_listings_display_detail == 'show')?'':'display:none'; ?>" >
										<?php $bldm_listings_apply_hover_bg = get_option('bldm_listings_apply_hover_bg'); ?>
										Hover Background: 
										<input type="text" name="bldm_listings_apply_hover_bg" value="<?php echo $bldm_listings_apply_hover_bg; ?>" class="bldm-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<?php $bldm_custom_apply_lnk = get_option('bldm_custom_apply_lnk'); ?>
									<label for="bldm_custom_apply_lnk">Custom Apply Link<br>(Leave blank for default link)</label>
								</th>
								<td>
									<input type="text" name="bldm_custom_apply_lnk" id="bldm_custom_apply_lnk" style="min-width: 350px;" placeholder="please use complete URL including http or https" value="<?php echo $bldm_custom_apply_lnk; ?>">
								</td>
							</tr>
							
						</table>
						
						<p class="submit"><input type="submit" name="bldm_cstmzr_sbmt" value="Save" class="button-primary"></p>
						
					</form>
				</div>
			</div>
	<?php
		}
	}
	
}
