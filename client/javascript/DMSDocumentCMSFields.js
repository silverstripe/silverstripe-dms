(function($) {
  "use strict";

  $.entwine('ss', function($) {
    $('#DocumentTypeID ul li').entwine({
      onadd: function () {
        this.addClass('ui-button ss-ui-button ui-corner-all ui-state-default ui-widget ui-button-text-only');
        this.parents('ul').removeClass('ui-tabs-nav');
        if (this.find('input').is(':checked')) {
          this.addClass('selected');
        }
      },
      onclick: function(e) {
        $('#DocumentTypeID').find('li.selected').removeClass('selected');
        this.find('input').prop("checked", true);
        this.addClass('selected');
      }
    });

    $('.permissions input[name="CanViewType"], .permissions input[name="CanEditType"]').entwine({
      onchange: function () {
        if (!this.is(':checked')) {
          return;
        }

        var dropDown = this.closest('.fieldholder-small').next();
        if (this.val() === 'OnlyTheseUsers') {
          dropDown.removeClass('hide');
        } else {
          dropDown.addClass('hide');
        }
      },
      onadd: function () {
                this.trigger('change');
      }
    });

    $('.dmsdocment-actions ul li').entwine({
      onclick: function (e) {
        // Add active state to the current button
        $('.dmsdocment-actions ul li').removeClass('dms-active');
        this.addClass('dms-active');

        // Hide all inner field sections
        var panel = $('.dmsdocument-actionspanel:first');
        panel.find('> .fieldgroup > .fieldgroup-field').hide();

        // Show the correct group of controls
        panel.find('.'+this.data('panel')).show().parents('.fieldgroup-field').show();
      }
    });

    $('#Form_ItemEditForm_Embargo input, #Form_EditForm_Embargo input').entwine({
      onchange: function () {
        // Selected the date options
        if (this.attr('value') === 'Date') {
          $('.embargoDatetime').children().show();
          $('.embargoDatetime').show();
        } else {
          $('.embargoDatetime').hide();
        }
      }
    });

    $('#Form_ItemEditForm_Expiry input, #Form_EditForm_Expiry input').entwine({
      onchange: function () {
        // Selected the date options
        if (this.attr('value') === 'Date') {
          $('.expiryDatetime').children().show();
          $('.expiryDatetime').show();
        } else {
          $('.expiryDatetime').hide();
        }
      }
    });

    $('.dmsdocument-actionspanel').entwine({
      onadd: function () {
        // Do an initial show of the entire panel
        this.show();

        // Add some extra classes to the replace field containers to make it work with drag and drop uploading
        this.find('.replace').closest('div.fieldgroup-field').addClass('ss-upload').addClass('ss-uploadfield');

        // Add class and hide
        $('.dmsdocument-actionspanel .embargo input.date').closest('.fieldholder-small').addClass('embargoDatetime').hide();
        $('.dmsdocument-actionspanel .expiry input.date').closest('.fieldholder-small').addClass('expiryDatetime').hide();

        // Add placeholder attribute to date and time fields
        $('.dmsdocument-actionspanel .embargo input.date').attr('placeholder', 'dd-mm-yyyy');
        $('.dmsdocument-actionspanel .embargo input.time').attr('placeholder', 'hh:mm:ss');
        $('.dmsdocument-actionspanel .expiry input.date').attr('placeholder', 'dd-mm-yyyy');
        $('.dmsdocument-actionspanel .expiry input.time').attr('placeholder', 'hh:mm:ss');

        // Show the embargo panel when the page loads
        $('li[data-panel="embargo"]').click();

        // Set the initial state of the radio button and the associated dropdown hiding
        $('.dmsdocument-actionspanel .embargo input[type="radio"][checked]').change();
        $('.dmsdocument-actionspanel .expiry input[type="radio"][checked]').change();
      }
    });

    $('#Form_ItemEditForm_action_doDelete').entwine({
      onclick: function (e) {
        // Work out how many pages are left attached to this document
        var form = this.closest('form');
        var pagesCount = form.data('pages-count');
        var relationCount = form.data('relation-count');

        // Display an appropriate message
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

        if (!confirm(message)) {
          e.preventDefault();
          return false;
        } else {
          // User says "okay", so go ahead and do the action
          this._super(e);
        }
      }
    });
  });
}(jQuery));
