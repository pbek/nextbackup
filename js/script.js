/**
 * ownCloud - nextbackup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Patrizio Bekerle <patrizio@bekerle.com>
 * @copyright Patrizio Bekerle 2015
 */
console.log("nextbackup");

(function ($, OC) {

	$(document).ready(function () {
		$('#nextbackup-backup-button').click(function () {
			console.log("nextbackup-backup-button");
			OC.dialogs.confirm(
				t('nextbackup_backup', 'Are you sure you want to create a new backup?'),
				t('nextbackup_backup', 'Create backup?'),
				function( confirmed )
				{
					if ( confirmed )
					{
						var url = OC.generateUrl('/apps/nextbackup/create-backup');

						// show a message
						$('#nextbackup-backup-message').show();
						$('#nextbackup-cover').show();

						$.post(url).success(function (response) {
							console.log(response);

							// update the backup date selector
							updateSelectorHashItems( $('#nextbackup-backup-date-select'), response.timestamps, true, true );

							// hide message
							$('#nextbackup-backup-message').hide();
							$('#nextbackup-cover').hide();
							$('#nextbackup-backup-tables-block').hide();

							OC.dialogs.info( response.message, t('nextbackup_backup', 'New backup'), function() {}, true );
						});
					}
				},
				true
			);
		});

		$('#nextbackup-select-all-tables-button').click(function () {
			var $select = $('#nextbackup-backup-tables-select');
			$select.find('option').attr('selected', 'selected');
			$select.trigger('chosen:updated');
		});

		$('#nextbackup-deselect-all-tables-button').click(function () {
			var $select = $('#nextbackup-backup-tables-select');
			$select.find('option:selected').removeAttr('selected');
			$select.trigger('chosen:updated');
		});

		$('#nextbackup-restore-button').click(function (){

			OC.dialogs.confirm(
				t('nextbackup_restore', 'Are you sure you want to restore the selected tables?'),
				t('nextbackup_restore', 'Restore tables?'),
				function( confirmed )
				{
					if ( confirmed )
					{
						// show a message
						$('#restore-message').show();
						$('#nextbackup-cover').show();

						var url = OC.generateUrl('/apps/nextbackup/restore-tables');
						var data = {
							timestamp: $('#nextbackup-backup-date-select').val(),
							tables: $('#nextbackup-backup-tables-select').val()
						};

						$.post(url, data).success(function (response) {
							// hide message
							$('#restore-message').hide();
							$('#nextbackup-cover').hide();

							OC.dialogs.info( response.message, t('nextbackup_restore', 'Tables restored'), function() {}, true );
						});
					}
				},
				true
			);
		});

		$('#nextbackup-backup-date-select').change( function() {
			var timestamp = parseInt( $(this).val() );

			if ( timestamp == 0 )
			{
				return;
			}

			var url = OC.generateUrl('/apps/nextbackup/fetch-tables');
			var data = {
				timestamp: timestamp
			};

			$.post(url, data).success(function (response) {
				$('#nextbackup-backup-tables-block').show();
				updateSelectorArrayItems( $('#nextbackup-backup-tables-select'), response.tables, false );
			});
		} );

		$('.chosen-select').chosen();
	});

	/**
	 * Updates a single select box with items from a simple array
	 *
	 * @param $selectBox the select box to update
	 * @param items the items to update it with
	 * @param addEmptyOption
	 */
	function updateSelectorArrayItems( $selectBox, items, addEmptyOption )
	{
		if ( addEmptyOption == undefined )
		{
			addEmptyOption = false;
		}

		$selectBox.empty();

		if ( addEmptyOption )
		{
			$selectBox.append( $("<option></option>") );
		}

		// try to add new items
		for ( var i = 0; i < items.length; i++ )
		{
			var item = items[i];
			// add new item
			$selectBox.append( $("<option></option>")
				.attr( "value", item ).text( item ) );
		}

		// update chosen selector
		$selectBox.chosen({ search_contains: true }).trigger( "chosen:updated" );
	}

	/**
	 * Updates a single select box with items from a hash (object)
	 *
	 * @param $selectBox the select box to update
	 * @param items the items to update it with
	 * @param addEmptyOption
	 * @param sort
	 */
	function updateSelectorHashItems( $selectBox, items, addEmptyOption, sort )
	{
		if ( addEmptyOption == undefined )
		{
			addEmptyOption = false;
		}

		if ( sort == undefined )
		{
			sort = false;
		}

		$selectBox.empty();

		if ( addEmptyOption )
		{
			$selectBox.append( $("<option></option>") );
		}

		var keys = Object.keys( items );

		if ( sort )
		{
			keys.reverse();
		}

		var len = keys.length;

		// add the new items
		for ( var i = 0; i < len; i++ )
		{
			key = keys[i];
			var item = items[key];

			// add new item
			$selectBox.append( $("<option></option>")
				.attr( "value", key ).text( item ) );
		}

		// update chosen selector
		$selectBox.trigger( "chosen:updated" );
	}

})(jQuery, OC);