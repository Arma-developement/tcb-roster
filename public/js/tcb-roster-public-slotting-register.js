 (function( $ ) {
	'use strict';

	$(document).ready(function() {

		jQuery(".slotForm").submit(function(event) {
			event.preventDefault();

			var userID = +jQuery(this).find(".userID").val();
			var postID = +jQuery(this).find(".postID").val();
			var slot = jQuery(this).find(".slot").val();
			var slotArray = slot.split (",");
			var component = '#slotToolSlot-' + (+slotArray[1]) + "-" + (+slotArray[2]);

			// console.log('userID: ' + userID);
			// console.log('postID: ' + postID);
			// console.log('slot: ' + slot);
			// console.log( component );

			// Send data back to PHP function that updates ACF database	
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: localize.ajax_url,
				data: {action: 'tcb_roster_public_slotting_tool_update', postId: postID, userId: userID, slot: slot, nounce: localize.ajax_nounce},
				success: function(response) {
					console.log(response);

					// Reload the attendance roster part of the page with AJAX
					//jQuery('#attendanceRoster').load(document.URL +  ' #attendanceRoster>*');
					jQuery(component).load(document.URL +  ' ' + component);
				},
				error: function(response) {
					console.log(response);
				}
			});
		});
	});
})( jQuery );
