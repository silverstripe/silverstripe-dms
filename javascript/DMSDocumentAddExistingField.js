(function($) {
	$('.document-add-existing .document-autocomplete').entwine({
		onmatch: function() {
			$(this).autocomplete({
				source: 'admin/pages/adddocument/documentautocomplete',
				select: function(event, ui) {
					if(ui.item) {
						var page_id = $(this).closest('form').find(':input[name=ID]').val();
						var document_id = ui.item.value;

						jQuery.ajax('admin/pages/adddocument/linkdocument?ID=' + page_id + '&documentID=' + document_id);
					}
				}
			});
		}
	});
}(jQuery));