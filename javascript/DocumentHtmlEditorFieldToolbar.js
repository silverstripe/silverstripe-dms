(function($) {
	"use strict";

	$.entwine('ss', function($) {

		/*$('form.htmleditorfield-linkform input[name=LinkType]').entwine({
			onchange: function(e) {
				this._super(e);

				var form = $('form.htmleditorfield-linkform');
				var show = false;

				if (this.attr('value') === 'document') {
					if (this.is(':checked')) {
						show = true;
					}
				}

				//hide or show the additional document link addition tool
				if (show) {
					form.find('.ss-add').show();
				} else {
					form.find('.ss-add').hide();
				}
			},
			onadd: function(e){
				this.change();
			}
		});*/

		$('form.htmleditorfield-linkform').entwine({
			insertLink: function() {
				var href, target = null;
				var checkedValue = this.find(':input[name=LinkType]:checked').val();
				if (checkedValue === 'document') {
					href = '[dms_document_link,id=' + this.find('.selected-document').data('document-id') + ']';

					// Determine target
					if(this.find(':input[name=TargetBlank]').is(':checked')) target = '_blank';

					var attributes = {
						href : href,
						target : target,
						class : 'documentLink',
						title : this.find('.selected-document').text()  //title is the text of the selected document
					};

					this.modifySelection(function(ed){
						ed.insertLink(attributes);
					});

					this.updateFromEditor();
					return false;
				} else {
					this._super();
				}
			},
			getCurrentLink: function() {
				var selectedEl = this.getSelection(), href = "", target = "", title = "", action = "insert", style_class = "";
				var linkDataSource = null;
				if(selectedEl.length) {
					if(selectedEl.is('a')) {
						linkDataSource = selectedEl;
					} else {
						linkDataSource = selectedEl = selectedEl.parents('a:first');
					}
				}
				if(linkDataSource && linkDataSource.length) this.modifySelection(function(ed){
					ed.selectNode(linkDataSource[0]);
				});

				// Is anchor not a link
				if (!linkDataSource.attr('href')) linkDataSource = null;

				if (linkDataSource) {
					href = linkDataSource.attr('href');
					target = linkDataSource.attr('target');
					title = linkDataSource.attr('title');
					style_class = linkDataSource.attr('class');
					href = this.getEditor().cleanLink(href, linkDataSource);
					action = "update";
				}

				//match a document or call the regular link handling
				if(href.match(/^\[dms_document_link(\s*|%20|,)?id=([0-9]+)\]?$/i)) {
					var returnArray = {
						LinkType: 'document',
						DocumentID: RegExp.$2,
						Description: title
					};

					//show the selected document
					$('.document-add-existing').selectdocument(returnArray.DocumentID,returnArray.Description);

					//select the correct radio button
					$('form.htmleditorfield-linkform input[name=LinkType][value=document]').click();

					return returnArray;
				} else {
					$('.document-add-existing').selectdocument();   //clear the selected document
					$('form.htmleditorfield-linkform .ss-add.ss-upload').hide();
					return this._super();
				}
			}
		});
	});

}(jQuery));
