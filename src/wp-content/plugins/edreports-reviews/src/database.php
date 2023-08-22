<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

//--------------------------------------------------
// Constants
//--------------------------------------------------

global $table_prefix;
global $edreports_table_prefix;

// create the edreports table prefix.
$edreports_table_prefix = $table_prefix . 'edreports_';

// create constants for table names.
const EDREPORTS_SERIES_OVERVIEW_TABLE = 'series_overview';
const EDREPORTS_SERIES_CRITERIA_TABLE = 'series_criteria';
const EDREPORTS_SUBJECT_TABLE = 'subjects';
const EDREPORTS_GRADES_TABLE = 'grades';
const EDREPORTS_REVIEW_TOOLS_TABLE = 'review_tools';
const EDREPORTS_REVIEW_TOOL_CRITERIA_TABLE = 'review_tool_criteria';

//--------------------------------------------------
// DATABASE TABLES
//--------------------------------------------------

// method for initializing tables to hold report data.
function create_edreports_tables() {

  // WP Globals
  global $edreports_table_prefix, $wpdb;

  // SUBJECTS TABLE -------------------------------

  // build the subject table name.
  $subjectTable = $edreports_table_prefix . EDREPORTS_SUBJECT_TABLE;

  // check if the subject table exists.
  if ($wpdb->get_var("show tables like '$subjectTable'") != $subjectTable) {

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$subjectTable` (
            `subject_key` varchar(255) NOT NULL,
            `label` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            PRIMARY KEY (`subject_key`)
    ) DEFAULT COLLATE utf8_bin;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }

  // GRADES TABLE ---------------------------------

  // build the grade table name.
  $gradeTable = $edreports_table_prefix . EDREPORTS_GRADES_TABLE;

  // check if the grade table exists.
  if ($wpdb->get_var("show tables like '$gradeTable'") != $gradeTable) {

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$gradeTable` (
            `grade_key` varchar(255) NOT NULL,
            `label` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `shortcode` varchar(255) NOT NULL,
            `order` int UNSIGNED, 
            PRIMARY KEY (`grade_key`)
    ) DEFAULT COLLATE utf8_bin;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }

  // REVIEW TOOLS TABLE ---------------------------

  // build the review tools table name.
  $reviewToolsTable = $edreports_table_prefix . EDREPORTS_REVIEW_TOOLS_TABLE;

  // check if the review tools table exists.
  if ($wpdb->get_var("show tables like '$reviewToolsTable'") != $reviewToolsTable) {

    // @TODO add ordering.

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$reviewToolsTable` (
            `review_tool_key` varchar(255) NOT NULL,
            `subject_key` varchar(255) NOT NULL,
            `label` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `order` int UNSIGNED,
            PRIMARY KEY (`review_tool_key`)
    ) DEFAULT COLLATE utf8_bin;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }

  // REVIEW TOOL CRITERIA -------------------------

  // build the review tool criteria table name.
  $reviewToolCriteriaTable = $edreports_table_prefix . EDREPORTS_REVIEW_TOOL_CRITERIA_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$reviewToolCriteriaTable'") != $reviewToolCriteriaTable) {

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$reviewToolCriteriaTable` (
            `review_tool_key` varchar(255) NOT NULL,
            `criteria_key` varchar(255) NOT NULL,
            PRIMARY KEY (`review_tool_key`, `criteria_key`)
    ) DEFAULT COLLATE utf8_bin;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }

  // SERIES OVERVIEW TABLE ------------------------

  // build the series overview table name.
  $seriesOverviewTable = $edreports_table_prefix . EDREPORTS_SERIES_OVERVIEW_TABLE;

  // check if series overview table exists
  if ($wpdb->get_var("show tables like '$seriesOverviewTable'") != $seriesOverviewTable) {

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$seriesOverviewTable` (
            `series_key` varchar(255) NOT NULL,
            `data` json NOT NULL,
            PRIMARY KEY (`series_key`)
    ) DEFAULT COLLATE utf8_bin;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }

  // SERIES CRITERIA TABLE ------------------------

  // build the series criteria table name.
  $seriesCriteriaTable = $edreports_table_prefix . EDREPORTS_SERIES_CRITERIA_TABLE;

  // check if series criteria table exists..
  if ($wpdb->get_var("show tables like '$seriesCriteriaTable'") != $seriesCriteriaTable) {

    // build the sql query to create the table.
    $sql = "CREATE TABLE `$seriesCriteriaTable` (
            `series_key` varchar(255) NOT NULL,
            `criteria_key` varchar(255) NOT NULL,
            PRIMARY KEY (`series_key`, `criteria_key`)
    ) DEFAULT COLLATE utf8_bin";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    dbDelta($sql);
  }
}


// method to remove plugin tables.
function remove_edreports_tables() {

  // WP Globals
  global $edreports_table_prefix, $wpdb;

  // build the subject table name.
  $subjectTable = $edreports_table_prefix . EDREPORTS_SUBJECT_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$subjectTable'") == $subjectTable) {

    // build the query.
    $sql = "DROP TABLE `$subjectTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }

  // build the grades table name.
  $gradesTable = $edreports_table_prefix . EDREPORTS_GRADES_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$gradesTable'") == $gradesTable) {

    // build the query.
    $sql = "DROP TABLE `$gradesTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }

  // build the review tools table name.
  $reviewToolsTable = $edreports_table_prefix . EDREPORTS_REVIEW_TOOLS_TABLE;

  // check if the review tools table exists.
  if ($wpdb->get_var("show tables like '$reviewToolsTable'") == $reviewToolsTable) {

    // build the query.
    $sql = "DROP TABLE `$reviewToolsTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }

  // build the review tools criteria table name.
  $reviewToolCriteriaTable = $edreports_table_prefix . EDREPORTS_REVIEW_TOOL_CRITERIA_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$reviewToolCriteriaTable'") == $reviewToolCriteriaTable) {

    // build the query.
    $sql = "DROP TABLE `$reviewToolCriteriaTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }

  // build the series overview table name.
  $seriesOverviewTable = $edreports_table_prefix . EDREPORTS_SERIES_OVERVIEW_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$seriesOverviewTable'") == $seriesOverviewTable) {

    // build the query.
    $sql = "DROP TABLE `$seriesOverviewTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }

  // build the series criteria table name.
  $seriesCriteriaTable = $edreports_table_prefix . EDREPORTS_SERIES_CRITERIA_TABLE;

  // check if the content area table exists.
  if ($wpdb->get_var("show tables like '$seriesCriteriaTable'") == $seriesCriteriaTable) {

    // build the query.
    $sql = "DROP TABLE `$seriesCriteriaTable`;";

    // include the upgrade script.
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // run the query.
    $wpdb->query($sql);
  }
}