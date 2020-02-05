<?php
/**
 * @package WPSCP
 */
/*
  Plugin Name: Gravure Produits
  Plugin URI: https://www.arsgroupe.com
  Description: Permettre au client de graver des textes et images sur les produits
  Version: 1.0
  Author: ARS GROUP
  Author URI: http://www.arsgroupe.cm
 */

define('WPSCP_PLUGIN_FILE',__FILE__);
define('WPSCP_DIR', plugin_dir_path(__FILE__));
 
define('WPSCP_URL', plugin_dir_url(__FILE__));

define('WPSCP_API_URL_SITE', get_site_url() . "/");


class SignCustomProduct {
	
    function __construct() {
		
		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'content_before_add_tocart_button') );
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 25, 2 );
		add_filter( 'woocommerce_get_item_data', array($this, 'get_item_data') , 25, 2 );
		add_action( 'woocommerce_add_order_item_meta', array($this, 'add_order_item_meta') , 10, 2 );
		
		add_action('wp_head', array($this, 'loadFonts') );

    }   

    /*
     * INit LINKS
     */
    
    /*
     * ADD ACTION
     */
	 
	 /* 
	 * Add Custom Content Before 'Add To Cart' Button On Product Page
	 */
	public function content_before_add_tocart_button() {

		$template = '
		
			<style>
				.engraving-image {
					position: relative;
				}
				
				.engraving-text-container {
					position: absolute;
					top: 0;
					width: 100%;
					height: 100%;
					z-index: 1;
					text-align: center;
				}
				
				#engraving-text {
					display: block;
					position: relative;
					top: 50%;
					transform: translateY(-50%);
					font-size: 1.2em;
				}
			
			</style>

			<div class="engraving-selector-container">
				<select id="engraving-selector" name="customer_engraving">
					<option value="no">Sans Gravure</option>
					<option value="yes">Avec Avec Gravure</option>
				</select>
			</div>
			<div id="engraving-container" class="engraving-container" style="display: none;">
				<div engraving-title>
					<h3>Gravure</h3>
				</div>
				<div class="engraving-image">
					<img src="//cdn.shopify.com/s/files/1/1438/8986/products/Antique-Silver-Blazer-Button_360x.png?v=1533723835" alt="produit"/>
					<div class="engraving-text-container">
						<span id="engraving-text"></span>
					</div>
				</div>
				<div class="engraving-options">
					<div class="engraving-option">
						<label for="customer_engraving_text">Texte de la gravure</label>
						<input name="customer_engraving_text" id="customer_engraving_text" />
					</div>
					<div class="engraving-option">
						<label for="customer_engraving_font">Police</label>
						<select id="customer_engraving_font" name="customer_engraving_font">';
						
						$fonts = $this->getFonts();
						
						foreach($fonts as $key => $value){
							$template .= '<option value="'. $key .'">' . $value . '</option>';
						}
						$template .= '
						</select>
					</div>	
				</div>
			</div>
			
			
			<script>
				
				// TODO clear form when there is no ingraving

				jQuery(document).ready(()=>{
					
					jQuery("#engraving-container").hide();

					jQuery("#engraving-selector").change(event=> {
						if(jQuery("#engraving-selector").val() == "yes"){
							jQuery("#engraving-container").show(200, "linear");
						}else{
							jQuery("#engraving-container").hide(200, "linear");
						}
					});
					
				});
				
				// TODO one why data binding
				jQuery("#customer_engraving_text").keyup((event)=>{
						jQuery("#engraving-text").text(jQuery("#customer_engraving_text").val()); 
				});
				
				jQuery("#customer_engraving_font").change(event=> {
					let fontElt = jQuery("#customer_engraving_font");
					console.log(fontElt.val());
					if(fontElt.val() == "dancing"){
						jQuery("#engraving-text").css("font-family", "Dancing Script")
					}else if (fontElt.val() == "lobster") {
						jQuery("#engraving-text").css("font-family", "Lobster Two")
					}else if (fontElt.val() == "jim") {
						jQuery("#engraving-text").css("font-family", "Jim Nightshade")
					}
				});
				
				
			</script>

		'; 
		echo $template;


	}

	/**
	 * Add data to cart item
	 */
	public function add_cart_item_data( $cart_item_meta, $product_id ) {

		if ( isset( $_POST ['customer_engraving_text'] ) && isset( $_POST ['customer_engraving_font'] ) ) {
			$custom_data  = array() ;
			$custom_data [ 'customer_engraving_text' ]    = isset( $_POST ['customer_engraving_text'] ) ?  sanitize_text_field ( $_POST ['customer_engraving_text'] ) : "" ;
			$custom_data [ 'customer_engraving_font' ] = isset( $_POST ['customer_engraving_font'] ) ? sanitize_text_field ( $_POST ['customer_engraving_font'] ): "" ;
			$cart_item_meta ['customer_engraving']     = $custom_data ;
		}
		
		return $cart_item_meta;
	}

	/**
	 * Display custom data on cart and checkout page.
	 */
	public function get_item_data ( $other_data, $cart_item ) {

		if ( isset( $cart_item [ 'customer_engraving' ] ) ) {
			$custom_data  = $cart_item [ 'customer_engraving' ];
				
			$other_data[] = array( 'name' => 'Gravure',
						'display'  => $custom_data['customer_engraving_text'] );
			$other_data[] = array( 'name' => 'Police',
						   'display'  => $custom_data['customer_engraving_font'] );
		}

		return $other_data;
	}


	/**
	 * Add order item meta.
	 */
	public function add_order_item_meta ( $item_id, $values ) {

		if ( isset( $values [ 'customer_engraving' ] ) ) {
			die("BOnjour");
			$custom_data  = $values [ 'customer_engraving' ];
			wc_add_order_item_meta( $item_id, 'name', $custom_data['customer_engraving_text'] );
			wc_add_order_item_meta( $item_id, 'name', $custom_data['customer_engraving_font'] );
		}
	}

    /**
     * POST ACTIONS
     */
    public static function Instance() {
        static $inst = null;
        if ($inst == null) {
            $inst = new SignCustomProduct();
        }
        return $inst;
    }
	
	// Loading Fonts
	public function loadFonts() {
		?>
			<link href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Lobster+Two&display=swap" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Jim+Nightshade&display=swap" rel="stylesheet">
		<?php

	}
	
	// Static fonts key and value
	public function getFonts() {
		$fonts = array
		(
			'dancing' =>'Dancing Script', 
			'lobster' => 'Lobster Two',
			'jim' => 'Jim Nightshade'
		);
		return $fonts;
	}

}

SignCustomProduct::Instance();
