<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}


// function to build the base edreports settings page.
function edreports_settings_config_page() {

  if (!current_user_can('manage_options')) {
    return;
  }

  // include media scripts.
  wp_enqueue_media();

  // include the config settings script.
  wp_enqueue_script('edreports_reviews_settings_config_script',
    EDREPORTS_REVIEWS_PLUGIN_URL . 'scripts/media-gallery.js',
    array('jquery'),
    time());

  wp_enqueue_style('edreports_reviews_settings_config_styles',
    EDREPORTS_REVIEWS_PLUGIN_URL . 'styles/settings/config.css',
    array(),
    time());

  // include the page template.
  include_once(EDREPORTS_REVIEWS_PLUGIN_DIR . 'includes/settings/config.php');
}


// function to build the edreports report settings page.
function edreports_settings_reports_page() {

  if (!current_user_can('manage_options')) {
    return;
  }

  include_once(EDREPORTS_REVIEWS_PLUGIN_DIR . 'includes/settings/reports.php');
}

// build plugin settings pages.
function edreports_reviews_plugin_settings_pages() {

  add_menu_page(
    'EdReports > Config',
    'EdReports',
    'manage_options',
    'edreports-reviews',
    'edreports_settings_config_page',
    get_edreports_reviews_icon_svg(),
    80
  );

  add_submenu_page(
    'edreports-reviews',
    'EdReports > Config',
    'Config',
    'manage_options',
    'edreports-reviews',
    'edreports_settings_config_page'
  );

  add_submenu_page(
    'edreports-reviews',
    'EdReports > Reports',
    'Reports',
    'manage_options',
    'edreports-reviews-reports',
    'edreports_settings_reports_page'
  );
}

// action to add settings pages to dashboard navigation.
add_action('admin_menu', 'edreports_reviews_plugin_settings_pages');





function register_edreports_reviews_plugin_settings() {
  //register edreports settings
  register_setting( 'edreports-reviews-plugin-config-settings-group', 'edreports_api_key' );
  register_setting( 'edreports-reviews-plugin-config-settings-group', 'edreports_approved_review_text' );
  register_setting( 'edreports-reviews-plugin-config-settings-group', 'edreports_approved_review_image' );
}

// add action to register settings options.
add_action( 'admin_init', 'register_edreports_reviews_plugin_settings' );

function get_edreports_reviews_icon_svg( $base64 = true ): string {
  $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96"><style type="text/css">.st0{fill:#FFCC68;}.st1{fill:#3498CB;}.st2{fill:#424E58;}</style><g><path class="st0" d="M43.5,0.3v43.2H0.3C2.5,20.6,20.6,2.5,43.5,0.3z"/><path class="st1" d="M95.7,43.5H52.5V0.3C75.4,2.5,93.5,20.6,95.7,43.5z"/><path class="st2" d="M0.3,52.5h43.2v43.2C20.6,93.5,2.5,75.4,0.3,52.5z"/></g></svg>';

  if ( $base64 ) {
    return 'data:image/svg+xml;base64,' . base64_encode( $svg );
  }

  return $svg;
}