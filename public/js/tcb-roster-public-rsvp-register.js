 (function( $ ) {
	'use strict';

	$(document).ready(function() {

		jQuery(document).on("submit", '.rsvpFormUnregister', function(event) { 
			event.preventDefault();
			
			var rsvpUserID = +jQuery(this).find(".rsvpUserID").val();
			var rsvpPostID = +jQuery(this).find(".rsvpPostID").val();
			var rsvpSelection = +jQuery(this).find(".rsvpSelection").val();

			console.log('rsvpUserID: '+rsvpUserID);
			console.log('rsvpPostID: '+rsvpPostID);
			console.log('rsvpSelection: '+rsvpSelection);
		
			// Send data back to PHP function that updates ACF database	
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: rsvp_localize.ajax_url,
				data: {action: 'tcb_roster_public_attendance_roster_update', postId: rsvpPostID, userId: rsvpUserID, selection: rsvpSelection, nounce: rsvp_localize.ajax_nounce},
				success: function(response) {
					console.log(response);

					// Reload the attendance roster part of the page with AJAX
					jQuery('#attendanceRoster').load(document.URL +  ' #attendanceRoster .inner');
					jQuery('#slotTool').load(document.URL +  ' #slotTool .inner');
				},
				error: function(response) {
					console.log(response);
				}
			});
		}); 
	});
})( jQuery );
