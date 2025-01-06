 (function( $ ) {
	'use strict';

	$(document).ready(function() {

		jQuery(document).on("submit", '.slotForm', function(event) { 
			event.preventDefault();

			var userID = +jQuery(this).find(".userID").val();
			var postID = +jQuery(this).find(".postID").val();
			var slot = jQuery(this).find(".slot").val();

			// console.log('userID: ' + userID);
			// console.log('postID: ' + postID);
			// console.log('slot: ' + slot);

			// Send data back to PHP function that updates ACF database	
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: slotting_localize.ajax_url,
				data: {action: 'tcb_roster_public_slotting_tool_update', postId: postID, userId: userID, slot: slot, nounce: slotting_localize.ajax_nounce},
				success: function(response) {
					console.log(response);

					// Reload the attendance roster part of the page with AJAX
					jQuery('#attendanceRoster').load(document.URL +  ' #attendanceRoster .inner');
					jQuery('#slotTool').load(document.URL +  ' #slotTool .inner');
					jQuery('#slotToolButtons').load(document.URL +  ' #slotToolButtons');
				},
				error: function(response) {
					console.log(response);
				}
			});
		});
	});
})( jQuery );
