<?php
/**
 * Software License Manager Client
 *
 * @package    Software License Manager Client
 * @subpackage SlmClient Main Functions
 */

$slmclient = new BldmSlmClient();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class BldmSlmClient {

	/** ==================================================
	 * Path
	 *
	 * @var $script_url  script_url.
	 */
	private $script_url;

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		/* Script Url */
		$this->script_url = plugin_dir_url( __DIR__ ) . 'license-checker/jquery.slmclient.js';

		add_shortcode( 'bldm_slmcl', array( $this, 'slmclient_func' ) );
		add_action( 'deactive_slm_key', array( $this, 'deactive_key' ), 10, 1 );
		add_filter( 'slmcl_is_licensed', array( $this, 'is_licensed' ), 10, 1 );

		$action1 = 'slm-client-ajax-action';
		add_action( 'wp_ajax_' . $action1, array( $this, 'slmclient_charge_callback' ) );
		add_action( 'wp_ajax_nopriv_' . $action1, array( $this, 'slmclient_charge_callback' ) );

	}

	/** ==================================================
	 * Short code
	 *
	 * @param array  $atts  attributes.
	 * @param string $content  contents.
	 * @return string $content  contents.
	 * @since 1.00
	 */
	public function slmclient_func( $atts, $content = null ) {

		$a = shortcode_atts(
			array(
				'item_reference'     => '',
				'sales_site_url'     => '',
				'license_server_url' => '',
				'special_secretkey'  => '',
			),
			$atts
		);

		if ( get_option( 'slmclient' ) ) {
			$settings_tbl = get_option( 'slmclient' );
			foreach ( $settings_tbl as $key => $value ) {
				$shortcodekey = strtolower( $key );
				if ( empty( $a[ $shortcodekey ] ) ) {
					$a[ $shortcodekey ] = $value;
				}
			}
		} else {
			if ( empty( $a['item_reference'] ) ||
				 empty( $a['license_server_url'] ) ||
				 empty( $a['special_secretkey'] ) ) {
				return;
			}
		}

		$a2                     = $this->atts_item( $a );
		$license_key_name       = $a2['license_key_name'];
		$sales_site_url         = $a2['sales_site_url'];
		$a2['slm_text_message'] = apply_filters( 'slmclient_activate_key_sent', __( 'License Key has been sent.', 'software-license-manager-client' ) );

		/* Ajax */
		$handle  = 'slm-client-ajax-script';
		$action1 = 'slm-client-ajax-action';
		wp_enqueue_script( $handle, $this->script_url, array( 'jquery' ), '1.0.0', false );
		$ajax_arr = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'action'   => $action1,
			'nonce'    => wp_create_nonce( $action1 ),
		);
		$ajax_arr = array_merge( $ajax_arr, $a2 );
		wp_localize_script( $handle, 'SLMCLIENTCHARGE', $ajax_arr );

		$licensed = $this->is_licensed( $a );

		/* Messages filters */
		$slmclient_activate_title = apply_filters( 'slmclient_activate_title', __( 'License', 'software-license-manager-client' ) );
		$slmclient_activate_enter_license_key = apply_filters( 'slmclient_activate_enter_license_key', __( 'Please enter the license key for this product to activate it. You are given a license key when you purchased this item.', 'software-license-manager-client' ) );
		$slmclient_activate_activated = apply_filters( 'slmclient_activate_activated', __( 'This product has been activated.', 'software-license-manager-client' ) );
		$slmclient_activate_license_key = apply_filters( 'slmclient_activate_license_key', __( 'License Key', 'software-license-manager-client' ) );
		$slmclient_activate_activate_button = apply_filters( 'slmclient_activate_activate_button', __( 'Activate', 'software-license-manager-client' ) );
		$slmclient_activate_confirm_button = apply_filters( 'slmclient_activate_confirm_button', __( 'Confirm', 'software-license-manager-client' ) );

		/* Form */
		$content .= '<div class="wrap">';
		// $content .= '<h3 style="display: inline;"><strong>' . $slmclient_activate_title . '</strong></h3>';
		$content .= '<div class="bldm_admin_top"><p>Listings For Buildium - WordPress Plugin</p><a href="https://listingsforbuildium.com/support/" target="_blank">Contact Us</a><a href="https://listingsforbuildium.com/documentation/" target="_blank">Documentation</a></div>';

		$content .= '<h4 class="bldm_slm_text_message">';
		if ( ! $licensed ) {
			$content .= $slmclient_activate_enter_license_key;
		} else {
			$content .= $slmclient_activate_activated;
		}
		$content .= '</h4>';
		$content .= '<div class="bldm_admin_key"><label for="' . $license_key_name . '">' . $slmclient_activate_license_key . '</label>';
		$readonly = null;
		if ( $licensed ) {
			$readonly = ' readonly="readonly"';
		}
		$content .= '<input style="margin-right: 10px;" class="bldm_lic_input regular-text" type="text" id="' . $license_key_name . '" name="' . $license_key_name . '" value="' . get_option( $license_key_name ) . '"' . $readonly . ' ></div>';
		
		if ( $licensed ) {
			$content .= '';
		} else {
			$content .= '<button style="margin-right: 10px;" type="button" class="button-primary bldm_activate_btm" id="activate_license">' . $slmclient_activate_activate_button . '</button>';
		}
		$content .= '</div>';
		
		return do_shortcode( $content );

	}

	/** ==================================================
	 * Attributes item
	 *
	 * @param array $atts  attributes.
	 * @return array $new_atts  attributes.
	 * @since 1.00
	 */
	private function atts_item( $atts ) {

		$new_atts = array();
		/* Name */
		$text                       = $atts['item_reference'];
		$text                       = trim( mb_convert_kana( $text, 'as', 'UTF-8' ) );
		$text                       = preg_replace( '/[^0-9a-z_-]/', '', $text );
		$new_atts['item_reference'] = substr( $text, 0, 20 );
		/* License key name */
		$new_atts['license_key_name'] = 'license_key_' . $new_atts['item_reference'];
		/* Sales Site URL */
		$new_atts['sales_site_url'] = $atts['sales_site_url'];
		/* License Server URL */
		$new_atts['license_server_url'] = $atts['license_server_url'];
		/* The Special Secret key */
		$new_atts['special_secretkey'] = $atts['special_secretkey'];

		return $new_atts;

	}

	/** ==================================================
	 * Software License Manager Verification
	 *
	 * @param array  $atts  attributes.
	 * @param string $license_key  license_key.
	 * @param string $slm_action  slm_action.
	 * @return object $license_data
	 * @since 1.00
	 */
	private function slm_verifi( $atts, $license_key, $slm_action ) {

		$item_reference     = $atts['item_reference'];
		$license_server_url = $atts['license_server_url'];
		$special_secretkey  = $atts['special_secretkey'];

		$domain_name = esc_url( home_url() );
		if ( is_ssl() ) {
			$domain_name = str_replace( 'https://', '', $domain_name );
		} else {
			$domain_name = str_replace( 'http://', '', $domain_name );
		}

		/* API query parameters */
		$api_params = array(
			'slm_action'        => $slm_action,
			'secret_key'        => $special_secretkey,
			'license_key'       => $license_key,
			'registered_domain' => $domain_name,
			'item_reference'    => urlencode( $item_reference ),
		);

		/* Send query to the license manager server */
		$query    = esc_url_raw( add_query_arg( $api_params, $license_server_url ) );
		$response = wp_remote_get(
			$query,
			array(
				'timeout' => 20,
				'sslverify' => false,
			)
		);

		/* Check for error in the response */
		if ( is_wp_error( $response ) ) {
			// echo 'Unexpected Error! The query returned with an error.';
			return 'no_response';
		}

		/* License data. */
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		return $license_data;

	}

	/** ==================================================
	 * License Key Check
	 *
	 * @param array $atts  attributes.
	 * @return bool
	 * @since 1.00
	 */
	public function is_licensed( $atts ) {

		$atts2             = $this->atts_item( $atts );
		$license_key_name  = $atts2['license_key_name'];
		if ( get_option( $license_key_name ) ) {
			$license_key = get_option( $license_key_name );
			$license_data = $this->slm_verifi( $atts, $license_key, 'slm_check' );
			if ( is_object( $license_data ) ) {
				if ( property_exists( $license_data, 'result' ) && property_exists( $license_data, 'status' ) ) {
					if ( 'success' == $license_data->result ) { // && 'active' == $license_data->status - Keep plugin working even if key has expired, we will only check for active key when need updates
						// return true;
						return $license_data;
					} else {
						delete_option( $license_key_name );
						return false;
					}
				}
			} else if ( 'no_response' === $license_data ) {
				$licensed = get_option('bldm_licensed');
				if(!$licensed){
					return false;
				} else{
					$license_obj = new stdClass();
					$license_obj->status = 'active';
					return $license_obj;
				}
			}
		}

		return false;

	}

	/** ==================================================
	 * Charge Callback
	 *
	 * @param string $license_key  license_key.
	 * @since 1.00
	 */
	public function slmclient_charge_callback( $license_key = null ) {
		
		delete_transient( 'bldm_lic_checked' );

		$action1 = 'slm-client-ajax-action';
		if ( check_ajax_referer( $action1, 'nonce', false ) ) {
			if ( isset( $_POST['license_key'] ) && ! empty( $_POST['license_key'] ) ) {
				$license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ) );
			}
			if ( isset( $_POST['license_key_name'] ) && ! empty( $_POST['license_key_name'] ) ) {
				$atts['license_key_name'] = sanitize_text_field( wp_unslash( $_POST['license_key_name'] ) );
			}
			if ( isset( $_POST['special_secretkey'] ) && ! empty( $_POST['special_secretkey'] ) ) {
				$atts['special_secretkey'] = sanitize_text_field( wp_unslash( $_POST['special_secretkey'] ) );
			}
			if ( isset( $_POST['item_reference'] ) && ! empty( $_POST['item_reference'] ) ) {
				$atts['item_reference'] = sanitize_text_field( wp_unslash( $_POST['item_reference'] ) );
			}
			if ( isset( $_POST['license_server_url'] ) && ! empty( $_POST['license_server_url'] ) ) {
				$atts['license_server_url'] = esc_url_raw( wp_unslash( $_POST['license_server_url'] ) );
			}
			$license_data = $this->slm_verifi( $atts, $license_key, 'slm_activate' );
			if ( 'success' == $license_data->result ) {
				/* Save the license key in the options table */
				update_option( $atts['license_key_name'], $license_key );
				if ( ! is_null( $license_key ) ) {
					$license_key = apply_filters( 'slmclient_licensed', $license_key, $atts['item_reference'] );
					if ( is_wp_error( $license_key ) ) {
						return $license_key;
					}
				}
			}
		} else {
			status_header( '403' );
			echo 'Forbidden';
		}

	}

	/** ==================================================
	 * Action hook Deactive License Key
	 *
	 * @param array $arg  attributes.
	 * @since 1.00
	 */
	public function deactive_key( $arg ) {

		$settings_tbl = get_option( 'slmclient' );
		if($settings_tbl){
			foreach ( $settings_tbl as $key => $value ) {
				if ( ! array_key_exists( $key, $arg ) ) {
					$arg[ $key ] = $value;
				} else {
					if ( empty( $arg[ $key ] ) ) {
						$arg[ $key ] = $value;
					}
				}
			}
		}

		$arg2              = $this->atts_item( $arg );
		$license_key_name  = $arg2['license_key_name'];
		$license_key       = get_option( $license_key_name );
		$license_data      = $this->slm_verifi( $arg2, $license_key, 'slm_deactivate' );
		
		// delete_option( $license_key_name ); // delete anyway - issue sometimes cause of the clone
		
		if ( is_object( $license_data ) ) {
			if ( property_exists( $license_data, 'result' ) ) {
				if ( 'success' == $license_data->result ) {
					delete_option( $license_key_name );
					delete_transient( 'bldm_lic_checked' );
				}
			}
		}

	}

}


