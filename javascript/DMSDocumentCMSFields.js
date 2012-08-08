(function($) {
	"use strict";

	$.entwine('ss', function($) {

		$('#DocumentTypeID ul li').entwine({
			onadd: function() {
				this.addClass('ui-button ss-ui-button ui-corner-all ui-state-default ui-widget ui-button-text-only');
				this.parents('ul').removeClass('ui-tabs-nav');
			}
		});

		$('#DocumentTypeID input[type=radio]').entwine({
			onadd: function() {
				// Checks to see what radio button is selected
				if (this.is(':checked')) {
					this.change();
				}
			},
			onchange: function(e) {
				// Remove selected class from radio buttons
				$('#DocumentTypeID').find('li').removeClass('selected');
				//If radio button is checked then add the selected class
				if (this.is(':checked')) {
					this.parent('li').addClass('selected');
				}
			}
		});

		$('#Actions ul li').entwine({
			onclick: function(e) {
				//TODO - Fix so when clicking on an action it toggles the correct details inside the ActionsPanel
				// Currently this just hides the whole ActionsPanel when you click an action button
				this.parents('fieldset').find('#ActionsPanel').hide();
			}
		});

		$('#Form_ItemEditForm_action_doDelete').entwine({
			onclick: function(e){
				//work out how many pages are left attached to this document
				var form = this.closest('form');
				var pagesCount = form.data('pages-count');

				//display an appropriate message
				var message = '';
				if (pagesCount > 1) {
					message = "Permanently delete this document and remove it from all pages where it is referenced?\n\nWarning: this document is attached to a total of "+pagesCount+" pages. Deleting it here will permanently delete it from this page and all other pages where it is referenced.";
				} else {
					message = "Permanently delete this document and remove it from this page?\n\nNotice: this document is only attached to this page, so deleting it won't affect any other pages.";
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


	});

}(jQuery));