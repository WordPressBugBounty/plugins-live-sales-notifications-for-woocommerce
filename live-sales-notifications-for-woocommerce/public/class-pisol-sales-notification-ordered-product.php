<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class pi_sn_ordered_products {
	private $products = array();
	private $popups = array();
	private $orders = array();
	private $pi_sn_time_value = '1'; /* order in last 1 day */
	private $pi_sn_time_unit = 'day'; /* hours, minutes, days */
	private $order_status = array('wc-completed');
	private $sales_message_format;
	private $min_popup = 10;

	public $pi_sn_remove_out_of_stock;
	public $order_time;
	public $max_popup;
	
	function __construct(){
		static $cache = null;

		if ($cache === null) {
			$cache = [
				'pi_sn_max_product_show' => get_option("pi_sn_max_product_show", 10),
				'pi_sn_order_status' => get_option("pi_sn_order_status", ['wc-completed']),
				'pi_sn_time_unit' => get_option("pi_sn_time_unit", "day"),
				'pi_sn_time_value' => (int) get_option("pi_sn_time_value", 1),
				'pi_sn_remove_out_of_stock' => get_option('pi_sn_remove_out_of_stock', 1),
			];
		}
		
		$this->max_popup = $cache['pi_sn_max_product_show'];

		$this->order_status = $cache['pi_sn_order_status'];
		
		$this->pi_sn_time_unit = $cache['pi_sn_time_unit']; // day, week, hour
		$this->pi_sn_time_value = $cache['pi_sn_time_value'];

		$this->pi_sn_remove_out_of_stock = $cache['pi_sn_remove_out_of_stock'];

		$this->order_time = "-".$this->pi_sn_time_value." ".$this->pi_sn_time_unit;
		
		$this->getOrders();
		$this->getProductsObj();
		$this->createMessage();
	}

	function getOrders(){
		$args = array(
			'status'=> $this->order_status,
			'orderby' => 'date',
			'order' => 'DESC',
			'date_created' => '>' . ( strtotime($this->order_time) ),
			'field' => 'ids',
		);
		$this->orders = wc_get_orders($args);
		return shuffle($this->orders);
	}

	function getProductsObj(){
		
		foreach($this->orders as $order_id){
			$order = wc_get_order($order_id);

			$exclude_order = $order->get_meta( 'pisol_exclude_order', true );
			if($exclude_order == 'on' || $exclude_order == 1){
				continue;
			}

			$items = $order->get_items();
			
			if(count($this->products) >= $this->max_popup ){
                break;
			}
			
			foreach($items as $item){

				$product = $item->get_product();

				if(!is_object($product)) continue;
				
				$add_to_list = $this->pi_sn_remove_out_of_stock == 1  ? $product->is_in_stock() : true;
              	$add_to_list = true; // We are making it true, as FREE version will show the out of stock product as well
				
				if(count($this->products) >= $this->max_popup ){
					break;
				}

				if($add_to_list){
					$this->products[]= pisol_sn_common::formatProductObj($product, $order);
				}
				
			}
			
		}

		return $this->products;
	}

	function createMessage(){
		foreach($this->products as $product){
			
			if(empty($product) || !is_array($product)) continue;

			$this->popups[] = array("desc"=> pisol_sn_common::searchReplace($product), "image"=> $product['image'], "link"=> $product['link']);
		}
		return $this->popups;
	}

	function getPopups(){
		return $this->popups;
	}

	
}