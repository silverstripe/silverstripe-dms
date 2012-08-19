(function($) {
	"use strict";

	$.entwine('ss', function($) {
		$('.document-add-existing').entwine({
			adddocument: function(document_id) {
				var page_id = $(this).closest('form').find(':input[name=ID]').val();

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
								}
		                    });

							$('.ss-add-files').append(fnout);
						}
					}
				);
			}
		});

		$('.document-add-existing .document-autocomplete').entwine({
			onmatch: function() {
				var self = this;
				this.autocomplete({
					source: 'admin/pages/adddocument/documentautocomplete',
					select: function(event, ui) {
						if(ui.item) {
							var document_id = ui.item.value;

							$(this).closest('.document-add-existing').adddocument(document_id);
						}
					}
				});
			}
		});

		// Add label to tree drop down button
		$('.document-add-existing .treedropdownfield-toggle-panel-link').entwine({
			onmatch: function() {
				this.prepend('<span>Browse by page</span>');
			}
		});

		// TODO - This will become redundant if the above function works
		// When clicking on the search input this removes the disabled state
		$('.document-add-existing .treedropdownfield-toggle-panel-link').entwine({
			onclick: function() {
				$(this).closest('.document-add-existing').find('input.document-autocomplete').prop('disabled', true);
			}
		});

		//TODO - When documents load in the document list. Toggle the visibilty of this. By default it should be hidden
		$('.document-add-existing input[name=PageSelector]').entwine({
			onchange: function(event) {
				$(this).closest('.document-add-existing').find('.document-list').load(
					'admin/pages/adddocument/documentlist?pageID=' + $(this).val(),
					null,
					function () {
						$(this).show();
					}
				);
				
			}
		});

		$('.document-add-existing a.add-document').entwine({
			onclick: function(event) {
				var document_id = $(this).data('document-id');

				$(this).closest('.document-add-existing').adddocument(document_id);
				$(this).closest('.document-list').hide();
				$(this).closest('.document-add-existing').find('input.document-autocomplete').prop('disabled', false);

				return false;
			}
		})

	});
}(jQuery));