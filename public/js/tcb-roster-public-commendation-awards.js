 (function( $ ) {
	'use strict';

	$(document).ready(function() {

		// Registered on every page (see class-tcb-roster-public.php), but the page data is only
		// present when the [tcbp_public_award_commendations] shortcode actually rendered.
		if ( typeof tcbpCommendationAwardData === 'undefined' ) {
			return;
		}

		var data = tcbpCommendationAwardData;

		function rowUserIds( $row ) {
			var ids = [];
			$row.find('.tcb_award_chip_list li').each(function() {
				ids.push( +jQuery(this).data('user-id') );
			});
			return ids;
		}

		function addChip( $row, userId, displayName ) {
			var $li = jQuery('<li>').attr('data-user-id', userId).text( displayName + ' ' );
			jQuery('<button type="button" class="tcb_award_chip_remove">&times;</button>').appendTo( $li );
			$row.find('.tcb_award_chip_list').append( $li );
		}

		// Remove a player from a row.
		jQuery(document).on('click', '.tcb_award_chip_remove', function() {
			jQuery(this).closest('li').remove();
		});

		// Filter the player list as the add-player input is typed into.
		jQuery(document).on('keyup', '.tcb_award_add_player', function() {
			var $input = jQuery(this);
			var $row = $input.closest('.tcb_award_row');
			var $results = $row.find('.tcb_award_add_player_results');
			var term = $input.val().trim().toLowerCase();

			$results.empty();

			if ( term.length < 2 ) {
				return;
			}

			var alreadyAdded = rowUserIds( $row );

			var matches = data.players.filter(function( player ) {
				return player.display_name.toLowerCase().indexOf( term ) !== -1 && alreadyAdded.indexOf( +player.user_id ) === -1;
			}).slice( 0, 15 );

			matches.forEach(function( player ) {
				var $li = jQuery('<li>').text( player.display_name ).attr('data-user-id', player.user_id);
				$results.append( $li );
			});
		});

		// Add a player from the filtered results.
		jQuery(document).on('click', '.tcb_award_add_player_results li', function() {
			var $result = jQuery(this);
			var $row = $result.closest('.tcb_award_row');

			addChip( $row, +$result.data('user-id'), $result.text() );

			$row.find('.tcb_award_add_player').val('');
			$row.find('.tcb_award_add_player_results').empty();
		});

		// Build and submit the full award set.
		jQuery('#tcbCommendationAwardCommit').on('click', function() {
			var $button = jQuery(this);
			var $result = jQuery('#tcbCommendationAwardResult');

			var awards = {};
			jQuery('.tcb_award_row').each(function() {
				var $row = jQuery(this);
				var group = $row.data('group');
				var slug = $row.data('slug');
				var userIds = rowUserIds( $row );

				if ( ! userIds.length ) {
					return;
				}
				if ( ! awards[ group ] ) {
					awards[ group ] = {};
				}
				awards[ group ][ slug ] = userIds;
			});

			if ( ! Object.keys( awards ).length ) {
				$result.text('Nothing selected to award.');
				return;
			}

			$button.prop('disabled', true).text('Committing…');
			$result.text('');

			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: data.ajaxUrl,
				data: {
					action: 'tcbp_public_commendation_award_commit',
					postId: data.postId,
					awards: JSON.stringify( awards ),
					nonce: data.nonce
				},
				success: function( response ) {
					if ( response && response.success ) {
						$result.text('Awards committed and announced.');
					} else {
						$result.text( 'Failed: ' + ( response && response.data ? response.data : 'unknown error' ) );
						$button.prop('disabled', false).text('Commit Awards');
					}
				},
				error: function() {
					$result.text('Failed: request error.');
					$button.prop('disabled', false).text('Commit Awards');
				}
			});
		});
	});
})( jQuery );
