 (function( $ ) {
	'use strict';

	$(document).ready(function() {

		jQuery(document).on("submit", '.slotForm', function(event) { 
			event.preventDefault();
			
			jQuery(this).parent().addClass('loading');
			var userID = +jQuery(this).find(".userID").val();
			var postID = +jQuery(this).find(".postID").val();
			var slot = jQuery(this).find(".slot").val();

			console.log('userID: ' + userID);
			console.log('postID: ' + postID);
			console.log('slot: ' + slot);

			// Send data back to PHP function that updates ACF database	
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: slotting_localize.ajax_url,
				data: {action: 'tcbp_public_slotting_update', postId: postID, userId: userID, slot: slot, nounce: slotting_localize.ajax_nounce},
				success: function(response) {
					console.log(response);

					// Reload the attendance roster part of the page with AJAX
					jQuery('#dynamicContent').load(document.URL + ' #dynamicContent');
				},
				error: function(response) {
					console.log(response);
				}
			});
		});
	});
})( jQuery );
