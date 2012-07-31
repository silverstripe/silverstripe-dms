(function($) {

	$.entwine('ss', function($) {
		$('.ui-state-success-text').entwine({

			onmatch: function() {
				var form = this.closest('.cms-edit-form');
				console.log(form);
			},

			onunmatch: function() {
				var form = this.closest('.cms-edit-form');
				console.log(form);
			}

		});
	});
});