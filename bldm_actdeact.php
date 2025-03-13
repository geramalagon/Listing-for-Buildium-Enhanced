<?php
// Handle plugin activation to save default option values
class Bldm_Actdeact{
	static function bldm_plugin_activate(){
		$bldm_columns_cnt = get_option('bldm_columns_cnt');
		if(!$bldm_columns_cnt){
			update_option('bldm_columns_cnt', '3');
		}

		$bldm_listings_banner_bg = get_option('bldm_listings_banner_bg');
		if( !$bldm_listings_banner_bg ){
			$bldm_listings_banner_bg = '#17506a';
		}

		$bldm_listings_banner_image = get_option('bldm_listings_banner_image');
		if(!$bldm_listings_banner_image){
			$bldm_listings_banner_image = 'hide';
		}

		$bldm_page_sub_hdng = get_option('bldm_page_sub_hdng');
		if(!$bldm_page_sub_hdng ){
			$bldm_page_sub_hdng = '';
		}

		$bldm_listings_banner_heading_font_size = get_option('bldm_listings_banner_heading_font_size');
		if(!$bldm_listings_banner_heading_font_size) {
			$bldm_listings_banner_heading_font_size = '50px';
		}
		$bldm_listings_banner_heading_font_weight = get_option('bldm_listings_banner_heading_font_weight');
		if(! $bldm_listings_banner_heading_font_weight) {
			$bldm_listings_banner_heading_font_weight = '400';
		}
		$bldm_listings_banner_heading_color = get_option('bldm_listings_banner_heading_color');
		if(!$bldm_listings_banner_heading_color) {
			$bldm_listings_banner_heading_color = '400';
		}
		$bldm_listings_banner_heading_line_height = get_option('bldm_listings_banner_heading_line_height');
		if(!$bldm_listings_banner_heading_line_height) {
			$bldm_listings_banner_heading_line_height = '1';
		}
		$bldm_listings_banner_heading_text_transform = get_option('bldm_listings_banner_heading_text_transform');
		if(!$bldm_listings_banner_heading_text_transform){
			$bldm_listings_banner_heading_text_transform = 'uppercase';
		}
		$bldm_listings_banner_heading_text_align = get_option('bldm_listings_banner_heading_text_align');
		if(!$bldm_listings_banner_heading_text_align){
			$bldm_listings_banner_heading_text_align = 'center';
		}
		$bldm_listings_banner_heading_padding_top = get_option('bldm_listings_banner_heading_padding_top');
		if(!$bldm_listings_banner_heading_padding_top){
			$bldm_listings_banner_heading_padding_top = '0px';
		}
		$bldm_listings_banner_heading_padding_bottom = get_option('bldm_listings_banner_heading_padding_bottom');
		if(!$bldm_listings_banner_heading_padding_bottom ){
			$bldm_listings_banner_heading_padding_bottom = '0px';
		}
		$bldm_listings_banner_heading_padding_left = get_option('bldm_listings_banner_heading_padding_left');
		if(!$bldm_listings_banner_heading_padding_left){
			$bldm_listings_banner_heading_padding_left = '0px';
		}
		$bldm_listings_banner_heading_padding_right = get_option('bldm_listings_banner_heading_padding_right');
		if(!$bldm_listings_banner_heading_padding_right){
			$bldm_listings_banner_heading_padding_right = '0px';
		}
		
		$bldm_filters_minrent = get_option('bldm_filters_minrent');
		if(!$bldm_filters_minrent){
			update_option('bldm_filters_minrent', 'show');
		}
		$bldm_filters_maxrent = get_option('bldm_filters_maxrent');
		if(!$bldm_filters_maxrent){
			update_option('bldm_filters_maxrent', 'show');
		}
		$bldm_filters_bed = get_option('bldm_filters_bed');
		if(!$bldm_filters_bed){
			update_option('bldm_filters_bed', 'show');
		}
		$bldm_filters_bath = get_option('bldm_filters_bath');
		if(!$bldm_filters_bath){
			update_option('bldm_filters_bath', 'show');
		}
		$bldm_filters_zip = get_option('bldm_filters_zip');
		if(!$bldm_filters_zip){
			update_option('bldm_filters_zip', 'show');
		}
		$bldm_filters_type = get_option('bldm_filters_type');
		if(!$bldm_filters_type){
			update_option('bldm_filters_type', 'show');
		}
		
		$bldm_listings_search_color = get_option('bldm_listings_search_color');
		if(!$bldm_listings_search_color){
			update_option('bldm_listings_search_color', '#ffffff');
		}
		$bldm_listings_search_bg = get_option('bldm_listings_search_bg');
		if(!$bldm_listings_search_bg){
			update_option('bldm_listings_search_bg', '#ff6600');
		}
		
		$bldm_listings_display_price = get_option('bldm_listings_display_price');
		if(!$bldm_listings_display_price){
			update_option('bldm_listings_display_price', 'show');
		}
		$bldm_listings_price_pos = get_option('bldm_listings_price_pos');
		if(!$bldm_listings_price_pos){
			update_option('bldm_listings_price_pos', 'onimage');
		}
		$bldm_listings_price_color = get_option('bldm_listings_price_color');
		if(!$bldm_listings_price_color){
			update_option('bldm_listings_price_color', '#ffffff');
		}
		$bldm_listings_price_bg = get_option('bldm_listings_price_bg');
		if(!$bldm_listings_price_bg){
			update_option('bldm_listings_price_bg', '#ff6600');
		}
		
		$bldm_listings_display_avail = get_option('bldm_listings_display_avail');
		if(!$bldm_listings_display_avail){
			update_option('bldm_listings_display_avail', 'show');
		}
		$bldm_listings_avail_pos = get_option('bldm_listings_avail_pos');
		if(!$bldm_listings_avail_pos){
			update_option('bldm_listings_avail_pos', 'onimage');
		}
		$bldm_listings_avail_color = get_option('bldm_listings_avail_color');
		if(!$bldm_listings_avail_color){
			update_option('bldm_listings_avail_color', '#ffffff');
		}
		$bldm_listings_avail_bg = get_option('bldm_listings_avail_bg');
		if(!$bldm_listings_avail_bg){
			update_option('bldm_listings_avail_bg', '#ff6600');
		}
		
		$bldm_listings_display_ttl = get_option('bldm_listings_display_ttl');
		if(!$bldm_listings_display_ttl){
			update_option('bldm_listings_display_ttl', 'show');
		}
		$bldm_listings_ttl_tag = get_option('bldm_listings_ttl_tag');
		if(!$bldm_listings_ttl_tag){
			update_option('bldm_listings_ttl_tag', 'h2');
		}
		$bldm_listings_display_address = get_option('bldm_listings_display_address');
		if(!$bldm_listings_display_address){
			update_option('bldm_listings_display_address', 'show');
		}
		$bldm_listings_address_tag = get_option('bldm_listings_address_tag');
		if(!$bldm_listings_address_tag){
			update_option('bldm_listings_address_tag', 'h3');
		}
		
		$bldm_listings_display_beds = get_option('bldm_listings_display_beds');
		if(!$bldm_listings_display_beds){
			update_option('bldm_listings_display_beds', 'show');
		}
		$bldm_listings_bed_img = get_option('bldm_listings_bed_img');
		if(!$bldm_listings_bed_img){
			update_option('bldm_listings_bed_img', plugin_dir_url( __FILE__ ).'images/sleep.png');
		}
		
		$bldm_listings_display_baths = get_option('bldm_listings_display_baths');
		if(!$bldm_listings_display_baths){
			update_option('bldm_listings_display_baths', 'show');
		}
		$bldm_listings_bath_img = get_option('bldm_listings_bath_img');
		if(!$bldm_listings_bath_img){
			update_option('bldm_listings_bath_img', plugin_dir_url( __FILE__ ).'images/bathtub.png');
		}
		
		$bldm_listings_display_detail = get_option('bldm_listings_display_detail');
		if(!$bldm_listings_display_detail){
			update_option('bldm_listings_display_detail', 'show');
		}
		$bldm_listings_display_apply = get_option('bldm_listings_display_apply');
		if(!$bldm_listings_display_apply){
			update_option('bldm_listings_display_apply', 'show');
		}
		
		$bldm_listings_detail_color = get_option('bldm_listings_detail_color');
		if(!$bldm_listings_detail_color){
			update_option('bldm_listings_detail_color', '#ffffff');
		}
		$bldm_listings_detail_bg = get_option('bldm_listings_detail_bg');
		if(!$bldm_listings_detail_bg){
			update_option('bldm_listings_detail_bg', '#598fcd');
		}
		$bldm_listings_detail_hover_color = get_option('bldm_listings_detail_hover_color');
		if(!$bldm_listings_detail_hover_color){
			update_option('bldm_listings_detail_hover_color', '#ffffff');
		}
		$bldm_listings_detail_hover_bg = get_option('bldm_listings_detail_hover_bg');
		if(!$bldm_listings_detail_hover_bg){
			update_option('bldm_listings_detail_hover_bg', '#444444');
		}
		
		$bldm_listings_apply_color = get_option('bldm_listings_apply_color');
		if(!$bldm_listings_apply_color){
			update_option('bldm_listings_apply_color', '#ffffff');
		}
		$bldm_listings_apply_bg = get_option('bldm_listings_apply_bg');
		if(!$bldm_listings_apply_bg){
			update_option('bldm_listings_apply_bg', '#47a560');
		}
		$bldm_listings_apply_hover_color = get_option('bldm_listings_apply_hover_color');
		if(!$bldm_listings_apply_hover_color){
			update_option('bldm_listings_apply_hover_color', '#ffffff');
		}
		$bldm_listings_apply_hover_bg = get_option('bldm_listings_apply_hover_bg');
		if(!$bldm_listings_apply_hover_bg){
			update_option('bldm_listings_apply_hover_bg', '#444444');
		}
		
	}
	
	static function bldm_plugin_deactivate(){
		// to handle deactivation
	}
	
}