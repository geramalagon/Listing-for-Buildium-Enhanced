<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
	exit;
}

if (!function_exists('bldm_pp_display_all_listings')) {
	function bldm_pp_display_all_listings($atts){
		
		if( !ini_get('allow_url_fopen') ) {
			return '<p>Please enable "allow_url_fopen" from server to make the plugin work correctly.</p>';
		}
		
		$custom_url = '';
		if($atts && isset($atts['url'])){
			$custom_url = $atts['url'];
		}
		
		$render_html = '';
		if(isset($_GET['lid'])){
			$render_html = bldm_pp_display_single_listing($custom_url);
			return $render_html;
		}
		else{
			global $bldm_plugin_url;
			global $bldm_listings_url;
			
			if(!$bldm_listings_url && !$custom_url){ return '<p>The Buildium URL is blank. Please contact site owner.</p>'; }
			
			$render_html .= '<div class="bldm-main-listings-page" style="width: 100%; max-width: 100%;">';
			
			if($custom_url){
				$bldm_listings_url = $custom_url;
			}
			
			$last_char = substr($bldm_listings_url, -1);
			if($last_char == '/'){
				$bldm_listings_url = substr($bldm_listings_url, 0, -1);
			}
			
			$url = $bldm_listings_url.'/Resident/public/rentals?hidenav=true';
			
			if(isset($_POST['fltr-submt'])){
				$params = '';
				if(isset($_POST['filters'])){
					foreach($_POST['filters'] as $fltr_key=>$fltr_val){
						$fltr_key = sanitize_text_field($fltr_key);
						$fltr_val = sanitize_text_field($fltr_val);
						if($fltr_val){
							$params .= '&' . $fltr_key . '=' . urlencode($fltr_val);
						}
					}
				}
				$url = $url.$params;
			}
			
			$html = new simple_html_dom();
			$html->load_file($url);
			$listings = array();
			$db = array();
			$listing_title = '';
			
			$listing_page_hdng = apply_filters( "bldm_page_hdng", '' );
			
			$bldm_listings_banner_image_url = get_option('bldm_listings_banner_image_url', false);
			$bldm_listings_banner_image = get_option('bldm_listings_banner_image', 'hide');

				if ($bldm_listings_banner_image_url &&  $bldm_listings_banner_image == 'show') {
					$render_html .= '<div class="bldm-listing-filters" style="background-image: url(\'' . esc_url($bldm_listings_banner_image_url) . '\'); background-position: center;">';
				} else {
					$render_html .= '<div class="bldm-listing-filters">';
				}
			
			$render_html .= '<div class="bldm_page_hdng">' . $listing_page_hdng . '</div>';
			
			$listing_filters = $html->find('.rentals-filter', 0);
			if($listing_filters){
				$location = $listing_filters->find('.js-rentals__search-input', 0);
				$rent_min = $listing_filters->find('#rentFromFilter', 0);
				$rent_max = $listing_filters->find('#rentToFilter', 0);
				$filters_bedrooms = $listing_filters->find('#bedroomFilter', 0);
				$filters_bathrooms = $listing_filters->find('#bathroomFilter', 0);
				$filters_type = $listing_filters->find('#propertyTypeFilter', 0);
			}
			$bldm_page_sub_hdng = get_option('bldm_page_sub_hdng') ? get_option('bldm_page_sub_hdng') : '';
			if ($bldm_page_sub_hdng) {
				$render_html .= '<div class="bldm_page_sub_hdng">' . $bldm_page_sub_hdng . '</div>';
			}
			
			// Mobile filter button (only visible on small screens)
			$render_html .= '<button class="mobile-filters-button" id="open-filters-modal">Filter Properties</button>';
			
			// Modal for mobile filters
			$render_html .= '<div class="filters-modal" id="filters-modal" aria-hidden="true">';
			$render_html .= '<div class="filters-modal-content">';
			$render_html .= '<button class="close-modal" id="close-filters-modal">&times;</button>';
			$render_html .= '<h3>Filter Properties</h3>';
			
			// Mobile filter form
			$render_html .= '<form method="post" id="mobile-filter-form">';
			
			// Filters
			$searched_beds = $searched_baths = $searched_rent_min = $searched_rent_max = 0;
			$searched_loc = $searched_type = '';
			$bldm_filters_zip = get_option('bldm_filters_zip');
			$bldm_filters_minrent = get_option('bldm_filters_minrent');
			$bldm_filters_maxrent = get_option('bldm_filters_maxrent');
			$bldm_filters_bed = get_option('bldm_filters_bed');
			$bldm_filters_bath = get_option('bldm_filters_bath');
			$bldm_filters_type = get_option('bldm_filters_type');
			
			// Mobile Location Filter
			if($bldm_filters_zip == 'show' && $location){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-location-filter">Location</label>';
				if(isset($_POST['filters']['location'])){
					$searched_loc = sanitize_text_field($_POST['filters']['location']);
					$render_html .= '<input type="text" id="mobile-location-filter" name="filters[location]" value="'.$searched_loc.'" placeholder="'.$location->{'placeholder'}.'">';
				} else{
					$render_html .= '<input type="text" id="mobile-location-filter" name="filters[location]" placeholder="'.$location->{'placeholder'}.'">';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Min Rent Filter
			if($bldm_filters_minrent == 'show' && $rent_min){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-min-rent-filter">Minimum Rent</label>';
				if(isset($_POST['filters']['rent-min'])){
					$searched_rent_min = sanitize_text_field($_POST['filters']['rent-min']);
					$render_html .= '<input type="number" id="mobile-min-rent-filter" name="filters[rent-min]" value="'.$searched_rent_min.'" step="100" min="0" placeholder="$ Min Rent">';
				} else{
					$render_html .= '<input type="number" id="mobile-min-rent-filter" name="filters[rent-min]" step="100" min="0" placeholder="$ Min Rent">';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Max Rent Filter
			if($bldm_filters_maxrent == 'show' && $rent_max){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-max-rent-filter">Maximum Rent</label>';
				if(isset($_POST['filters']['rent-max'])){
					$searched_rent_max = sanitize_text_field($_POST['filters']['rent-max']);
					$render_html .= '<input type="number" id="mobile-max-rent-filter" name="filters[rent-max]" value="'.$searched_rent_max.'" step="100" min="0" placeholder="$ Max Rent">';
				} else{
					$render_html .= '<input type="number" id="mobile-max-rent-filter" name="filters[rent-max]" step="100" min="0" placeholder="$ Max Rent">';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Bedrooms Filter
			if($bldm_filters_bed == 'show' && $filters_bedrooms){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-bedrooms-filter">Bedrooms</label>';
				$correct_beds = str_replace("0+", "Beds", stripslashes($filters_bedrooms->innertext));
				if(isset($_POST['filters']['bedrooms'])){
					$searched_beds = $selected = sanitize_text_field($_POST['filters']['bedrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="mobile-bedrooms-filter" name="filters[bedrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_beds).'</select>';
				} else{
					$render_html .= '<select id="mobile-bedrooms-filter" name="filters[bedrooms]">'.$correct_beds.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Bathrooms Filter
			if($bldm_filters_bath == 'show' && $filters_bathrooms){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-bathrooms-filter">Bathrooms</label>';
				$correct_baths = str_replace("0+", "Baths", stripslashes($filters_bathrooms->innertext));
				if(isset($_POST['filters']['bathrooms'])){
					$searched_baths = $selected = sanitize_text_field($_POST['filters']['bathrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="mobile-bathrooms-filter" name="filters[bathrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_baths).'</select>';
				} else{
					$render_html .= '<select id="mobile-bathrooms-filter" name="filters[bathrooms]">'.$correct_baths.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Property Type Filter
			if($bldm_filters_type == 'show' && $filters_type){
				$render_html .= '<div class="filter">';
				$render_html .= '<label for="mobile-property-type-filter">Property Type</label>';
				$correct_type = stripslashes($filters_type->innertext);
				if(isset($_POST['filters']['propertyTypeFilter'])){
					$searched_type = $selected = sanitize_text_field($_POST['filters']['propertyTypeFilter']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="mobile-property-type-filter" name="filters[propertyTypeFilter]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_type).'</select>';
				} else{
					$render_html .= '<select id="mobile-property-type-filter" name="filters[propertyTypeFilter]">'.$correct_type.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Mobile Search Button
			$render_html .= '<input type="submit" class="bldm-prmry-btn" value="Apply Filters" name="fltr-submt">';
			
			$render_html .= '</form>';
			$render_html .= '</div>'; // End modal content
			$render_html .= '</div>'; // End modal
			
			// Desktop filter form
			$render_html .= '<form method="post" class="desktop-filter-form">';
			
			// Filters
			$searched_beds = $searched_baths = $searched_rent_min = $searched_rent_max = 0;
			$searched_loc = $searched_type = '';
			$bldm_filters_zip = get_option('bldm_filters_zip');
			$bldm_filters_minrent = get_option('bldm_filters_minrent');
			$bldm_filters_maxrent = get_option('bldm_filters_maxrent');
			$bldm_filters_bed = get_option('bldm_filters_bed');
			$bldm_filters_bath = get_option('bldm_filters_bath');
			$bldm_filters_type = get_option('bldm_filters_type');
			
			// Location Filter
			if($bldm_filters_zip == 'show' && $location){
				$render_html .= '<div class="filter-section location-filter">';
				$render_html .= '<label for="location-filter">Location</label>';
				if(isset($_POST['filters']['location'])){
					$searched_loc = sanitize_text_field($_POST['filters']['location']);
					$render_html .= '<input type="text" id="location-filter" name="filters[location]" value="'.$searched_loc.'" placeholder="'.$location->{'placeholder'}.'">';
				} else{
					$render_html .= '<input type="text" id="location-filter" name="filters[location]" placeholder="'.$location->{'placeholder'}.'">';
				}
				$render_html .= '</div>';
			}
			
			// Min Rent Filter
			if($bldm_filters_minrent == 'show' && $rent_min){
				$render_html .= '<div class="filter-section min-rent-filter">';
				$render_html .= '<label for="min-rent-filter">Minimum Rent</label>';
				if(isset($_POST['filters']['rent-min'])){
					$searched_rent_min = sanitize_text_field($_POST['filters']['rent-min']);
					$render_html .= '<input type="number" id="min-rent-filter" name="filters[rent-min]" value="'.$searched_rent_min.'" step="100" min="0" placeholder="$ Min Rent">';
				} else{
					$render_html .= '<input type="number" id="min-rent-filter" name="filters[rent-min]" step="100" min="0" placeholder="$ Min Rent">';
				}
				$render_html .= '</div>';
			}
			
			// Max Rent Filter
			if($bldm_filters_maxrent == 'show' && $rent_max){
				$render_html .= '<div class="filter-section max-rent-filter">';
				$render_html .= '<label for="max-rent-filter">Maximum Rent</label>';
				if(isset($_POST['filters']['rent-max'])){
					$searched_rent_max = sanitize_text_field($_POST['filters']['rent-max']);
					$render_html .= '<input type="number" id="max-rent-filter" name="filters[rent-max]" value="'.$searched_rent_max.'" step="100" min="0" placeholder="$ Max Rent">';
				} else{
					$render_html .= '<input type="number" id="max-rent-filter" name="filters[rent-max]" step="100" min="0" placeholder="$ Max Rent">';
				}
				$render_html .= '</div>';
			}
			
			// Bedrooms Filter
			if($bldm_filters_bed == 'show' && $filters_bedrooms){
				$render_html .= '<div class="filter-section bedrooms-filter">';
				$render_html .= '<label for="bedrooms-filter">Bedrooms</label>';
				$correct_beds = str_replace("0+", "Beds", stripslashes($filters_bedrooms->innertext));
				if(isset($_POST['filters']['bedrooms'])){
					$searched_beds = $selected = sanitize_text_field($_POST['filters']['bedrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="bedrooms-filter" name="filters[bedrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_beds).'</select>';
				} else{
					$render_html .= '<select id="bedrooms-filter" name="filters[bedrooms]">'.$correct_beds.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Bathrooms Filter
			if($bldm_filters_bath == 'show' && $filters_bathrooms){
				$render_html .= '<div class="filter-section bathrooms-filter">';
				$render_html .= '<label for="bathrooms-filter">Bathrooms</label>';
				$correct_baths = str_replace("0+", "Baths", stripslashes($filters_bathrooms->innertext));
				if(isset($_POST['filters']['bathrooms'])){
					$searched_baths = $selected = sanitize_text_field($_POST['filters']['bathrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="bathrooms-filter" name="filters[bathrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_baths).'</select>';
				} else{
					$render_html .= '<select id="bathrooms-filter" name="filters[bathrooms]">'.$correct_baths.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Property Type Filter
			if($bldm_filters_type == 'show' && $filters_type){
				$render_html .= '<div class="filter-section property-type-filter">';
				$render_html .= '<label for="property-type-filter">Property Type</label>';
				$correct_type = stripslashes($filters_type->innertext);
				if(isset($_POST['filters']['propertyTypeFilter'])){
					$searched_type = $selected = sanitize_text_field($_POST['filters']['propertyTypeFilter']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select id="property-type-filter" name="filters[propertyTypeFilter]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_type).'</select>';
				} else{
					$render_html .= '<select id="property-type-filter" name="filters[propertyTypeFilter]">'.$correct_type.'</select>';
				}
				$render_html .= '</div>';
			}
			
			// Search Button
			$render_html .= '<div class="search-button-section">';
			$render_html .= '<input type="submit" value="Search Properties" name="fltr-submt">';
			$render_html .= '</div>';
			
			$render_html .= '</form></div>';
			
			// All listings in columns
			$render_html .= '<div class="bldm-all-listings bldm-section-inner">';
			$listing_items = $html->find('a.featured-listing');
			
			if($listing_items){
				$i = 0;
				
				$bldm_listings_display_price = get_option('bldm_listings_display_price');
				$bldm_listings_price_pos = get_option('bldm_listings_price_pos');
				
				$bldm_listings_display_avail = get_option('bldm_listings_display_avail');
				$bldm_listings_avail_pos = get_option('bldm_listings_avail_pos');
				
				$bldm_listings_display_ttl = get_option('bldm_listings_display_ttl');
				$bldm_listings_ttl_tag = get_option('bldm_listings_ttl_tag');
				
				$bldm_listings_display_address = get_option('bldm_listings_display_address');
				$bldm_listings_address_tag = get_option('bldm_listings_address_tag');
				
				$bldm_listings_display_beds = get_option('bldm_listings_display_beds');
				$bldm_listings_bed_img = get_option('bldm_listings_bed_img');
				
				$bldm_listings_display_baths = get_option('bldm_listings_display_baths');
				$bldm_listings_bath_img = get_option('bldm_listings_bath_img');
				
				$bldm_listings_display_detail = get_option('bldm_listings_display_detail');
				
				$bldm_listings_display_apply = get_option('bldm_listings_display_apply');
				
				$bldm_columns_cnt = get_option('bldm_columns_cnt');
				if(!$bldm_columns_cnt){ $bldm_columns_cnt = 3; }
				foreach ($listing_items as $listing) {
					
					$list_beds = $listing->{'data-bedrooms'};
					$list_baths = $listing->{'data-bathrooms'};
					$list_rent = $listing->{'data-rent'};
					$list_type = $listing->{'data-type'};
					$list_location = $listing->{'data-location'};
					
					if((int)$searched_beds){
						if($list_beds < (int)$searched_beds){
							continue;
						}
					}
					if((int)$searched_baths){
						if($list_baths < (int)$searched_baths){
							continue;
						}
					}
					if((int)$searched_rent_min){
						if($list_rent < (int)$searched_rent_min){
							continue;
						}
					}
					if((int)$searched_rent_max){
						if($list_rent > (int)$searched_rent_max){
							continue;
						}
					}
					
					if($searched_type){
						if($list_type != $searched_type){
							continue;
						}
					}
					
					if(!empty($searched_loc)){
						if(strpos(strtolower($list_location), strtolower($searched_loc)) === false){
							continue;
						}
					}
					
					$listing_ID = '';
					$list_url = $listing->{'href'};
					if($list_url){
						$list_url_part = explode('?hidenav', $list_url);
						$list_url = $list_url_part[0];
						$pos = strrpos($list_url, '/');
						if($pos){
							$listing_ID = substr($list_url, $pos + 1);
						}
					}
					
					$listing_Img = '';
					$listing_Img_obj = $listing->find('.featured-listing__image-container img', 0);
					if($listing_Img_obj){
						$listing_Img = $listing_Img_obj->{'src'};
						$listing_Img = $bldm_listings_url . $listing_Img;
					}
					
					$listing_rent = '';
					$listing_rent = $listing->{'data-rent'};
					
					$listing_beds = '';
					$listing_beds = $listing->{'data-bedrooms'};
					
					$listing_baths = '';
					$listing_baths = $listing->{'data-bathrooms'};
					
					$listing_ttl = $listing_address = $listing_avlble = '';
					$listing_content = $listing->find('.featured-listing__content', 0);
					if($listing_content){
						$listing_address_obj = $listing_content->find('.featured-listing__address', 0);
						if($listing_address_obj){
							$listing_address = $listing_address_obj->innertext;
						}
						$listing_ttl_obj = $listing_content->find('.featured-listing__title', 0);
						if($listing_ttl_obj){
							$listing_ttl = $listing_ttl_obj->innertext;
						}
						$listing_avl_obj = $listing_content->find('.featured-listing__availability', 0);
						if($listing_avl_obj){
							$listing_avlble = $listing_avl_obj->innertext;
						}
					}
					
					$listing_Apply_Link = '';
					if($listing_ID){
						$listing_Apply_Link = $bldm_listings_url . '/Resident/rental-application/?listingId='.$listing_ID.'&hidenav=true';
					}
					
					$listing_Apply_Link = apply_filters( "bldm_apply_btn_link", $listing_Apply_Link, $listing_Apply_Link );
					
					if($i%$bldm_columns_cnt == 0){
						$render_html .= '<div class="bldm-listing-items-grp">';
					}
					$render_html .= '<div class="bldm-listing-item bldm-column">
						<a href="?lid='.$listing_ID.'">
						<div class="bldm-list-img">
							<img src="'.$listing_Img.'">';
						if($bldm_listings_display_price == 'show' && $bldm_listings_price_pos == 'onimage'){
							$render_html .= '<span class="bldm-rent-price">$'.$listing_rent.'</span>';
						}
						if($bldm_listings_display_avail == 'show' && $bldm_listings_avail_pos == 'onimage'){
							$render_html .= '<span class="bldm-lstng-avail">'.$listing_avlble.'</span>';
						}
						$render_html .= '</div></a>
						<div class="bldm-details">';
						
						if($bldm_listings_display_price == 'show' && $bldm_listings_price_pos == 'offimage'){
							$render_html .= '<span class="bldm-rent-price-off">$'.$listing_rent.'</span>';
						}
						if($bldm_listings_display_avail == 'show' && $bldm_listings_avail_pos == 'offimage'){
							$render_html .= '<span class="bldm-lstng-avail-off">'.$listing_avlble.'</span>';
						}
						
						if($bldm_listings_display_ttl == 'show'){
							if(!$bldm_listings_ttl_tag){ $bldm_listings_ttl_tag = 'h2'; }
							$render_html .= '<'.$bldm_listings_ttl_tag.' class="bldm-lstng_ttl">'.$listing_ttl.'</'.$bldm_listings_ttl_tag.'>';
						}
						
						if($bldm_listings_display_address == 'show'){
							if(!$bldm_listings_address_tag){ $bldm_listings_address_tag = 'h3'; }
							$render_html .= '<'.$bldm_listings_address_tag.' class="bldm-address">'.$listing_address.'</'.$bldm_listings_address_tag.'>';
						}						
						
						$render_html .= '<p>';
						if($bldm_listings_display_beds == 'show'){
							if($bldm_listings_bed_img){ $render_html .= '<img class="bldm-bedimg" src="'.$bldm_listings_bed_img.'">'; }
							$render_html .= '<span>'.$listing_beds.' Bed </span> ';
						}
						if($bldm_listings_display_baths == 'show'){
							if($bldm_listings_bath_img){ $render_html .= '<img class="bldm-bathimg" src="'.$bldm_listings_bath_img.'">'; }
							$render_html .= '<span>'.$listing_baths.' Bath</span>';
						}
						$render_html .= '</p><div class="bldm-btns">';
						
						if($bldm_listings_display_detail == 'show'){
							$render_html .= '<a class="bldm_more_detail_btn" href="?lid='.$listing_ID.'">Details</a>';
						}
						if($bldm_listings_display_apply == 'show'){
							$render_html .= '<a class="bldm_apply_btn" href="'.$listing_Apply_Link.'" target="_blank">Apply</a>';
						}
						$render_html .= '</div>
						</div>
					</div>';
					
					$i++;
					
					if($i%$bldm_columns_cnt == 0){
						$render_html .= '</div>';
					}
					
				}
				if(!$i){
					$render_html .= '<div class="bldm-no-listings"><p>No vacancies found matching your search criteria. Please select other filters.</p></div>';
				}
				
			} else{
				$render_html .= '<div class="bldm-no-listings"><p>No vacancies found matching your search criteria. Please select other filters.</p></div>';
			}
			$render_html .= '</div></div>';

			return $render_html;
		
		}
		
		// Add JavaScript for mobile filter modal
		add_action('wp_footer', 'bldm_filter_modal_js');
		function bldm_filter_modal_js() {
			?>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				// Get modal elements
				const modal = document.getElementById('filters-modal');
				const openModalBtn = document.getElementById('open-filters-modal');
				const closeModalBtn = document.getElementById('close-filters-modal');
				
				// Check if elements exist (they might not if we're not on the listings page)
				if (modal && openModalBtn && closeModalBtn) {
					// Open modal
					openModalBtn.addEventListener('click', function() {
						modal.setAttribute('aria-hidden', 'false');
						document.body.style.overflow = 'hidden'; // Prevent scrolling
					});
					
					// Close modal
					closeModalBtn.addEventListener('click', function() {
						modal.setAttribute('aria-hidden', 'true');
						document.body.style.overflow = ''; // Restore scrolling
					});
					
					// Close modal when clicking outside content
					modal.addEventListener('click', function(e) {
						if (e.target === modal) {
							modal.setAttribute('aria-hidden', 'true');
							document.body.style.overflow = '';
						}
					});
					
					// Sync filter values between desktop and mobile forms
					const desktopForm = document.querySelector('.desktop-filter-form');
					const mobileForm = document.getElementById('mobile-filter-form');
					
					if (desktopForm && mobileForm) {
						// Sync from desktop to mobile when opening modal
						openModalBtn.addEventListener('click', function() {
							syncFormValues(desktopForm, mobileForm);
						});
						
						// Sync from mobile to desktop when submitting mobile form
						mobileForm.addEventListener('submit', function() {
							syncFormValues(mobileForm, desktopForm);
						});
					}
					
					// Function to sync form values
					function syncFormValues(sourceForm, targetForm) {
						const sourceInputs = sourceForm.querySelectorAll('input:not([type="submit"]), select');
						
						sourceInputs.forEach(function(input) {
							const inputName = input.getAttribute('name');
							if (inputName) {
								const targetInput = targetForm.querySelector('[name="' + inputName + '"]');
								if (targetInput) {
									if (input.type === 'checkbox' || input.type === 'radio') {
										targetInput.checked = input.checked;
									} else {
										targetInput.value = input.value;
									}
								}
							}
						});
					}
				}
			});
			</script>
			<?php
		}
	}
}
