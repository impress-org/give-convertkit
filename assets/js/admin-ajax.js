/**
 * Give ConvertKit Admin AJAX JS
 *
 * @description: The Give ConvertKit Admin AJAX scripts.
 * @package:     Give
 * @since:       1.0
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

var give_vars;
jQuery.noConflict();
jQuery(document).ready(function ($) {

	/**
	 * Refresh Lists Button click.
	 */
	$('.give-reset-convertkit-button').on('click', function (e) {
		e.preventDefault();

		var field_type = $(this).data('field_type');

		var data = {
				action: $(this).data('action'),
				field_type: field_type,
				post_id: give_vars.post_id
			},
			refresh_button = $(this),
			spinner = $(this).next();

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: data,
			beforeSend: function () {
				spinner.addClass('is-active');
			},
			success: function (res) {
				if (true == res.success) {
					//Replace select options.
					if (field_type == 'select') {
						$('.give-convertkit-list-select').empty().append(res.data.lists);
					} else {
						$('.give-convertkit-list-wrap').empty().append(res.data.lists);
					}

					refresh_button.hide();
					spinner.removeClass('is-active');
				}
			}
		});
	});

	/**
	 * Refresh Tags Button click.
	 */
	$('.give-reset-tags-convertkit-button').on('click', function (e) {
		e.preventDefault();

		var field_type = $(this).data('field_type');

		var data = {
				action: $(this).data('action'),
				field_type: field_type,
				post_id: give_vars.post_id
			},
			refresh_button = $(this),
			spinner = $(this).next();

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: data,
			beforeSend: function () {
				spinner.addClass('is-active');
			},
			success: function (res) {
				if (true == res.success) {
					//Replace select options.
					if (field_type == 'select') {
						$('.give-convertkit-tag-select').empty().append(res.data.lists);
					} else {
						$('.give-convertkit-tag-wrap').empty().append(res.data.lists);
					}

					refresh_button.hide();
					spinner.removeClass('is-active');
				}
			}
		});
	});

});
