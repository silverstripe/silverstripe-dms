(function ($) {
  "use strict";

  $.entwine('ss', function ($) {
    $('form.htmleditorfield-linkform input[name=LinkType]').entwine({
      onchange: function (e) {
        this._super(e);

        var form = $('form.htmleditorfield-linkform');
        var show = false;

        if (this.attr('value') === 'document') {
          if (this.is(':checked')) {
            show = true;
          }
        }

        // Hide or show the additional document link addition tool
        if (show) {
          form.find('.ss-add').show();
        } else {
          form.find('.ss-add').hide();
        }
      },
      onadd: function (e) {
        this.change();
      }
    });

    $('form.htmleditorfield-linkform').entwine({
      getShortcodeKey: function () {
        return this.find(':input[name=DMSShortcodeHandlerKey]').val();
      },
      insertLink: function () {
        var href, target = null;
        var checkedValue = this.find(':input[name=LinkType]:checked').val();
        if (checkedValue === 'document') {
          href = '[' + this.getShortcodeKey() + ',id=' + this.find('.selected-document').data('document-id') + ']';

          // Determine target
          if (this.find(':input[name=TargetBlank]').is(':checked')) {
            target = '_blank';
          }

          var attributes = {
            href: href,
            target: target,
            class: 'documentLink',
            // Title is the text of the selected document
            title: this.find('.selected-document').text()
          };

          this.modifySelection(function (ed) {
            ed.insertLink(attributes);
          });

          this.updateFromEditor();
          return false;
        } else {
          this._super();
        }
      },
      getCurrentLink: function () {
        var selectedEl = this.getSelection(), href = "", target = "", title = "", action = "insert", style_class = "";
        var linkDataSource = null;
        if (selectedEl.length) {
          if (selectedEl.is('a')) {
            linkDataSource = selectedEl;
          } else {
            linkDataSource = selectedEl = selectedEl.parents('a:first');
          }
        }

        if (linkDataSource && linkDataSource.length) {
          this.modifySelection(function (ed) {
            ed.selectNode(linkDataSource[0]);
          });
        }

        // Is anchor not a link
        if (!linkDataSource.attr('href')) {
          linkDataSource = null;
        }

        if (linkDataSource) {
          href = linkDataSource.attr('href');
          target = linkDataSource.attr('target');
          title = linkDataSource.attr('title');
          style_class = linkDataSource.attr('class');
          href = this.getEditor().cleanLink(href, linkDataSource);
          action = "update";
        }

        // Match a document or call the regular link handling
        if (href.match(new RegExp('^\\[' + this.getShortcodeKey() + '(\s*|%20|,)?id=([0-9]+)\\]?$', 'i'))) {
          var returnArray = {
            LinkType: 'document',
            DocumentID: RegExp.$2,
            Description: title
          };

          // Show the selected document
          $('.document-add-existing').selectdocument(returnArray.DocumentID,returnArray.Description);

          // Select the correct radio button
          $('form.htmleditorfield-linkform input[name=LinkType][value=document]').click();

          return returnArray;
        } else {
          // Clear the selected document
          $('.document-add-existing').selectdocument();
          $('form.htmleditorfield-linkform .ss-add.ss-upload').hide();
          return this._super();
        }
      }
    });
  });
}(jQuery));
