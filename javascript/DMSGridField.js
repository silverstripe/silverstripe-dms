(function($){
	"use strict";

	$.entwine('ss', function($) {

		$('#SectionID ul li').entwine({
			onadd: function() {
				this.addClass('ui-button ss-ui-button ui-corner-all ui-state-default ui-widget ui-button-text-only');
				this.parents('ul').removeClass('ui-tabs-nav');
			}
		});

		$('#SectionID input[type=radio]').entwine({
			onadd: function() {
				// Checks to see what radio button is selected
				if (this.is(':checked')) {
					this.change();
				}
			},
			onchange: function(e) {
				// Remove selected class from radio buttons
				$('#SectionID').find('li').removeClass('selected');
				//If radio button is checked then add the selected class
				if (this.is(':checked')) {
					this.parent('li').addClass('selected');
				}
			}
		});

		$('.ss-gridfield .action.dms-delete').entwine({
			onclick: function(e){
				//work out how many pages are left attached to this document
				var pagesCount = this.data('pages-count');
				var pagesCountAfterDeletion = pagesCount - 1;
				var addS = 's';
				if (pagesCountAfterDeletion === 1) {
					addS = '';
				}

				//display an appropriate message
				var message = '';
				if (this.hasClass('dms-delete-last-warning')) {
					message = "Permanently delete this document?\n\nWarning: this document is attached only to this page, deleting it here will delete it permanently.";
				}
				if (this.hasClass('dms-delete-link-only')) {
					message = "Unlink this document from this page?\n\nNote: it will remain attached to "+pagesCountAfterDeletion+" other page"+addS+".";
				}

				if(!confirm(message)) {
					e.preventDefault();
					return false;
				} else {
					//user says "okay", so go ahead and do the action
					this._super(e);
				}
			}
		});

		$('.ss-gridfield .dms-document-hidden').entwine({
			onadd: function() {
				this.closest('tr').addClass('dms-document-hidden-row');
			}
		});

		$('.cms-content-actions.south .ss-ui-action-destructive').entwine({
			confirmBeforeDelete: function() {
				var deleteButtons = $('button.dms-delete[data-pages-count=1]');

				//we have page with DMSDocuments on it, and we have documents that only exist on this page
				if (deleteButtons.length > 0) {
					var message = "Are you sure you want to delete this page? Deleting this page will delete "+deleteButtons.length;
					if (deleteButtons.length === 1) {
						message += " document that is associated only with this page. This document is:\n\n";
					} else {
						message += " documents that are associated only with this page. These documents are:\n\n";
					}

					//create a list of documents and their IDs
					deleteButtons.each(function(){
						var tr = $(this).closest('tr');
						message += tr.find('.col-ID').text() +' - '+ tr.find('.col-Title').text() +"\n";
					});

					if(!confirm(message)) {
						return false;
					}
				}

				return true;
			}
		});

		$('#Form_EditForm_action_deletefromlive').entwine({
			onclick: function(e) {
				if (this.confirmBeforeDelete()) {
					this._super(e);
				} else {
					return false;
				}
			}
		});

		$('#Form_EditForm_action_delete').entwine({
			onclick: function(e) {
				if (this.confirmBeforeDelete()) {
					this._super(e);
				} else {
					return false;
				}
			}
		});

		$('.ss-gridfield-item a.file-url').entwine({
			onclick: function(e) {
				//make sure the download link doesn't trigger a gridfield edit dialog
				window.open(this.attr('href'), '_blank');

				e.preventDefault();
				return false;
			}
		});

	});

}(jQuery));
