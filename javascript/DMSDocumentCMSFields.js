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
				//add active state to the current button
				$('#Actions ul li').removeClass('dms-active');
				this.addClass('dms-active');
				//$('li.dms-active').append('<span class="arrow"></span>');

				//hide all inner field sections
				var panel = $('#ActionsPanel');
				panel.find('div.fieldgroup-field').hide();

				//show the correct group of controls
				panel.find('.'+this.data('panel')).closest('div.fieldgroup-field').show();
			}
		});

		$('#Form_ItemEditForm_Embargo input').entwine({
			onchange: function() {
				//selected the date options
				if (this.attr('value') == 3) {
					$('.embargoDatetime').show();
				} else {
					$('.embargoDatetime').hide();
				}
			}
		});

		$('#Form_ItemEditForm_Expiry input').entwine({
			onchange: function() {
				//selected the date options
				if (this.attr('value') == 1) {
					$('.expiryDatetime').show();
				} else {
					$('.expiryDatetime').hide();
				}
			}
		});

		$('#ActionsPanel').entwine({
			onadd: function() {
				//do an initial show of the entire panel
				this.show();

				//move the delete button into the panel
				$('#Actions ul').append('<li class="delete-button-appended"></li>');
				$('.delete-button-appended').append($('#Form_ItemEditForm_action_doDelete'));

				//add some extra classes to the replace field containers to make it work with drag and drop uploading
				this.find('.replace').closest('div.fieldgroup-field').addClass('ss-upload').addClass('ss-uploadfield');

				$('#Form_ItemEditForm_EmbargoedUntilDate-date').closest('.fieldholder-small').addClass('embargoDatetime').hide();
				$('#Form_ItemEditForm_ExpireAtDate-date').closest('.fieldholder-small').addClass('expiryDatetime').hide();

				//show the replace panel when the page loads
				$('li[data-panel="embargo"]').click();

				//set the initial state of the radio button and the associated dropdown hiding
				$('#Form_ItemEditForm_Embargo input[checked]').change();
				$('#Form_ItemEditForm_Expiry input[checked]').change();
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