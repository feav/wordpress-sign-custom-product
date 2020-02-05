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
	private $meta_key_img;

    function __construct() {
		$this->meta_key_img = 'image_gravure';
		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'content_before_add_tocart_button') );
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 25, 2 );
		add_filter( 'woocommerce_get_item_data', array($this, 'get_item_data') , 25, 2 );
		add_action( 'woocommerce_add_order_item_meta', array($this, 'add_order_item_meta') , 10, 2 );
		
		add_action('wp_head', array($this, 'loadFonts') );
		add_action('admin_menu', array( &$this , 'register_metabox'));
       	add_action('save_post',  array(&$this, 'misha_save'), 1, 2 );
		add_action('manage_product_posts_custom_column', array(&$this, 'booked_add_custom_product_columns'), 15, 3);
		add_filter('manage_product_posts_columns', array(&$this, 'booked_add_product_columns'), 15, 1);
    }   
    function booked_add_custom_product_columns($column_name, $postid){
		if ( $column_name == $this->meta_key_img ) {
		 	$name = get_post_meta($postid, $this->meta_key_img,  false )[0];
			if($name){
				echo "<img style='width: 25px' src='https://img.icons8.com/plasticine/50/000000/double-tick.png'>";
			}else{

				echo "<img style='width: 25px' src='https://img.icons8.com/color/48/000000/close-window.png'>";
			}		

		}

    }
    function booked_add_product_columns($defaults ){

			$defaults[$this->meta_key_img] = esc_html__('Gravable', 'booked');
			return $defaults;
    }
    /*
     * INit LINKS
     */
    
    /*
     * ADD META
     */
	 static function register_metabox(){
        	add_meta_box('Gravure', 'Gravure Produit', array( &$this, 'product_gravure_img'), 'product', 'side', 'high');
		}
	/*
	* CALL BACK META
	*/
	public function product_gravure_img(){
		global $post;
		$template = '';
		 $name = get_post_meta($post->ID, $this->meta_key_img,  false )[0];
		?>
		
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
				#engraving-container img{
					width: 100%;
				}
				.engraving-options{
					text-align: center;
				}
				span#engraving-text {
				    color: white;
				    text-shadow: 1px 1px 1px black;
				}
				.engraving-option {
				    display: flex;
				    justify-content: space-between;
				}
			</style>
			<div id="engraving-container" class="engraving-container">
				<div class="engraving-image">
					<span class="img">
						<?php if($name){ ?>
						<img src="<?php echo $name; ?>" alt="produit"/>
						<?php } ?>
					</span>
					<div class="engraving-text-container">
						<span id="engraving-text">Exemple</span>
					</div>
					<input type="hidden" name="<?php echo $this->meta_key_img ?>" id="<?php echo $this->meta_key_img ?>">
				</div>
				<div class="engraving-options">
					<div class="engraving-option">
						<button class="misha_upload_image_button button button-primary button-large">AJOUTER</button>
						<button class="misha_remove_image_button button-link delete-attachment" style="color: red;text-transform: lowercase;">SUPPRIMER</button>
					</div>
				</div>
			</div>
			
			
			<script>
				
				jQuery(function($){
				    /*
				     * Select/Upload image(s) event
				     */
				    $('body').on('click', '.misha_upload_image_button', function(e){
				        e.preventDefault();

				            var button = $(this),
				                custom_uploader = wp.media({
				            title: 'Insert image',
				            library : {
				                // uncomment the next line if you want to attach image to the current post
				                // uploadedTo : wp.media.view.settings.post.id, 
				                type : 'image'
				            },
				            button: {
				                text: 'Use this image' // button label text
				            },
				            multiple: false // for multiple image selection set to true
				        }).on('select', function() { // it also has "open" and "close" events 
				            var attachment = custom_uploader.state().get('selection').first().toJSON();
				            $(".engraving-image > span.img").html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:95%;display:block;" />').next().val(attachment.id).next().show();
				            $("#<?php echo $this->meta_key_img ?>").val(attachment.url);
				        })
				        .open();
				    });

				    /*
				     * Remove image event
				     */
				    $('body').on('click', '.misha_remove_image_button', function(){
				        $(this).hide().prev().val('').prev().addClass('button').html('Upload image');
				        $("#<?php echo $this->meta_key_img ?>").val('');
				        return false;
				    });

				});
				
			</script>
		<?php
		$t=''; 
		echo $template;

	}
	function misha_save( $post_id, $post) {
	    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	        return $post_id;
	    
	    $meta_key = $this->meta_key_img;
		if($post->post_type=="product"){
	            if ( ! current_user_can( 'edit_post', $post_id ) ) 
	                    return $post_id;
	            $value = esc_textarea( $_POST[$meta_key] );
	            update_post_meta( $post_id, $meta_key, $value );
	        }
	    return $post_id;
	}
	 /* 
	 * Add Custom Content Before 'Add To Cart' Button On Product Page
	 */
	public function content_before_add_tocart_button() {

		global $post;
		$url = get_post_meta($post->ID, $this->meta_key_img,  false )[0];
		if(!$url)
			return;
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
					<img src="'.$url.'" alt="produit"/>
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
