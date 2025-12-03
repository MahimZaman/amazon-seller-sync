<?php

/**
 * Plugin Name: Amazon Seller Order Sync
 * Description: This plugin enables you to sync your amazon order status directly from your wordpress database without having to update it to amazon.
 * Version: 1.0.0
 * Author: Noor Kamal
 * Author URI: https://www.example.com
 * Text Domain: asos
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) return;

define('ASOS_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('ASOS_URL', trailingslashit(plugin_dir_url(__FILE__)));

add_action('plugins_loaded', 'asos_load');

function asos_load()
{
    require_once ASOS_PATH . 'includes/classes/class-asos-init.php';
    require_once ASOS_PATH . 'includes/classes/class-amazon-tracking-api.php';
    $load = new ASOS_INIT();
    $api = new ASOS_AmazonTrackingAPI();
}
