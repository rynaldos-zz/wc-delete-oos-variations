<?php

/*
 Plugin Name: WC Delete out of stock variations
 Plugin URI: https://profiles.wordpress.org/rynald0s
 Description: This plugin adds a status tool that lets you delete all out of stock variations from your shop
 Author: Rynaldo Stoltz
 Author URI: https://github.com/rynaldos
 Version: 1.0
 License: GPLv3 or later License
 URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
if (!class_exists('WooCommerce_delete_all_outofstock_variations')) {
  class WooCommerce_delete_all_outofstock_variations {
    public static $instance;
    public static function init() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new WooCommerce_delete_all_outofstock_variations();
      }
      return self::$instance;
    }
    private function __construct() {
      add_filter( 'admin_init', array( $this, 'handle_woocommerce_tool' ) );
      add_filter( 'woocommerce_debug_tools', array( $this, 'add_woocommerce_tool' ) );
    }
    /**
     * Runs an SQL query in the database
     */
    public function wc_delete_outofstock_variations() {
      global $wpdb;
      $ran = true;
      $sql = "DELETE p FROM wp_posts p join wp_postmeta pm on p.ID = pm.post_id WHERE p.post_type = 'product_variation' and pm.meta_key='_stock_status' and pm.meta_value='outofstock'";
      $rows = $wpdb->query( $sql );
      if( false !== $rows ) {
        $this->deleted = $rows;
        //add_action( 'admin_notices', array( $this, 'admin_notice_success' ) );
      }
    }
    /**
     * Adds a tool to the WooCommerce tools
     */
    public function add_woocommerce_tool( $tools ) {
      $tools['wc_delete_outofstock_variations'] = array(
        'name'    => __( 'Delete out of stock variations', 'woocommerce' ),
        'button'  => __( 'Delete out of stock variations', 'woocommerce' ),
        'desc'    => __( 'This option will delete all out of stock variations from your store — please use with caution!', 'woocommerce' ),
        'callback' => array( $this, 'debug_notice_success' ),
      );
      return $tools;
    }
    /**
     * Runs the tool
     *
     * The tool button, when clicked, will send a GET request to the tab page
     * along with &action=wc_delete_outofstock_variations
     */
    public function handle_woocommerce_tool() {
      if( empty( $_REQUEST['page'] ) || empty( $_REQUEST['tab'] ) ) {
          return;
      }
      
      // check that we are on woocommerce system status admin page
      if( 'wc-status' != $_REQUEST['page'] ) {
        return;
      }
      // check that we are on the tools tab
      if( 'tools' != $_REQUEST['tab'] ) {
        return;
      }
      // check permissions
      if( ! is_user_logged_in() || ! current_user_can('manage_woocommerce') ) {
        return;
      }
      if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
        if( $_GET['action'] === 'wc_delete_outofstock_variations' ) {
          $this->wc_delete_outofstock_variations();
        }
      }
    }
    /**
     * Admin notification after running the tool
     */
    public function debug_notice_success( ) {
      $deleted = $this->deleted;
    ?>
<div class="notice notice-success is-dismissible">
  <p><?php echo wp_sprintf( __('%d out of stock variations were deleted.', 'woocommerce'), $deleted ); ?></p>
</div>
    <?php
    }
  }
}
// init plugin
$woocommerce_delete_orders = WooCommerce_delete_all_outofstock_variations::init();