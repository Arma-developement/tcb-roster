 (function( $ ) {
	'use strict';

	$(document).ready(function() {
		/*
		// Find my username
		var name = jQuery(this).find(".memberName:first").val();
		//console.log(name);
		
		// Build an array of people already slotted in slotMember 
		// nope var slottedNames = jQuery('#slotTool').find(".slotMember").toArray();	
		var slottedNames = jQuery('.slotMember').map(function() {
			return jQuery(this).text()
		}).get();
		
		//console.log(slottedNames);
		
		// Then if i'm in that list, set a variable to say im already slotted
		var isAlreadySlotted = '';
		//if (jQuery.inArray(name, slottedNames) != -1)
		if (slottedNames.indexOf(name) > -1)
		{	
			console.log('is slotted somewhere');
			isAlreadySlotted = 'true';
		} else {
			console.log('isnt slotted somewhere');
			isAlreadySlotted = 'false';
		}
		*/

		// jQuery(document).on("submit", '.rsvpFormUnregister', function(event) { 
		// 	event.preventDefault();
			
		// 	var rsvpUserID = +jQuery(this).find(".rsvpUserID").val();
		// 	var rsvpPostID = +jQuery(this).find(".rsvpPostID").val();
		// 	var rsvpSelection = +jQuery(this).find(".rsvpSelection").val();


		jQuery(".slotForm").submit(function(event) {
			event.preventDefault();

			var userID = +jQuery(this).find(".userID").val();
			var postID = +jQuery(this).find(".postID").val();
			var slot = jQuery(this).find(".slot").val();

			console.log('userID: ' + userID);
			console.log('postID: ' + postID);
			console.log('slot: ' + slot);

			var slotArray = slot.split (",");
			var component = '#slotToolSlot-' + (+slotArray[1]) + "-" + (+slotArray[2]);
			console.log( component );

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

					// Update avatar on page
					//jQuery("#"+formID).find("input.slotIcon").css("background-image", "url(" + profilePic + ")");
					// Update member name on page
					//jQuery("#"+formID).find(".slotMember").html(name);
				},
				error: function(response) {
					console.log(response);
				}
			});
			
			// var formID = jQuery(this).parent().attr('id');
			// var form = jQuery(this);
			
			// var currentlySlotted = jQuery("#"+formID).find(".slotMember").text(); 
			// var name = jQuery(this).find(".memberName").val();
			// var postID = jQuery(this).find(".postID").val();
			// var acfPath = jQuery(this).find(".acfPath").val();
			// var profilePic = jQuery(this).find(".profilePic").val();
			
			//console.log(isAlreadySlotted);
			
			//console.log(formID);
			//console.log('currently slotted: '+currentlySlotted);
			//console.log('member name: '+name);
			//console.log('post: '+postID);
			//console.log('acf path: '+acfPath);
		
			/*
			// Slot is empty
			if (currentlySlotted == '' && isAlreadySlotted == 'false')  {
				console.log ('slot was empty, now filling it');
				jQuery.ajax({
					traditional: true,
					url: localize.ajax_url + "?action=tcb_roster_public_slotting_tool_update",
					type: 'post',
					dataType: "json",
					// build query string manually as the serialize was URI encoding the commas
					data: 'acf-path='+acfPath+'&post-id='+postID+'&member-name='+name,  
					success: function(data) {
						console.log("SUCCESS!");
						// Update avatar on page
						jQuery("#"+formID).find("input.slotIcon").css("background-image", "url(" + profilePic + ")");
						// Update member name on page
						jQuery("#"+formID).find(".slotMember").html(name);
						console.log('added');
						isAlreadySlotted = 'true';
					},
					error: function(data) {
						console.log("FAILURE");
					}
				});
			} else if (currentlySlotted == name ) {
				console.log ('is already slotted');
				// delete user but only if it's me
				name = '',
				jQuery.ajax({
					traditional: true,
					url: localize.ajax_url + "?action=tcb_roster_public_slotting_tool_update",
					type: 'post',
					dataType: "json",
					// build query string manually as the serialize was URI encoding the commas
					data: 'acf-path='+acfPath+'&post-id='+postID+'&member-name='+name,  
					success: function(data) {
						console.log("SUCCESS!");
						// Update avatar on page
						jQuery("#"+formID).find("input.slotIcon").css("background-image", "none");
						// Update member name on page
						jQuery("#"+formID).find(".slotMember").html(name);
						isAlreadySlotted = 'false';
						console.log('removed');
					},
					error: function(data) {
						console.log("FAILURE");
					}
				});
			} else {
				if(isAlreadySlotted == 'false' ) {
					console.log('do nothing, its not me');
					// message
					jQuery.toast({
						content: "Slot taken"
					})
				} 
				if(isAlreadySlotted == 'true') {
					console.log('im already slotted somewhere else');
					// message
					jQuery.toast({
						content: "You're already slotted"
					})
				}
			}
			*/
		});
	});
})( jQuery );
