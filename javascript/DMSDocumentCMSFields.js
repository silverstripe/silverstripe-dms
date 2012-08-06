(function($) {

	$.entwine('ss', function($) {

		$('#DocumentTypeID ul li').entwine({
			onadd: function() {
				this.addClass('ui-button ss-ui-button ui-corner-all ui-state-default ui-widget ui-button-text-only');
				this.parents('ul').removeClass('ui-tabs-nav');
			},
		});

		$('#DocumentTypeID input[type=radio]').entwine({
			onadd: function() {
				if (this.is(':checked')) {
					this.change();
				}
			},
			onchange: function(e) {
				$('#DocumentTypeID').find('li').removeClass('selected');

				if (this.is(':checked')) {
					this.parent('li').addClass('selected');
				}
			}
		});

		$('#Actions ul li').entwine({
			onclick: function(e) {
				e.preventDefault();
				this.parents('fieldset').find('#ReplaceFile').show();
			}
		});


	});

}(jQuery));