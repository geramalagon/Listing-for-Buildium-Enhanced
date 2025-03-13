<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

add_action('wp_ajax_bldm_handle_banner_image_upload', 'bldm_handle_banner_image_upload');
add_action('wp_ajax_nopriv_bldm_handle_banner_image_upload', 'bldm_handle_banner_image_upload');
if (!function_exists('bldm_handle_banner_image_upload')) {
	function bldm_handle_banner_image_upload()
	{
		$res_array = array();
		if (!isset($_POST) || empty($_POST) || !is_user_logged_in() || !wp_verify_nonce($_POST['bldm_nonce'], 'bldm_ajax_nonce')) {
			header('HTTP/1.1 400 Empty POST Values');
			$res_array['error'] = ('Error - Could not verify POST values');
			echo json_encode($res_array);
			exit;
		}

		if (!empty($_FILES['uploaded_file'])) {
			$uploaded_file = $_FILES['uploaded_file'];
		
			$upload_overrides = array('test_form' => false);
		
			$file_data = wp_handle_upload($uploaded_file, $upload_overrides);
		
			if ($file_data && !isset($file_data['error'])) {
				// Get the attachment ID
				$attachment = array(
					'post_title'     => sanitize_file_name($file_data['file']),
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_mime_type' => $file_data['type'],
				);
		
				$attachment_id = wp_insert_attachment($attachment, $file_data['file']);
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attachment_data = wp_generate_attachment_metadata($attachment_id, $file_data['file']);
				wp_update_attachment_metadata($attachment_id, $attachment_data);
		
				// Update option with attachment ID
				update_option('bldm_listings_banner_image_url', $file_data['url']);
		
				$res_array['url'] = $file_data['url'];
				$res_array['attachment_id'] = $attachment_id; // Include the attachment ID in the response
				$res_array['msg'] = "File uploaded successfully!";
			} else {
				$res_array['error'] = $file_data['error'];
			}
		} else {
			$res_array['error'] = 'No file selected for upload.';
		}
		

		echo json_encode($res_array);
		exit;
	}
}

add_action('wp_ajax_bldm_handle_banner_image_remove', 'bldm_handle_banner_image_remove');
add_action('wp_ajax_nopriv_bldm_handle_banner_image_remove', 'bldm_handle_banner_image_remove');
if (!function_exists('bldm_handle_banner_image_remove')) {
	function bldm_handle_banner_image_remove()
	{
		$res_array = array();
		if (!isset($_POST) || empty($_POST) || !is_user_logged_in() || !wp_verify_nonce($_POST['bldm_nonce'], 'bldm_ajax_nonce')) {
			header('HTTP/1.1 400 Empty POST Values');
			$res_array['error'] = ('Error - Could not verify POST values');
			echo json_encode($res_array);
			exit;
		}

		if (isset($_POST['file_url'])) {
            $file_url = sanitize_url($_POST['file_url']);
            $attachment_id = attachment_url_to_postid($file_url);

            if ($attachment_id) {
                $deleted = wp_delete_attachment($attachment_id, true);
				update_option('bldm_listings_banner_image_url', '');

                if ($deleted) {
                    $res_array['msg'] = 'Attachment deleted successfully.';
                } else {
                    $res_array['error'] = 'Error deleting attachment.';
                }
            } else {
                $res_array['error'] = 'Invalid attachment URL.';
            }
        }

		echo json_encode($res_array);
		exit;
	}
}
