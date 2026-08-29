(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(function ($) {



		/**
		 * Control tab
		 */
		/*
		$("#pi_sn_show_all").on("change", function () {
			if ($(this).is(":checked")) {
				$("#pi_control .row").not("#row_pi_sn_show_all").fadeOut();
			} else {
				$("#pi_control .row").not("#row_pi_sn_show_all").fadeIn()
			}
		});
		$("#pi_sn_show_all").trigger('change');
		*/
		/* End control tab */

		/**
		 * Product selection tab
		 */
		$("#pi_sn_product_selection").on("change", function () {
			var selected = $("#pi_sn_product_selection option:selected").val();
			virtual_name_location(selected);
			selected_product(selected);
			selected_category(selected);
			orders(selected);
		});

		$("#pi_sn_product_selection").trigger('change');

		$("#pi_sn_selected_product").selectWoo({
			ajax: {
				url: window.pi_ajax_object.ajax_url,
				dataType: 'json',
				type: "GET",
				delay: 250,
				data: function (params) {
					return {
						keyword: params.term,
						action: "pi_search_product"
					};
				},
				processResults: function (data) {
					return {
						results: data
					};

				},
			}
		});

		$("#pi_sn_selected_category").selectWoo({
			ajax: {
				url: window.pi_ajax_object.ajax_url,
				dataType: 'json',
				type: "GET",
				delay: 250,
				data: function (params) {
					return {
						keyword: params.term,
						action: "pi_search_category"
					};
				},
				processResults: function (data) {
					return {
						results: data
					};

				},
			}
		});

		$("#pi_sn_order_status, #pi_sn_order_tags").selectWoo();
		/* End product selection tab */

	});

	function virtual_name_location(selected) {
		if (selected == "recently-viewed-products" || selected == "selected-products" || selected == "selected-categories") {
			$("#virtual-name-location").fadeIn();
		} else {
			$("#virtual-name-location").fadeOut();
		}
	}

	function selected_product(selected) {
		if (selected == "selected-products") {
			$("#selected-products").fadeIn();
		} else {
			$("#selected-products").fadeOut();
		}
	}

	function selected_category(selected) {
		if (selected == "selected-categories") {
			$("#selected-categories").fadeIn();
		} else {
			$("#selected-categories").fadeOut();
		}
	}

	function orders(selected) {
		if (selected == "orders") {
			$("#orders").fadeIn();
		} else {
			$("#orders").fadeOut();
		}
	}

	jQuery(document).ready(function ($) {
		preview_theme_css_loader();
		$("#pi_sn_theme").on('change', function(){
			preview_theme_css_loader();
		});

		function preview_theme_css_loader(){
			if(!$("#pi_sn_theme")) return;

			var theme_selected = $("#pi_sn_theme option:selected").val();
			removeThemeClasses($("#preview.pi-popup"));
			if(theme_selected !== 'default'){
				$("#preview.pi-popup").addClass('pi-popup-theme-'+theme_selected);
				$("#live_sales_preview_default-css").prop('disabled', true);
				$("#live_sales_preview_default-inline-css").prop('disabled', true);
				$("#live_sales_preview_theme-css").prop('disabled', false);
			}else{
				$("#preview.pi-popup").addClass('pi-popup-theme-default');
				$("#live_sales_preview_default-css").prop('disabled', false);
				$("#live_sales_preview_default-inline-css").prop('disabled', false);
				$("#live_sales_preview_theme-css").prop('disabled', true);
			}
		}

		function removeThemeClasses($el) {
			var classes = $el.attr('class') ? $el.attr('class').split(/\s+/) : [];
			var toRemove = classes.filter(function(cls) {
				return /^pi-popup-theme-theme\d+$/.test(cls);
			});
			$el.removeClass(toRemove.join(' '));
		}
	});


})(jQuery);
