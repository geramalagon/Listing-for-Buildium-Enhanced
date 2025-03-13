jQuery(document).ready(function($) {
	
	// Customizer toggle options fields
	$('.bldm_custom_option input[type="checkbox"]').on('click', function(){
		if($(this).is(":checked")){
			$(this).closest('td').find('.bldm-adm-more-options').show();
		} else{
			$(this).closest('td').find('.bldm-adm-more-options').hide();
		}
	});
	
	// color picker
	$('.bldm-listings-color').wpColorPicker();

	$('#bldm-upload-banner-image').on('click', function (e) {
		e.preventDefault();
		var fileInput = $('#bldm_listings_banner_image_upload')[0];

		// Check if a file is selected
		if (fileInput.files.length > 0) {
			var uploaded_file = fileInput.files[0];
			var fd = new FormData();
			fd.append("uploaded_file", uploaded_file);
			fd.append("action", "bldm_handle_banner_image_upload");
			fd.append("bldm_nonce", bldm_admin_obj.nonce);
			jQuery
				.ajax({
					url: bldm_admin_obj.ajaxurl,
					type: "POST",
					data: fd,
					contentType: false,
					processData: false,
					dataType: "JSON",
				})
				.done(function (results) {
					if (results.url) {

						$('#bldm-banner-image-preview').attr('src', results.url);
						$('#bldm-banner-image-preview').css('display', 'block');

						$('#bldm-remove-banner-image').attr('file-src', results.url);
						$('#bldm-remove-banner-image').css('display', 'block');


						$('#bldm-upload-msg').html(results.msg);
						$('#bldm-upload-msg').css('color', 'green');

					} else if (results.error) {
						$('#bldm-upload-msg').html(results.error);
						$('#bldm-upload-msg').css('color', 'red');
					}
				})
				.fail(function (data) {
					console.log(data.responseText);
					console.log("Request Failed. Status - " + data.statusText);
				});
		} else {
			alert('Please select a file before uploading.');
		}
	});

	$('#bldm-remove-banner-image').on('click', function () {

		file_url = $(this).attr('file-src');
		// Check if a file is selected
		if (file_url) {

			var fd = new FormData();
			fd.append("file_url", file_url);
			fd.append("action", "bldm_handle_banner_image_remove");
			fd.append("bldm_nonce", bldm_admin_obj.nonce);
			jQuery
				.ajax({
					url: bldm_admin_obj.ajaxurl,
					type: "POST",
					data: fd,
					contentType: false,
					processData: false,
					dataType: "JSON",
				})
				.done(function (results) {
					if (results.msg) {
						$('#bldm-banner-image-preview').attr('src', '');
						$('#bldm-banner-image-preview').css('display', 'none');

						$('#bldm-remove-banner-image').attr('file-src', '');
						$('#bldm-remove-banner-image').css('display', 'none');

						$('#bldm-upload-msg').html(results.msg);
						$('#bldm-upload-msg').css('color', 'green');

					} else if (results.error) {
						$('#bldm-upload-msg').html(results.error);
						$('#bldm-upload-msg').css('color', 'red');
					}

				})
				.fail(function (data) {
					console.log(data.responseText);
					console.log("Request Failed. Status - " + data.statusText);
				});
		}
	});

	$('#bldm_listings_banner_image_upload').change(function () {
		previewImage(this);
	});

	function previewImage(input) {
		var preview = $('#bldm-banner-image-preview');
		var file = input.files[0];
		var reader = new FileReader();

		reader.onload = function (e) {
			preview.attr('src', e.target.result);
			preview.css('display', 'block');
		};

		if (file) {
			reader.readAsDataURL(file);
		}
	}
	
});