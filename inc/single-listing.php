<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

if (!function_exists('bldm_pp_display_single_listing')) {
	function bldm_pp_display_single_listing($custom_url){
		global $bldm_plugin_url;
		global $bldm_listings_url;
		
		if(!$bldm_listings_url && !$custom_url){ return '<p>The Buildium URL is blank. Please contact site owner.</p>'; }
		
		if($custom_url){
			$bldm_listings_url = $custom_url;
		}
		
		$apply_btn_link = $contact_btn_link = '';
		$sl_html = '<div class="bldm-sl-wrapper" style="width: 100%; max-width: 100%;">';
		if(isset($_GET['lid'])){
			
			$list_id = sanitize_text_field($_GET['lid']); // sanitize target listing ID number
			
			$last_char = substr($bldm_listings_url, -1);
			if($last_char == '/'){
				$bldm_listings_url = substr($bldm_listings_url, 0, -1);
			}
			
			$url = $bldm_listings_url.'/Resident/public/rentals/' . $list_id . '?hidenav=true';

			$html = file_get_html($url);
			
			$gallery_thumbs = $html->find('.unit-detail__gallery-thumbnails img');
			
			$listing_images = array();
			$i = 0;
			$main_gallery = $html->find('ul.js-gallery', 0);
			if($main_gallery){
				$main_imgs = $main_gallery->find('.js-gallery-item');
				if($main_imgs){
					foreach($main_imgs as $main_img){
						$listing_images[$i]['img_url'] = $main_img->{'data-mfp-src'};
						$i++;
						$sl_html .= '<span data-id="'.$i.'" class="bldm-gall-full-img" style="display: none;" data-src="'.$main_img->{'data-mfp-src'}.'"></span>';
					}
				}
			}
			
			$all_lstng_url = strtok($_SERVER["REQUEST_URI"], '?');
			if($all_lstng_url){
				$sl_html .= '<div style="margin-bottom: 2rem;"><a class="bldm-prmry-btn" href="'.$all_lstng_url.'" style="margin-left: 2%;"> << All Listings</a></div>';
			}
			
			$sl_html .='<div class="bldm-listing-sec bldm-section-inner"><div class="bldm-column bldm-two-fifth">';
			if($listing_images){
				$sl_html .='<div class="bldm-gallery">
								<div class="bldm-numbertext">1 / '.count($listing_images).'</div>
								<img src="'.$listing_images[0]["img_url"].'" data-href="'.$listing_images[0]["img_url"].'" data-id="1">
							</div>';
					$sl_html .='<div class="bldm-row" style="margin-top: 7px;">';
					foreach($gallery_thumbs as $thumb){
						$data_index = (int)$thumb->{'data-index'};
						$data_index = $data_index + 1;
						$sl_html .='<div class="bldm-imgcolumn">
										<img class="bldm-img-thumb bldm-cursor" src="'.$thumb->{'src'}.'" data-id="' . $data_index .'">
									</div>';
					}
				$sl_html .='</div></div>';
			}
			$sl_html .='</div>';
			
			// Get other details
			$list_ttl_obj = $html->find('h1.title', 0);
			if($list_ttl_obj){
				$list_ttl = $list_ttl_obj->innertext;
			}
			
			$list_info = $html->find('.unit-detail__info', 0);
			if($list_info){
				
				$sl_html .= '<div class="bldm-column bldm-three-fifth">';
				
				$price_obj = $list_info->find('.unit-detail__price', 0);
				if($price_obj){
					$price = $price_obj->innertext;
					$price_obj->outertext = '';
				}
				
				$avl_obj = $list_info->find('.unit-detail__available-date', 0);
				if($avl_obj){
					$avl = $avl_obj->innertext;
					$avl_obj->outertext = '';
				}
				
				$space_obj = $list_info->find('ul.unit-detail__unit-info', 0);
				if($space_obj){
					$space = $space_obj->innertext; // list items
					$space_obj->outertext = '';
				}
				
				// Req Info btn
				$req_info_obj = $list_info->find('.unit-detail__actions .btn--primary', 0);
				if($req_info_obj){
					$curr_link = $req_info_obj->{'href'};
					$req_info_obj->{'href'} = $bldm_listings_url.$curr_link;
					$req_info_obj->{'target'} = '_blank';
				}
				
				// Apply btn
				$apply_obj = $list_info->find('.unit-detail__actions .btn--secondary', 0);
				if($apply_obj){
					$curr_link = $apply_obj->{'href'};
					$apply_btn_link = apply_filters( "bldm_apply_btn_link", $curr_link, $curr_link );
					$apply_obj->{'href'} = $apply_btn_link;
					$apply_obj->{'target'} = '_blank';
				}
				
				$desc_html = $list_info->innertext;
				$sl_html .= '<div class="bldm-lst-dtls">
								<div class="bldm-details-left">
									<h3 class="bldm-address-hdng">' . $list_ttl . '</h3>
									<ul class="bldm-bed-bath-std">' . $space . '</ul>
								</div>
								<div class="bldm-details-right">
									<p class="bldm-rent-hdng"><img class="bldm-price-tag" src="'.$bldm_plugin_url.'images/dollar-tag.png">'.$price.'</p>
									<p style="margin-bottom: 1rem;"><img class="bldm-avail-now" src="'.$bldm_plugin_url.'images/check.png"><span id="bldm-avail-txt">'.$avl.'</span></p>
								</div>
							</div>';
				
				$sl_html .= $desc_html;
				
			}
			
			$sl_html .= '</div>';
			
		}
		
	$sl_html .='</div>';

	return $sl_html;

	}
}
