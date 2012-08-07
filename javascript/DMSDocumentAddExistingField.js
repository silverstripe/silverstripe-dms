(function($) {
	$('.document-add-existing .document-autocomplete').entwine({
		onmatch: function() {
			$(this).autocomplete({
				source: 'admin/pages/adddocument/documentautocomplete'
			});
		}
	});
}(jQuery));