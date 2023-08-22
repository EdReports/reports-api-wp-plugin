<?php
/**
 * Plugin Name: EdReports Review Feed
 * Description: Plugin to import and display EdReports' reviews.
 * Author: EdReports
 * Author URI: https://edreports.org/
 * Author Email: admin@edreports.org
 * Version: 1.0.0
 * License: GPL-3.0
 * Requires PHP: 7.4
 *
 * @package edreports
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}


// define the directory location of the plugin.
define( 'EDREPORTS_REVIEWS_PLUGIN_DIR', plugin_dir_path(__FILE__));
// define the plugin url.
define('EDREPORTS_REVIEWS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Initialize plugin declarations.
 */
require_once EDREPORTS_REVIEWS_PLUGIN_DIR . 'src/init.php';


/**
 * Register plugin hooks.
 */

// create tables on plugin activation.
register_activation_hook(__FILE__, 'create_edreports_tables');

// add hook to unschedule the review sync cron on plugin deactivation.
register_deactivation_hook(__FILE__, 'deactivate_edreports_data_sync');

// uninstall tables on plugin uninstall.
register_uninstall_hook(__FILE__, 'remove_edreports_tables');

/**
 * Plugin settings page.
 */

// Add Settings Link to Plugin Page
function edreports_reviews_plugin_add_settings_link($links) {
  $links[] = '<a href="admin.php?page=edreports-reviews">' . __('Settings') . '</a>';
  return $links;
}

$filter_name = "plugin_action_links_" . plugin_basename(__FILE__);
add_filter($filter_name, 'edreports_reviews_plugin_add_settings_link');