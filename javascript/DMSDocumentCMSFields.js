(function($) {
	"use strict";

	$.entwine('ss', function($) {

		$('#DocumentTypeID ul li').entwine({
			onadd: function() {
				this.addClass('ui-button ss-ui-button ui-corner-all ui-state-default ui-widget ui-button-text-only');
				this.parents('ul').removeClass('ui-tabs-nav');
				if(this.find('input').is(':checked')) this.addClass('selected');
			},
			onclick: function(e){
				$('#DocumentTypeID').find('li.selected').removeClass('selected');
				this.find('input').prop("checked", true);
				this.addClass('selected');
			}
		});

/*		$('#DocumentTypeID input[type=radio]').entwine({
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
		});*/

		$('#Actions ul li').entwine({
			onclick: function(e) {

				console.log('LI CLICKED');

				//add active state to the current button
				$('#Actions ul li').removeClass('dms-active');
				this.addClass('dms-active');
				//$('li.dms-active').append('<span class="arrow"></span>');

				//hide all inner field sections
				var panel = $('.ActionsPanel:first');
				panel.find('div.fieldgroup-field').hide();

				//show the correct group of controls
				//panel.find('.'+this.data('panel')).closest('div.fieldgroup').show();
				panel.find('.'+this.data('panel')).show().parents('.fieldgroup-field').show();

			}
		});

		$('#Form_ItemEditForm_Embargo input, #Form_EditForm_Embargo input').entwine({
			onchange: function() {
				//selected the date options
				if (this.attr('value') === 'Date') {
					$('.embargoDatetime').children().show();
					$('.embargoDatetime').show();
				} else {
					$('.embargoDatetime').hide();
				}
			}
		});

		$('#Form_ItemEditForm_Expiry input, #Form_EditForm_Expiry input').entwine({
			onchange: function() {
				//selected the date options
				if (this.attr('value') === 'Date') {
					$('.expiryDatetime').children().show();
					$('.expiryDatetime').show();
				} else {
					$('.expiryDatetime').hide();
				}
			}
		});

		$('.ActionsPanel').entwine({
			onadd: function() {
				//do an initial show of the entire panel
				this.show();

				//add some extra classes to the replace field containers to make it work with drag and drop uploading
				this.find('.replace').closest('div.fieldgroup-field').addClass('ss-upload').addClass('ss-uploadfield');
				
				//Add placeholder attribute to date and time fields
				$('#Form_ItemEditForm_EmbargoedUntilDate-date').attr('placeholder', 'dd-mm-yyyy');
				$('#Form_ItemEditForm_EmbargoedUntilDate-time').attr('placeholder', 'hh:mm:ss');
				$('#Form_ItemEditForm_ExpireAtDate-date').attr('placeholder', 'dd-mm-yyyy');
				$('#Form_ItemEditForm_ExpireAtDate-time').attr('placeholder', 'hh:mm:ss');
				// We need to duplicate to work when adding documents
				$('#Form_EditForm_EmbargoedUntilDate-date').attr('placeholder', 'dd-mm-yyyy');
				$('#Form_EditForm_EmbargoedUntilDate-time').attr('placeholder', 'hh:mm:ss');
				$('#Form_EditForm_ExpireAtDate-date').attr('placeholder', 'dd-mm-yyyy');
				$('#Form_EditForm_ExpireAtDate-time').attr('placeholder', 'hh:mm:ss');


				$('#Form_ItemEditForm_EmbargoedUntilDate-date').closest('.fieldholder-small').addClass('embargoDatetime').hide();
				$('#Form_ItemEditForm_ExpireAtDate-date').closest('.fieldholder-small').addClass('expiryDatetime').hide();

				// We need to duplicate the above functions to work when Adding documents
				$('#Form_EditForm_EmbargoedUntilDate-date').closest('.fieldholder-small').addClass('embargoDatetime').hide();
				$('#Form_EditForm_ExpireAtDate-date').closest('.fieldholder-small').addClass('expiryDatetime').hide();

				//show the replace panel when the page loads
				$('li[data-panel="embargo"]').click();

				//set the initial state of the radio button and the associated dropdown hiding
				$('#Form_ItemEditForm_Embargo input[checked]').change();
				$('#Form_ItemEditForm_Expiry input[checked]').change();

				//Again we need to duplicate the above function to work when adding documents
				$('#Form_EditForm_Embargo input[checked]').change();
				$('#Form_EditForm_Expiry input[checked]').change();
			}
		});

		$('#Form_ItemEditForm_action_doDelete').entwine({
			onclick: function(e){
				//work out how many pages are left attached to this document
				var form = this.closest('form');
				var pagesCount = form.data('pages-count');
				var relationCount = form.data('relation-count');

				//display an appropriate message
				var message = '';
				if (pagesCount > 1 || relationCount > 0) {
					var pages = '';
					if (pagesCount > 1) {
						pages = "\nWarning: doc is attached to a total of "+pagesCount+" pages. ";
					}
					var references = '';
					var referencesWarning = '';
					if (relationCount > 0) {
						var pname = 'pages';
						referencesWarning = "\n\nBefore deleting: please update the content on the pages where this document is referenced, otherwise the links on those pages will break.";
						if (relationCount === 1) {
							pname = 'page';
							referencesWarning = "\n\nBefore deleting: please update the content on the page where this document is referenced, otherwise the links on that page will break.";
						}
						references = "\nWarning: doc is referenced in the text of "+relationCount +" "+pname+".";
					}
					message = "Permanently delete this document and remove it from all pages where it is referenced?\n"+pages+references+"\n\nDeleting it here will permanently delete it from this page and all other pages where it is referenced."+referencesWarning;
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