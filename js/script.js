/**
 * ownCloud - ownbackup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Patrizio Bekerle <patrizio@bekerle.com>
 * @copyright Patrizio Bekerle 2015
 */

(function ($, OC) {

	$(document).ready(function () {
		$('#backup-button').click(function () {
			OCdialogs.confirm(
				t('ownbackup_backup', 'Are you sure you want to create a new backup?'),
				t('ownbackup_backup', 'Create backup?'),
				function( confirmed )
				{
					if ( confirmed )
					{
						var url = OC.generateUrl('/apps/ownbackup/create-backup');

						// show a message
						$('#backup-message').show();
						$('#cover').show();

						$.post(url).success(function (response) {
							// update the backup date selector
							updateSelectorHashItems( $('#backup-date-select'), response.timestamps, true );

							// hide message
							$('#backup-message').hide();
							$('#cover').hide();

							OCdialogs.info( response.message, t('ownbackup_backup', 'New backup'), null, true );
						});
					}
				},
				true
			);
		});

		$('#select-all-tables-button').click(function () {
			var $select = $('#backup-tables-select');
			$select.find('option').attr('selected', 'selected');
			$select.trigger('chosen:updated');
		});

		$('#deselect-all-tables-button').click(function () {
			var $select = $('#backup-tables-select');
			$select.find('option:selected').removeAttr('selected');
			$select.trigger('chosen:updated');
		});

		$('#restore-button').click(function (){

			OCdialogs.confirm(
				t('ownbackup_restore', 'Are you sure you want to restore the selected tables?'),
				t('ownbackup_restore', 'Restore tables?'),
				function( confirmed )
				{
					if ( confirmed )
					{
						// show a message
						$('#restore-message').show();
						$('#cover').show();

						var url = OC.generateUrl('/apps/ownbackup/restore-tables');
						var data = {
							timestamp: $('#backup-date-select').val(),
							tables: $('#backup-tables-select').val()
						};

						$.post(url, data).success(function (response) {
							// hide message
							$('#restore-message').hide();
							$('#cover').hide();

							OCdialogs.info( response.message, t('ownbackup_restore', 'Tables restored'), null, true );
						});
					}
				},
				true
			);
		});

		$('#backup-date-select').change( function() {
			var timestamp = parseInt( $(this).val() );

			if ( timestamp == 0 )
			{
				return;
			}

			var url = OC.generateUrl('/apps/ownbackup/fetch-tables');
			var data = {
				timestamp: timestamp
			};

			$.post(url, data).success(function (response) {
				$('#backup-tables-block').show();
				updateSelectorArrayItems( $('#backup-tables-select'), response.tables, false );
			});
		} );

		$('.chosen-select').chosen();
		$('button.icon-button').tipsy();
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
	 */
	function updateSelectorHashItems( $selectBox, items, addEmptyOption )
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
		for ( var key in items )
		{
			// check if key really exists
			if ( items.hasOwnProperty( key ) )
			{
				var item = items[key];

				// add new item
				$selectBox.append( $("<option></option>")
					.attr( "value", key ).text( item ) );
			}
		}

		// update chosen selector
		$selectBox.trigger( "chosen:updated" );
	}

})(jQuery, OC);