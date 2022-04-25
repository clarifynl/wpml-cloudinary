/*global jQuery, ajaxurl */
(function ($) {
	'use strict';

	function onLoadEvent() {
		var form         = $('#wpml_cloudinary_options_form');
		var form_action  = form.find('#wpml_cloudinary_options_action');
		var actionButton = form.find(':button');

		actionButton.click(
			function () {
				form_action.val($(this).attr('name'));
				var action = $(this).parent('.action');

				if (!$(this).attr('disabled')) {
					switch (form_action.val()) {
						case 'fix_missing_file_paths':
							wpml_cloudinary_options_form_working(action);
							wpml_cloudinary_fix_missing_file_paths(action);
							break;
						case 'fix_missing_cloudinary_meta':
							wpml_cloudinary_options_form_working(action);
							wpml_cloudinary_fix_missing_cloudinary_meta(action);
							break;
						case 'fix_incorrect_wordpress_meta':
							wpml_cloudinary_options_form_working(action);
							wpml_cloudinary_fix_incorrect_wordpress_meta(action);
							break;
					}
				}

				form_action.val(0);
			}
		);

		function wpml_update_status(action, message) {
			$(action).find('.status').html(message);
			if (message.length > 0) {
				$(action).find('.status').show();
			} else {
				$(action).find('.status').fadeOut();
			}
		}

		function wpml_cloudinary_options_form_working(action) {
			wpml_update_status(action, '');
			$(action).find(':button').prop('disabled', true);
			$(action).find('.progress').fadeIn();
		}

		function wpml_cloudinary_options_form_finished(action, status) {
			$(action).find(':button').prop('disabled', false);
			$(action).find('.progress').fadeOut();
			wpml_update_status(action, status);
			window.setTimeout(
				function () {
					wpml_update_status(action, '');
				}, 1000
			);
		}

		function wpml_cloudinary_fix_missing_file_paths(action) {
			$.ajax({
				url:      ajaxurl,
				type:     'POST',
				data:     {action: 'fix_missing_file_paths'},
				dataType: 'json',
				success:  function (ret) {
					wpml_update_status(action, ret.message);
					if (ret.left > 0) {
						wpml_cloudinary_fix_missing_file_paths(action);
					} else {
						wpml_cloudinary_options_form_finished(action, ret.message);
						$('#wpml_cloudinary_all_done').fadeIn();
					}
				},
				error: function (jqXHR, textStatus) {
					wpml_update_status(action, 'Duplicating missing file paths: please try again (' + textStatus + ')');
				}

			});
		}

		function wpml_cloudinary_fix_missing_cloudinary_meta(action) {
			$.ajax({
				url:      ajaxurl,
				type:     'POST',
				data:     {action: 'fix_missing_cloudinary_meta'},
				dataType: 'json',
				success:  function (ret) {
					wpml_update_status(action, ret.message);
					if (ret.left > 0) {
						wpml_cloudinary_fix_missing_cloudinary_meta(action);
					} else {
						wpml_cloudinary_options_form_finished(action, ret.message);
						$('#wpml_cloudinary_all_done').fadeIn();
					}
				},
				error: function (jqXHR, textStatus) {
					wpml_update_status(action, 'Duplicating missing cloudinary meta: please try again (' + textStatus + ')');
				}

			});
		}

		function wpml_cloudinary_fix_incorrect_wordpress_meta(action) {
			$.ajax({
				url:      ajaxurl,
				type:     'POST',
				data:     {action: 'fix_incorrect_wordpress_meta'},
				dataType: 'json',
				success:  function (ret) {
					wpml_update_status(action, ret.message);
					if (ret.left > 0) {
						wpml_cloudinary_fix_incorrect_wordpress_meta(action);
					} else {
						wpml_cloudinary_options_form_finished(action, ret.message);
						$('#wpml_cloudinary_all_done').fadeIn();
					}
				},
				error: function (jqXHR, textStatus) {
					console.log(jqXHR, textStatus);
					wpml_update_status(action, 'Duplicating incorrect wordpress meta: please try again (' + textStatus + ')');
				}

			});
		}
	}

	$( window ).load(function() {
		onLoadEvent();
	});

})( jQuery );
