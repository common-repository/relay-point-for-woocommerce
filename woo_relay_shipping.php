<?php
/*
 * Plugin Name: Relay Point for WooCommerce
 * Description: Manually create relay points on WooCommerce and display them in an improved selection format.
 * Version: 2.0.2
 * Tags: WooCommerce, Shipping
 * Author: Lionel Bataille
 * Author URI: mailto:lionel.bataille@hotmail.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: relay-point-for-woocommerce
 * Domain Path: /languages
 */

if (!defined('ABSPATH') && !defined('WPINC')) {
	exit; // Exit if accessed directly.
}

class Woo_Relay_Shipping
{
	public function __construct()
	{
		// Check if the class doesn't exist yet
		if (!class_exists('WC_Point_relais'))
			include_once plugin_dir_path(__FILE__) . 'modules/class_relay_shipping.php';

		// Add template override capabilities for the plugin
		add_filter('woocommerce_locate_template', 'plugin_override_woocommerce_locate_template', 10, 3);

		function plugin_override_woocommerce_locate_template($template, $template_name, $template_path)
		{
			global $woocommerce;
			$_template = $template;
			if (!$template_path)
				$template_path = $woocommerce->template_url;
			$plugin_path = untrailingslashit(plugin_dir_path(__FILE__)) . '/woocommerce/';

			$template = locate_template(
				array(
					$template_path . $template_name,
					$template_name
				)
			);

			if (!$template && file_exists($plugin_path . $template_name))
				$template = $plugin_path . $template_name;

			if (!$template)
				$template = $_template;

			return $template;
		}

		// Add translation for the plugin
		add_action('plugins_loaded', [$this, 'load_plugin_text_domain']);
	}

	public function load_plugin_text_domain()
	{
		load_plugin_textdomain('relay-point-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}
}

// Check WooCommerce before running the plugin
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	new Woo_Relay_Shipping();
}
