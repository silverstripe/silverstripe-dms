(function($) {
	"use strict";

	$.entwine('ss', function($) {

		$('.document-add-existing .document-autocomplete').entwine({
			onmatch: function() {
				var self = this;
				this.autocomplete({
					source: 'admin/pages/adddocument/documentautocomplete',
					select: function(event, ui) {
						if(ui.item) {
							var page_id = $(this).closest('form').find(':input[name=ID]').val();
							var document_id = ui.item.value;

							jQuery.ajax(
								'admin/pages/adddocument/linkdocument?ID=' + page_id + '&documentID=' + document_id,
								{
									dataType: 'json',
									success: function(data, textstatus) {
										var fn = window.tmpl.cache['ss-uploadfield-addtemplate'];
										var fnout = fn({
											files: [data],
												formatFileSize: function (bytes) {
												if (typeof bytes !== 'number') return '';
												if (bytes >= 1000000000) return (bytes / 1000000000).toFixed(2) + ' GB';
												if (bytes >= 1000000) return (bytes / 1000000).toFixed(2) + ' MB';
												return (bytes / 1000).toFixed(2) + ' KB';
											},
											options: self.fileupload('option')
	                                    });

										$('.ss-add-files').append(fnout);
									}
								}
							);
						}
					}
				});
			}
		});

	});
}(jQuery));