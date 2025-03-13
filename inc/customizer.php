<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

function bldm_customizer_css(){
	$bldm_columns_cnt = get_option('bldm_columns_cnt');
	echo '<style>';
		echo '@media only screen and (min-width: 768px) {';
			if($bldm_columns_cnt == 2){
				echo '.bldm-main-listings-page .bldm-all-listings{
						margin: auto;
					}
					.bldm-main-listings-page .bldm-listing-item{
						width: 49%;
					}
					.bldm-main-listings-page .bldm-listing-item #bldm-list-img img{
						height: 450px;
					}';
			} elseif($bldm_columns_cnt == 1){
				echo '.bldm-main-listings-page .bldm-all-listings{
						margin: auto;
					}
					.bldm-main-listings-page .bldm-listing-item{
						width: 100%;
						margin-left: 0;
						margin-right: 0;
						display: flex;
						flex-wrap: wrap;
						align-items: normal;
					}
					.bldm-main-listings-page .bldm-listing-item > a{
						display: block;
						width: 40%;
					}
					.bldm-main-listings-page .bldm-listing-item .bldm-details{
						width: 60%;
						margin-top: 0;
					}
					.bldm-main-listings-page .bldm-listing-item #bldm-list-img img{
						height: 450px;
					}';
			}
		echo '}';
		$bldm_listings_banner_bg = get_option('bldm_listings_banner_bg', '#17506a');
		if ($bldm_listings_banner_bg) {
			echo '.bldm-main-listings-page .bldm-listing-filters {
					background-color: ' . $bldm_listings_banner_bg . ' !important;
				}
				
				
				input,
	.site textarea,
	select
	 {
		border: 1px solid black !important;
	}';
		}

		$bldm_listings_banner_heading_font_size = get_option('bldm_listings_banner_heading_font_size', '50px');
		$bldm_listings_banner_heading_font_weight = get_option('bldm_listings_banner_heading_font_weight', '400');
		$bldm_listings_banner_heading_color = get_option('bldm_listings_banner_heading_color', '#fff');
		$bldm_listings_banner_heading_line_height = get_option('bldm_listings_banner_heading_line_height', '1');
		$bldm_listings_banner_heading_text_transform = get_option('bldm_listings_banner_heading_text_transform', 'uppercase');
		$bldm_listings_banner_heading_text_align = get_option('bldm_listings_banner_heading_text_align', 'center');
		$bldm_listings_banner_heading_padding_top = get_option('bldm_listings_banner_heading_padding_top', '0px');
		$bldm_listings_banner_heading_padding_bottom = get_option('bldm_listings_banner_heading_padding_bottom', '0px');
		$bldm_listings_banner_heading_padding_left = get_option('bldm_listings_banner_heading_padding_left', '0px');
		$bldm_listings_banner_heading_padding_right = get_option('bldm_listings_banner_heading_padding_right', '0px');

		echo '.bldm-main-listings-page .bldm-listing-filters .bldm_page_sub_hdng {';

			// Font Size
			if ($bldm_listings_banner_heading_font_size) {
				echo 'font-size: ' . $bldm_listings_banner_heading_font_size . ';';
			}
		
			// Font Weight
			if ($bldm_listings_banner_heading_font_weight) {
				echo 'font-weight: ' . $bldm_listings_banner_heading_font_weight . ';';
			}
		
			// Color
			if ($bldm_listings_banner_heading_color) {
				echo 'color: ' . $bldm_listings_banner_heading_color . ';';
			}
		
			// Line Height
			if ($bldm_listings_banner_heading_line_height) {
				echo 'line-height: ' . $bldm_listings_banner_heading_line_height . ';';
			}
		
			// Text Transform
			if ($bldm_listings_banner_heading_text_transform) {
				echo 'text-transform: ' . $bldm_listings_banner_heading_text_transform . ';';
			}
		
			// Text Align
			if ($bldm_listings_banner_heading_text_align) {
				echo 'text-align: ' . $bldm_listings_banner_heading_text_align . ';';
			}
		
			// Padding Top
			if ($bldm_listings_banner_heading_padding_top) {
				echo 'padding-top: ' . $bldm_listings_banner_heading_padding_top . ';';
			}
		
			// Padding Bottom
			if ($bldm_listings_banner_heading_padding_bottom) {
				echo 'padding-bottom: ' . $bldm_listings_banner_heading_padding_bottom . ';';
			}
		
			// Padding Left
			if ($bldm_listings_banner_heading_padding_left) {
				echo 'padding-left: ' . $bldm_listings_banner_heading_padding_left . ';';
			}
		
			// Padding Right
			if ($bldm_listings_banner_heading_padding_right) {
				echo 'padding-right: ' . $bldm_listings_banner_heading_padding_right . ';';
			}
			echo '}';
		
		
		$bldm_listings_search_color = get_option('bldm_listings_search_color');
		if($bldm_listings_search_color){
			echo '.bldm-listing-filters input[type="submit"]{
				color: '.$bldm_listings_search_color.';
			}';
		}
		$bldm_listings_search_bg = get_option('bldm_listings_search_bg');
		if($bldm_listings_search_bg){
			echo '.bldm-listing-filters input[type="submit"]{
				background: '.$bldm_listings_search_bg.';
			}';
		}
		
		$bldm_listings_price_color = get_option('bldm_listings_price_color');
		if($bldm_listings_price_color){
			echo '.bldm-main-listings-page .bldm-listing-item span.bldm-rent-price, .bldm-main-listings-page .bldm-listing-item span.bldm-rent-price-off{
				color: '.$bldm_listings_price_color.';
			}';
		}
		$bldm_listings_price_bg = get_option('bldm_listings_price_bg');
		if($bldm_listings_price_bg){
			echo '.bldm-main-listings-page .bldm-listing-item span.bldm-rent-price, .bldm-main-listings-page .bldm-listing-item span.bldm-rent-price-off{
				background: '.$bldm_listings_price_bg.';
			}';
		}
		
		$bldm_listings_avail_color = get_option('bldm_listings_avail_color');
		if($bldm_listings_avail_color){
			echo '.bldm-main-listings-page .bldm-listing-item span.bldm-lstng-avail, .bldm-main-listings-page .bldm-listing-item span.bldm-lstng-avail-off{
				color: '.$bldm_listings_avail_color.';
			}';
		}
		$bldm_listings_avail_bg = get_option('bldm_listings_avail_bg');
		if($bldm_listings_avail_bg){
			echo '.bldm-main-listings-page .bldm-listing-item span.bldm-lstng-avail, .bldm-main-listings-page .bldm-listing-item span.bldm-lstng-avail-off{
				background: '.$bldm_listings_avail_bg.';
			}';
		}
		
		$bldm_listings_detail_color = get_option('bldm_listings_detail_color');
		if($bldm_listings_detail_color){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_more_detail_btn{
				color: '.$bldm_listings_detail_color.';
			}';
		}
		$bldm_listings_detail_bg = get_option('bldm_listings_detail_bg');
		if($bldm_listings_detail_bg){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_more_detail_btn{
				background: '.$bldm_listings_detail_bg.';
			}';
		}
		$bldm_listings_detail_hover_color = get_option('bldm_listings_detail_hover_color');
		if($bldm_listings_detail_hover_color){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_more_detail_btn:hover{
				color: '.$bldm_listings_detail_hover_color.';
			}';
		}
		$bldm_listings_detail_hover_bg = get_option('bldm_listings_detail_hover_bg');
		if($bldm_listings_detail_hover_bg){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_more_detail_btn:hover{
				background: '.$bldm_listings_detail_hover_bg.';
			}';
		}
		
		$bldm_listings_apply_color = get_option('bldm_listings_apply_color');
		if($bldm_listings_apply_color){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_apply_btn{
				color: '.$bldm_listings_apply_color.';
			}';
		}
		$bldm_listings_apply_bg = get_option('bldm_listings_apply_bg');
		if($bldm_listings_apply_bg){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_apply_btn{
				background: '.$bldm_listings_apply_bg.';
			}';
		}
		$bldm_listings_apply_hover_color = get_option('bldm_listings_apply_hover_color');
		if($bldm_listings_apply_hover_color){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_apply_btn:hover{
				color: '.$bldm_listings_apply_hover_color.';
			}';
		}
		$bldm_listings_apply_hover_bg = get_option('bldm_listings_apply_hover_bg');
		if($bldm_listings_apply_hover_bg){
			echo '.bldm-main-listings-page .bldm-listing-item .bldm-btns .bldm_apply_btn:hover{
				background: '.$bldm_listings_apply_hover_bg.';
			}';
		}
		
		
	echo '</style>';
}
add_action('wp_head', 'bldm_customizer_css');
