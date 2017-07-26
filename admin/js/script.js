(function($) {

	$(function() {
		$('#yarakuzen-retrieve-translation').click(function() {
			$('#yarakuzen_submitdiv .spinner').addClass('is-active');

			var data = {
				'action': 'yarakuzen_retrieve_translation',
				'post_id': $('#post_ID').val()
			};

			$.post(ajaxurl, data, function(response) {
				var tmce = $('#wp-content-wrap').hasClass('tmce-active');

				if (tmce) {
					switchEditors.go( 'content', 'html' );
				}

				$('#title').val(response.title);
				$('#content').val(response.content);

				if (tmce) {
					switchEditors.go( 'content', 'tmce' );
				}

				$('#yarakuzen_submitdiv .spinner').removeClass('is-active');
			});
		});
	});

})(jQuery);
