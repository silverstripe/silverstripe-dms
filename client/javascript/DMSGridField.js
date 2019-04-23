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

    $('.ss-gridfield-item a.dms-doc-sets-link').entwine({
      onclick: function (e){
        // Prevent the initial flash of the gridfield's edit form
        e.preventDefault();
        document.location.href=this.attr('href');
        return false;
      }
    });
  });
}(jQuery));
