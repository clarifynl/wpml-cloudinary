/*global jQuery, ajaxurl */
(function ($) {
	'use strict';

	function onLoadEvent() {
		var form         = $('#wpml_cloudinary_options_form');
		var form_action  = form.find('#wpml_cloudinary_options_action');
		var submitButton = form.find(':submit');

		submitButton.click(
			function () {
				form_action.val($(this).attr('name'));
			}
		);

		form.submit(
			function () {
				if (!submitButton.attr('disabled')) {
					switch (form_action.val()) {
						case 'fix_missing_file_paths':
							wpml_cloudinary_options_form_working();
							wpml_cloudinary_fix_missing_file_paths();
							break;
						case 'fix_missing_cloudinary_meta':
							wpml_cloudinary_options_form_working();
							wpml_cloudinary_fix_missing_cloudinary_meta();
							break;
					}
				}

				form_action.val(0);
				return false;
			}
		);

		function wpml_update_status(message) {
			$(form).find('.status').html(message);
			if (message.length > 0) {
				$(form).find('.status').show();
			} else {
				$(form).find('.status').fadeOut();
			}
		}

		function wpml_cloudinary_options_form_working() {
			wpml_update_status('');
			submitButton.prop('disabled', true);
			$(form).find('.progress').fadeIn();
		}

		function wpml_cloudinary_options_form_finished(status) {
			submitButton.prop('disabled', false);
			$(form).find('.progress').fadeOut();
			wpml_update_status(status);
			window.setTimeout(
				function () {
					wpml_update_status('');
				}, 1000
			);
		}

		function wpml_cloudinary_fix_missing_file_paths() {
			$.ajax({
				url:      ajaxurl,
				type:     'POST',
				data:     {action: 'fix_missing_file_paths'},
				dataType: 'json',
				success:  function (ret) {
					wpml_update_status(ret.message);
					if (ret.left > 0) {
						wpml_cloudinary_fix_missing_file_paths();
					} else {
						wpml_cloudinary_options_form_finished(ret.message);
						$('#wpml_cloudinary_all_done').fadeIn();
					}
				},
				error: function (jqXHR, textStatus) {
					wpml_update_status('Duplicating missing file paths: please try again (' + textStatus + ')');
				}

			});
		}

		function wpml_cloudinary_fix_missing_cloudinary_meta() {
			$.ajax({
				url:      ajaxurl,
				type:     'POST',
				data:     {action: 'fix_missing_cloudinary_meta'},
				dataType: 'json',
				success:  function (ret) {
					wpml_update_status(ret.message);
					if (ret.left > 0) {
						wpml_cloudinary_fix_missing_cloudinary_meta();
					} else {
						wpml_cloudinary_options_form_finished(ret.message);
						$('#wpml_cloudinary_all_done').fadeIn();
					}
				},
				error: function (jqXHR, textStatus) {
					wpml_update_status('Duplicating missing cloudinary meta: please try again (' + textStatus + ')');
				}

			});
		}
	}

	$( window ).load(function() {
		onLoadEvent();
	});

})( jQuery );
