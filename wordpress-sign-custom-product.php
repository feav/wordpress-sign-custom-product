<?php
/**
 * @package WPSCP
 */
/*
  Plugin Name: Gravure Produits
  Plugin URI: https://www.vendmy.com
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
        
    }   

    /*
     * INit LINKS
     */
    
    /*
     * ADD ACTION
     */

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

}

SignCustomProduct::Instance();