(function($) {

	$.entwine('ss', function($) {

		$('#DocumentTypeID ul li').entwine({
				onmatch: function() {
					this.addClass('ss-ui-button');
					this.parents('ul').removeClass('ui-tabs-nav');
				},
			});

		$('#DocumentTypeID ul li').entwine({
			onclick: function(e) {
				if ($('input[type=radio]:checked').length > 0) {
					this.addClass('selected');
				}
				//not.(this).removeClass('selected');
	/*			e.preventDefault();
				this.parents('.cms-preview').loadUrl(this.attr('href'));
				this.addClass('disabled');
				this.parents('.cms-preview-states').find('a').not(this).removeClass('disabled');
				//This hides all watermarks
				this.parents('.cms-preview-states').find('.cms-preview-watermark').hide();
				//Show the watermark for the current state
				this.siblings('.cms-preview-watermark').show();*/
			},
		});

		$('#Actions ul li').entwine({
			onclick: function(e) {
				e.preventDefault();
				this.parents('fieldset').find('#ReplaceFile').show();
			}
		});


	});

}(jQuery));