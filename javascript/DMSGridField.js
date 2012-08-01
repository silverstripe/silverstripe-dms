(function($){

	$.entwine('ss', function($) {


		$('.ss-gridfield .action.dms-delete').entwine({
			onclick: function(e){
				//work out how many pages are left attached to this document
				var pagesCount = this.data('pages-count');
				var pagesCountAfterDeletion = pagesCount - 1;
				var addS = 's';
				if (pagesCountAfterDeletion == 1) addS = '';

				//display an appropriate message
				var message = '';
				if (this.hasClass('dms-delete-last-warning')) message = "Permanently delete this document?\n\nWarning: this document is attached only to this page, deleting it here will delete it permanently.";
				if (this.hasClass('dms-delete-link-only')) message = "Unlink this document from this page?\n\nNote: it will remain attached to "+pagesCountAfterDeletion+" other page"+addS+".";

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
