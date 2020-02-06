<?php

define('WPSCP_SERVICE_CATEGORY_NAME', 'Service Gravure');

add_action('init', 'create_wc_product_category');

function create_wc_product_category() {
	
	$term_id = null;
	
	// Create Category
	if(!term_exists(WPSCP_SERVICE_CATEGORY_NAME, 'product_cat' )){
		
		$term = wp_insert_term(
		  WPSCP_SERVICE_CATEGORY_NAME, // the term 
		  'product_cat', // the taxonomy
		  array(
			'description'=> 'Categorie pour les services de gravure',
			'slug' => 'service-gravure'
		  )
		);
		
		if ( is_wp_error( $term ) ) {
			$term_id = $term->error_data['term_exists'] ?? null;
		} else {
			$term_id = $term['term_id'];
		}
		
	}else{
		$term = get_term_by('name', WPSCP_SERVICE_CATEGORY_NAME, 'product_cat');
		$term_id = $term->term_id;
	}
	
	$args = array(
        'post_type'      => 'product',
		'tax_query' => array(
			array (
				'taxonomy' => 'product_cat',
				'field' => 'name',
				'terms' => WPSCP_SERVICE_CATEGORY_NAME,
			)
		)
    );
	
	$products = get_posts($args);
	
	if(count($products) == 0 && $term_id != null){
		print_r($products);
	
		// add product		
		$post_id = wp_insert_post(
			array(
				'post_title' => 'Service Gravure',
				'post_type' => 'product',
				'post_status' => 'publish', 
				'post_content' => 'service de gravure',
				'post_excerpt' => 'Service de gravure'
			)
		);
		
		// update product
		wp_set_object_terms( $post_id, $term_id, 'product_cat' );
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock');
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'yes' );
		update_post_meta( $post_id, '_regular_price', '99' );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_sku', '' );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', '99' );
		update_post_meta( $post_id, '_sold_individually', '' );
		// wc_update_product_stock($post_id, $single['qty'], 'set');
		
	}
	
	
}


