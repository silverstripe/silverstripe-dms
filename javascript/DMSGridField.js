(function ($) {
  "use strict";

  $.entwine('ss', function ($) {
    $('.ss-gridfield-item a.file-url').entwine({
      onclick: function (e) {
        // Make sure the download link doesn't trigger a gridfield edit dialog
        window.open(this.attr('href'), '_blank');

        e.preventDefault();
        return false;
      }
    });
  });
}(jQuery));
