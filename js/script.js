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
		$('#hello').click(function () {
			alert('Hello from your script file');
		});

		$('#restore-button').click(function () {
			if ( confirm( "Are you sure you want to restore the selected tables?" ) )
			{
				var url = OC.generateUrl('/apps/ownbackup/restore-tables');
				var data = {
					tables: $('#backup-tables-select').val()
				};

				$.post(url, data).success(function (response) {
					$('#echo-result').text(response.message);
				});
			}
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
				updateSelectorArrayItems( $('#backup-tables-select'), response.tables );
			});
		} );

		$()

		$('.chosen-select').chosen();
	});

	/**
	 * Updates a single select box with items from a simple array
	 *
	 * @param $selectBox the select box to update
	 * @param items the items to update it with
	 */
	function updateSelectorArrayItems( $selectBox, items )
	{
		$selectBox.empty();
		$selectBox.append( $("<option></option>") );

		// try to add new items
		for ( var i = 0; i < items.length; i++ )
		{
			var item = items[i];
			// add new item
			$selectBox.append( $("<option></option>")
				.attr( "value", item ).text( item ) );
		}

		// update chosen selector
		$selectBox.chosen().trigger( "chosen:updated" );
	}
})(jQuery, OC);