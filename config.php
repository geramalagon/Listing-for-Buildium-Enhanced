<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

// To override the apply link
add_filter('bldm_apply_btn_link', function($current_listing_Apply_Link){
	// Hardcoded link - replace with your desired URL
	return 'https://rentbutter.com/apply/marblestone';
});

// To override the listings page heading
add_filter('bldm_page_hdng', function($current_page_hdng){
	$bldm_custom_page_hdng = get_option('bldm_page_hdng');
	if($bldm_custom_page_hdng){
		return $bldm_custom_page_hdng;
	} else{
		return $current_page_hdng;
	}
});

// Plugin auto update info
add_filter('plugins_api', 'bldm_pro_plugin_info', 20, 3);
function bldm_pro_plugin_info( $res, $action, $args ){
	if( 'plugin_information' !== $action ) {
		return $res;
	}
	$plugin_slug = 'listings-for-buildium-pro';
	
	if( $plugin_slug !== $args->slug ) {
		return $res;
	}
 
	// trying to get from cache first
	if( false == $remote = get_transient( 'bldm_pro_update_' . $plugin_slug ) ) {
		$remote = wp_remote_get( 'https://listingsforbuildium.com/wp-content/uploads/bldm-updates/latest.json', array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) )
		);
 
		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			set_transient( 'bldm_pro_update_' . $plugin_slug, $remote, 43200 ); // 12 hours cache
		}
 
	}
 
	if( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
		$plugin_updatable = get_option('bldm_licensed_updatable');
		if($plugin_updatable){
			$remote = json_decode( $remote['body'] );
			$res = new stdClass();
	 
			$res->name = 'Listings for Buildium Pro';
			$res->slug = $plugin_slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = '<a href="https://listingsforbuildium.com">Listings for Buildium</a>';
			$res->author_profile = 'https://listingsforbuildium.com';
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = '7.4';
			$res->last_updated = $remote->last_updated;
			$res->sections = array(
				'description' => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog' => $remote->sections->changelog
				// also can add custom sections (tabs) here
			);
	 
			$res->banners = array(
				'low' => $remote->banners->low
			);
			
			return $res;
		}
	}
 
	return false;
 
}

// Push update info to WP transient
add_filter('site_transient_update_plugins', 'bldm_pro_push_update' );
function bldm_pro_push_update( $transient ){
 
	if ( empty($transient->checked ) ) {
		return $transient;
	}

	if(is_admin()){
		if( false == $remote = get_transient( 'bldm_pro_upgrade_listings-for-buildium-pro' ) ) {
			$remote = wp_remote_get( 'https://listingsforbuildium.com/wp-content/uploads/bldm-updates/latest.json', array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				) )
			);
			if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
				set_transient( 'bldm_pro_upgrade_listings-for-buildium-pro', $remote, 43200 ); // 12 hours cache
			}
		}
		
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_name = $plugin_data['TextDomain'];
	 
		if( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			$plugin_updatable = get_option('bldm_licensed_updatable');
			if($plugin_updatable){
				$remote = json_decode( $remote['body'] );
				// your installed plugin version should be on the line below! You can obtain it dynamically of course 
				if( $remote && version_compare( BLDM_PRO_CURR_VER, $remote->version, '<' ) && version_compare($remote->requires, get_bloginfo('version'), '<' ) ) {
					$res = new stdClass();
					$res->slug = 'listings-for-buildium-pro';
					$res->plugin = $plugin_name . '/buildium-listings.php';
					$res->new_version = $remote->version;
					$res->tested = $remote->tested;
					$res->package = $remote->download_url;
						$transient->response[$res->plugin] = $res;
						//$transient->checked[$res->plugin] = $remote->version;
				}
			}
		}
	}
	return $transient;
}

// After update transient
add_action( 'upgrader_process_complete', 'bldm_pro_after_update', 10, 2 );
function bldm_pro_after_update( $upgrader_object, $options ) {
	if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
		// just clean the cache when new plugin version is installed
		delete_transient( 'bldm_pro_upgrade_listings-for-buildium-pro' );
	}
}
