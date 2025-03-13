jQuery(document).ready(function($) {
	
	// extract YT video ID
	function getId(url) {
		const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
		const match = url.match(regExp);

		return (match && match[2].length === 11)
		  ? match[2]
		  : null;
	}
	
	// pause YT video on slide change
	$('.bldm-gallery .bldm-next, .bldm-gallery .bldm-prev, .bldm-imgcolumn').on('click', function(){
		$('.bldm-yt-frame').each(function(){
			$(this)[0].contentWindow.postMessage('{"event":"command","func":"' + 'pauseVideo' + '","args":""}', '*');
		});
	});
	
	// Gallery popup/lightbox
	$('.bldm-gallery img, .bldm-imgcolumn img').on('click', function(){
		let pp_html = '<div class="bldm_full_pp"><span class="close_bldm_pp">X</span>';
		let pp_slide = '';
		const pp_curr_slide_id = $(this).attr('data-id');
		
		$('.bldm-gall-full-img').each(function(){
			const full_src = $(this).attr('data-src');
			const pp_slide_id = $(this).attr('data-id');
			let current_slide = '';
			if(pp_curr_slide_id == pp_slide_id){
				current_slide = 'current';
			}
			if(full_src.indexOf('youtube') != -1){
				const videoId = getId(full_src);
				pp_slide = '<div id="'+pp_slide_id+'" class="bldm_pp_slide bldm_vid_container '+current_slide+'"><iframe class="bldm-yt-frame" width="560" height="330" src="//www.youtube.com/embed/' + videoId + '?enablejsapi=1&version=3&playerapiid=ytplayer" frameborder="0" allowfullscreen></iframe></div>';
			} else{
				pp_slide = '<div id="'+pp_slide_id+'" class="bldm_pp_slide '+current_slide+'"><img src="'+full_src+'"></div>';
			}
			pp_html += pp_slide;
		});
		pp_html += '<span id="bldm_pp_prev">&lsaquo;</span><span id="bldm_pp_next">&rsaquo;</span></div>';
		$('body').append(pp_html);
	});
	
	$('body').on('click', '.close_bldm_pp', function(){
		$('.bldm_full_pp').remove();
	});
	
	$('body').on('click', '#bldm_pp_prev', function(){
		pp_slideIndex = parseInt($('.bldm_pp_slide.current').attr('id'));
		pp_showSlides(pp_slideIndex += -1);
	});
	$('body').on('click', '#bldm_pp_next', function(){
		pp_slideIndex = parseInt($('.bldm_pp_slide.current').attr('id'));
		pp_showSlides(pp_slideIndex += 1);
	});
	
	// Gallery popup slider
	let pp_slideIndex = '';
	function pp_showSlides(n) {
	  var i;
	  var slides = document.getElementsByClassName("bldm_pp_slide");
	  if (n > slides.length) {pp_slideIndex = 1}
	  if (n < 1) {pp_slideIndex = slides.length}
	  for (i = 0; i < slides.length; i++) {
		// slides[i].style.display = "none";
		slides[i].className = slides[i].className.replace(" current", "");
	  }
	  slides[pp_slideIndex-1].className += " current";
	}

	
	
});