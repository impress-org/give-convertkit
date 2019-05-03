/**
 * Give ConvertKit Admin Forms JS
 *
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ($) {

	/**
	 * Toggle Conditional Form Fields
	 *
	 *  @since: 1.0
	 */
	var toggle_convertkit_fields = function () {

		var convertkit_option = $('input[name="_give_convertkit_override_option"]');

		convertkit_option.on('change', function () {

			var convertkit_option_val = $(this).filter(':checked').val();

			if (typeof convertkit_option_val == 'undefined') {
				return;
			}

			if (convertkit_option_val === 'disabled' || convertkit_option_val == 'default') {
				$('.give-convertkit-field-wrap').hide();
			} else {
				$('.give-convertkit-field-wrap').show();
			}

		}).change();

	};


	//On DOM Ready
	$(function () {

		toggle_convertkit_fields();

	});


})(jQuery);