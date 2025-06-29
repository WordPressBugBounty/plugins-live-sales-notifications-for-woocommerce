<?php
class Pisol_Sales_Notification_Activator {

	public static function activate() {
		add_option('pi_sales_notification_do_activation_redirect', true);

		$cat_id = self::get_category_with_most_products();
		$present_cat_id = get_option('pi_sn_selected_category', []);
		if (empty($present_cat_id) && !empty($cat_id) && is_array($cat_id)) {
			update_option('pi_sn_selected_category', $cat_id);
		}
	}

	static function get_category_with_most_products() {
		$terms = get_terms([
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
		]);

		if (count($terms) <= 3) {
			return wp_list_pluck($terms, 'term_id');
		}

		$category_counts = [];

		foreach ($terms as $term) {
			// Get only directly assigned products
			$query = new WP_Query([
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => [[
					'taxonomy'         => 'product_cat',
					'terms'            => $term->term_id,
					'include_children' => false,
					'field'            => 'term_id',
				]]
			]);

			$category_counts[$term->term_id] = count($query->posts);
			wp_reset_postdata();
		}

		// Sort categories by count in descending order
		arsort($category_counts);

		// Return top 3 category IDs
		return array_slice(array_keys($category_counts), 0, 3);
	}

}
