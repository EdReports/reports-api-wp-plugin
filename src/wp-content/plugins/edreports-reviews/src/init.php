<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// database table activation.
require_once plugin_dir_path( __FILE__ ) . 'database.php';

// settings pages.
require_once plugin_dir_path( __FILE__ ) . 'settings.php';

// automated sync via cron.
require_once plugin_dir_path( __FILE__ ) . 'sync.php';


// @TODO testing.
// do_action('edreports_reports_sync', null, null);




