<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// require database PHP for table names.
require_once plugin_dir_path(__FILE__) . 'database.php';

global $edreports_api_key;

$edreports_api_key = get_option('edreports_api_key');

// Don't attempt to sync data unless the API key option has been set.
if (!$edreports_api_key) {
  error_log("Failed to sync data. EdReports API key not set.");
}

// create constant path to Reports API base.
const EDREPORTS_API_BASE_URL = "https://edreports.org/_ah/api/reports/v3";


//--------------------------------------------------
// CRON SETUP: automated task scheduling
//--------------------------------------------------

/** create a custom cron schedule interval.
function edreports_sync_interval($schedules) {
  $schedules['five_seconds'] = array(
    'interval' => 5,
    'display' => esc_html__('Every Five Seconds'),);
  return $schedules;
}

add_filter('cron_schedules', 'edreports_sync_interval'); */

// method scheduling review sync cron.
function schedule_edreports_data_sync() {

  if (!wp_next_scheduled('edreports_data_sync')) {
    // wp_schedule_event(time(), 'five_seconds', 'edreports_data_sync');
    wp_schedule_event(time(), 'daily', 'edreports_data_sync');
  }
}

// create an action hook to run the review sync.
add_action('wp', 'schedule_edreports_data_sync');

// method to unschedule the review sync cron.
function deactivate_edreports_data_sync() {

  error_log("edreports::deactivate");

  // unschedule sync calls.
  $timestamp = wp_next_scheduled('edreports_data_sync');
  wp_unschedule_event($timestamp, 'edreports_data_sync');

  $timestamp = wp_next_scheduled('edreports_subjects_sync');
  wp_unschedule_event($timestamp, 'edreports_subjects_sync');

  $timestamp = wp_next_scheduled('edreports_grades_sync');
  wp_unschedule_event($timestamp, 'edreports_grades_sync');

  $timestamp = wp_next_scheduled('edreports_review_tools_sync');
  wp_unschedule_event($timestamp, 'edreports_review_tools_sync');

  $timestamp = wp_next_scheduled('edreports_reports_sync');
  wp_unschedule_event($timestamp, 'edreports_reports_sync');

  delete_option('edreports_last_subjects_sync');
  delete_option('edreports_last_grades_sync');
  delete_option('edreports_last_review_tools_sync');
  delete_option('edreports_last_reports_sync');
}

//--------------------------------------------------
// CRON TASK: main hook to trigger sub-tasks
//--------------------------------------------------

// method for syncing reviews from EdReports' API.
function sync_edreports_data() {

  // check if subjects are scheduled to sync.
  if (!wp_next_scheduled('edreports_subjects_sync')) {
    // schedule subject sync.
    wp_schedule_single_event(time(), 'edreports_subjects_sync', array(null, null));
  }

  // check if grades are scheduled to sync.
  if (!wp_next_scheduled('edreports_grades_sync')) {
    // schedule grade sync.
    wp_schedule_single_event(time(), 'edreports_grades_sync', array(null, null));
  }

  // check if review tools are scheduled to sync.
  if (!wp_next_scheduled('edreports_review_tools_sync')) {
    // schedule review tool sync.
    wp_schedule_single_event(time(), 'edreports_review_tools_sync', array(null, null));
  }

  // check if reports are scheduled to sync.
  if (!wp_next_scheduled('edreports_reports_sync')) {
    // schedule report sync.
    wp_schedule_single_event(time(), 'edreports_reports_sync', array(null, null));
  }
}

// register the data sync function as an action.
add_action('edreports_data_sync', 'sync_edreports_data');

//--------------------------------------------------
// CRON SUB-TASKS: sync hooks for each data type
//--------------------------------------------------

// method to sync subject data.
function sync_edreports_subjects($cursor = null, $modified = null) {

  // get the date of the last sync.
  $modified = get_option('edreports_last_subjects_sync');

  // update the last sync date.
  update_option('edreports_last_subjects_sync', date('Y-m-d'));

  // load the available subjects from EdReports' API.
  $response = load_edreports_api_items("subjects", $modified);

  // ensure the request was successful.
  if ($response == null) {
    error_log("edreports::subjects: plugin failed sync data: ensure the provided API key is valid.");
  } else if ($response->error) {
    error_log("edreports::subjects: plug failed to sync data: " . $response->code . " Error: " . $response->message);
  }

  // check if there are more items to be loaded.
  if (isset($response->nextPageToken) && !empty($response->nextPageToken)) {
    // schedule subject sync with next page token.
    wp_schedule_single_event(time(), 'edreports_subjects_sync', array($modified, $response->nextPageToken));
  }

  // extract the subject data.
  $items = $response->items;

  // ensue the response items is an array.
  if (is_array($items) && !empty($items)) {

    global $edreports_table_prefix, $wpdb;

    // build the subject table name.
    $subjects_table = $edreports_table_prefix . EDREPORTS_SUBJECT_TABLE;

    // initialize the sql query.
    $sql = "INSERT INTO `$subjects_table` (subject_key, label, slug) VALUES";

    // initialize values array to handle comma separation.
    $values = array();

    // iterate the response items and build the values.
    foreach ($items as $subject) {
      // add the value entry.
      $values[] = "('$subject->key', '$subject->abbr', '$subject->slug')";
    }

    // apply the values to the query.
    $sql .= " " . join(", ", $values);

    // add update methodology for overwriting data.
    $sql .= " ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `slug` = VALUES(`slug`);";

    // run the query.
    $wpdb->query($sql);

  }
}

// register the subject sync function as an action.
add_action('edreports_subjects_sync', 'sync_edreports_subjects', 10, 2);

// method to sync grade data.
function sync_edreports_grades($cursor = null, $modified = null) {

  // get the date of the last sync.
  $modified = get_option('edreports_last_grades_sync');

  // update the last sync date.
  update_option('edreports_last_grades_sync', date('Y-m-d'));

  // load the available grades from EdReports' API.
  $response = load_edreports_api_items("grades", $modified);

  // ensure the request was successful.
  if ($response == null) {
    error_log("edreports::grades: plugin failed sync data: ensure the provided API key is valid.");
  } else if ($response->error) {
    error_log("edreports::grades: plugin failed to sync data: " . $response->code . " Error: " . $response->message);
  }

  // check if there are more items to be loaded.
  if (isset($response->nextPageToken) && !empty($response->nextPageToken)) {
    // schedule grade sync with next page token.
    wp_schedule_single_event(time(), 'edreports_grades_sync', array($modified, $response->nextPageToken));
  }

  // extract the grade data.
  $items = $response->items;

  // ensue the response items is an array.
  if (is_array($items) && !empty($items)) {

    global $edreports_table_prefix, $wpdb;

    // build the grade table name.
    $grades_table = $edreports_table_prefix . EDREPORTS_GRADES_TABLE;

    // initialize the sql query.
    $sql = "INSERT INTO `$grades_table` (`grade_key`, `label`, `slug`, `shortcode`, `order`) VALUES";

    // initialize values array to handle comma separation.
    $values = array();

    // iterate the response items and build the values.
    foreach ($items as $grade) {
      // add the value entry.
      $values[] = "('$grade->key', '$grade->name', '$grade->slug', '$grade->shortcode', '$grade->order')";
    }

    // apply the values to the query.
    $sql .= " " . join(", ", $values);

    // add update methodology for overwriting data.
    $sql .= " ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `slug` = VALUES(`slug`), `shortcode` = VALUES(`shortcode`), `order` = VALUES(`order`);";

    // run the query.
    $wpdb->query($sql);

  }
}

// register the subject sync function as an action.
add_action('edreports_grades_sync', 'sync_edreports_grades', 10, 2);

// method to sync review tool data.
function sync_edreports_review_tools($cursor = null, $modified = null) {

  // get the date of the last sync.
  $modified = get_option('edreports_last_review_tools_sync');

  // update the last sync date.
  update_option('edreports_last_review_tools_sync', date('Y-m-d'));

  // load the available review tools from EdReports' API.
  $response = load_edreports_api_items("tools", $modified);

  // ensure the request was successful.
  if ($response == null) {
    error_log("edreports::review-tools: plugin failed sync data: ensure the provided API key is valid.");
  } else if ($response->error) {
    error_log("edreports::review-tools: plug failed to sync data: " . $response->code . " Error: " . $response->message);
  }

  // check if there are more items to be loaded.
  if (isset($response->nextPageToken) && !empty($response->nextPageToken)) {
    // schedule grade sync with next page token.
    wp_schedule_single_event(time(), 'edreports_review_tools_sync', array($modified, $response->nextPageToken));
  }

  // extract the review tool data.
  $items = $response->items;

  // ensue the response items is an array.
  if (is_array($items) && !empty($items)) {

    // initialize values array to help build SQL.
    $review_tool_values = array();
    // initialize a values array to store review tool criteria entries.
    $criteria_values = array();

    // iterate the response items and build the values.
    foreach ($items as $review_tool) {

      // extract the review tool key.
      $review_tool_key = $review_tool->key;

      // add the value entry.
      $review_tool_values[] = "('$review_tool_key', '$review_tool->subjectKey', '$review_tool->label', '$review_tool->slug', $review_tool->order)";

      // extract the criteria keys.
      $criteria_keys = $review_tool->criteriaKeys;

      // ensure the criteria keys are not empty.
      if (isset($criteria_keys) && !empty($criteria_keys)) {

        // iterate the criteria keys.
        foreach ($criteria_keys as $criteria_key) {
          // add an entry for the review tool / criteria pair.
          $criteria_values[] = "('$review_tool_key', '$criteria_key')";
        }
      }
    }

    global $edreports_table_prefix, $wpdb;

    // build the review tools table name.
    $review_tools_table = $edreports_table_prefix . EDREPORTS_REVIEW_TOOLS_TABLE;
    // initialize the review tool query.
    $review_tool_query = "INSERT INTO `$review_tools_table` (`review_tool_key`, `subject_key`, `label`, `slug`, `order`) VALUES";
    // apply the review tool values to the query.
    $review_tool_query .= " " . join(", ", $review_tool_values);
    // add update methodology for overwriting data.
    $review_tool_query .= " ON DUPLICATE KEY UPDATE `subject_key` = VALUES(`subject_key`), `label` = VALUES(`label`), `slug` = VALUES(`slug`), `order` = VALUES(`order`);";

    // run the query.
    $wpdb->query($review_tool_query);

    // build the review tool criteria table name.
    $review_tool_criteria_table = $edreports_table_prefix . EDREPORTS_REVIEW_TOOL_CRITERIA_TABLE;
    // start building the criteria key query.
    $review_tool_criteria_query = "INSERT IGNORE INTO `$review_tool_criteria_table` (`review_tool_key`, `criteria_key`) VALUES";
    // apply the criteria values to the query
    $review_tool_criteria_query .= " " . join(", ", $criteria_values);

    // run the query.
    $wpdb->query($review_tool_criteria_query);

  }
}

// register the review tool sync function as an action.
add_action('edreports_review_tools_sync', 'sync_edreports_review_tools', 10, 2);

// method to sync report data.
function sync_edreports_reports($cursor = null, $modified = null) {

  error_log("sync reports");

  // check if last modified date has been provided.
  if ($modified == null) {
    // get the last sync date.
    // @TODO TESTING: use modified date.
    // $modified = get_option('edreports_last_report_sync');
  }

  // update the last sync date.
  update_option('edreports_last_report_sync', date('Y-m-d', 0));

  // load the available reports from EdReports' API.
  $response = load_edreports_api_items("series/overview", $modified, $cursor);

  // ensure the request was successful.
  if ($response == null) {
    error_log("Plugin failed sync data: ensure the provided API key is valid.");
  } else if ($response->error) {
    error_log("Plugin failed to sync data: ($response->code) Error: $response->message");
  }

  // extract the report data.
  $items = $response->items;

  // ensure the response items is an array.
  if (is_array($items) && !empty($items)) {

    global $edreports_table_prefix, $wpdb;

    // build the series overview table name.
    $series_overview_table = $edreports_table_prefix . EDREPORTS_SERIES_OVERVIEW_TABLE;
    // initialize the series overview query.
    $series_overview_query = "INSERT INTO `$series_overview_table` (`series_key`, `data`) VALUES ('%s', '%s') ON DUPLICATE KEY UPDATE `data` = '%s';";

    // build the series criteria table name.
    $series_criteria_table = $edreports_table_prefix . EDREPORTS_SERIES_CRITERIA_TABLE;
    // build the series criteria query.
    $series_criteria_query = "INSERT IGNORE INTO `$series_criteria_table` (`series_key`, `criteria_key`) VALUES ";

    // iterate the response items and build the values.
    foreach ($items as $overview) {

      // legacy sync.
      edreports_legacy_series_import($overview);

      // extract the series key.
      $series_key = $overview->key;

      // extract the reports.
      $reports = $overview->reports;

      // initialize a values array to store report criteria entries.
      $series_criteria_values = array();

      // ensure the reports array is present and populated.
      if (is_array($reports) && !empty($reports)) {

        // iterate the series reports.
        foreach ($reports as $report) {

          // extract the criteria key.
          $criteria_key = $report->criteriaKey;

          // ensure the criteria key is set.
          if ($criteria_key) {

            // create the SQL value string.
            $series_criteria = array($series_key, $criteria_key);

            // check if the criteria array contains the value.
            if (!in_array($series_criteria, $series_criteria_values)) {
              // add the criteria value string to the array.
              $series_criteria_values[] = $series_criteria;
            }
          }
        }
      }

      // @TODO documents (technology info, publisher responses, etc)

      // encode any HTML found in the JSON.
      html_encode($overview);

      // encode as JSON.
      $series_overview_data = json_encode($overview);

      // build the series value array.
      $series_overview = array($series_key, $series_overview_data, $series_overview_data);
      // build the query.
      $series_overview_sql = $wpdb->prepare($series_overview_query, $series_overview);
      // run the query.
      $wpdb->query($series_overview_sql);

      // check for series criteria.
      if (!empty($series_criteria_values)) {

        // initialize a values array to build the SQL query.
        $values = array();

        // iterate the criteria values.
        foreach ($series_criteria_values as $series_criteria) {
          // build the SQL values string and add it to the array.
          $value = sprintf("('%s', '%s')", $series_criteria[0], $series_criteria[1]);
          // check if the value pair has been added already.
          if (!in_array($value, $values)) {
            // add the value.
            $values[] = $value;
          }
        }

        // join the values and apply to the series criteria query.
        $series_criteria_sql = $series_criteria_query . join(", ", $values);

        // run teh query.
        $wpdb->query($series_criteria_sql);
      }
    }

    // get the total number of items available.
    $total = $response->total ?: 0;
    // get the next page token.
    $nextPageToken = $response->nextPageToken ?: null;

    // check if another query should be run.
    if (!empty($nextPageToken) && count($items) < $total) {
      // schedule the next batch of reports to sync.
      wp_schedule_single_event(time(), 'edreports_reports_sync', array($nextPageToken, $modified));
    }
  }
}

// register the report sync function as an action.
add_action('edreports_reports_sync', 'sync_edreports_reports', 10, 2);

// legacy sync functions.
function edreports_legacy_series_import($overview) {

  global $wpdb;

  // series info.
  $series_key = $overview->key;
  $series_title = $overview->title;
  $series_technology_url = "";

  // load series documents (technology checklist).
  $documents_response = load_edreports_api_items("series/$series_key/documents");
  // check for a valid response.
  if ($documents_response != null) {
    // get the documents.
    $documents = $documents_response->items;
    // iterate the documents in search of the technology checklist.
    foreach ($documents as $document) {
      if (strcmp($document->category, "technology_information") == 0) {
        $series_technology_url = $document->url;
        break;
      }
    }
  }

  // subject info.
  $subject_key = $overview->subjectKey;

  // publisher info.
  $publisher_key = $overview->publisherKey;
  $publisher_name = $overview->publisher;

  // search publishers.
  $update_publisher_name = false;
  $publisher_results = $wpdb->get_results("SELECT * FROM `wp_er_publishers` WHERE `publisher_key`='$publisher_key'");
  foreach ($publisher_results as $row) {
    if (strcmp($row->publisher_key, $publisher_key) == 0) {
      $update_publisher_name = $row->publisher_name;
      break;
    }
  }

  // build the query.
  if (strcmp($update_publisher_name, $publisher_name)) {
    // prepare the publisher query.
    $publisher_query = "UPDATE `wp_er_publishers` SET `publisher_name`='%s' WHERE `publisher_key`='%s' AND `publisher_name`='%s'";
    $publisher_sql = $wpdb->prepare($publisher_query, array($publisher_name, $publisher_key, $update_publisher_name));
    // run the publisher query.
    $wpdb->query($publisher_sql);
  } else if ($update_publisher_name === false) {
    // prepare the query.
    $publisher_query = "INSERT INTO `wp_er_publishers` (`publisher_key`, `publisher_name`) VALUES ('%s', '%s')";
    $publisher_sql = $wpdb->prepare($publisher_query, array($publisher_key, $publisher_name));
    // run the publisher query.
    $wpdb->query($publisher_sql);
  }

  // create the series query.
  $series_query = "INSERT INTO `wp_er_series` (
                            `series_key`, 
                            `series_publisher_key`, 
                            `series_title`, 
                            `series_subject_key`, 
                            `series_technology_url`) 
                    VALUES ('%s', '%s', '%s', '%s', '%s') 
                    ON DUPLICATE KEY UPDATE 
                            `series_publisher_key` = '%s', 
                            `series_title` = '%s', 
                            `series_subject_key` = '%s', 
                            `series_technology_url` = '%s';";

  // create the series query vars array.
  $series_vars = array(
    $publisher_key,
    $series_title,
    $subject_key,
    $series_technology_url
  );

  // prepare the query.
  $series_sql = $wpdb->prepare($series_query, array_merge(array($series_key), $series_vars, $series_vars));

  // update the series table.
  $wpdb->query($series_sql);

  // extract reports.
  $reports = $overview->reports;

  // iterate the reports.
  foreach ($reports as $report) {

    // get the review tool key.
    $tool_key = $report->reviewToolKey;

    // report info.
    $report_key = $report->key;
    $grade_key = $report->gradeKey;
    $report_url = "https://edreports.org/reports/detail/$report->slug";
    $report_alignment = $report->alignment->readable;
    $report_usability = $report->usability->readable;
    $report_alignment_kebab = $report->alignment->kebab;
    $report_usability_kebab = $report->usability->kebab;
    $report_criteria_key = $report->criteriaKey;

    // gateways.
    $gateways = $report->gateways;
    $gateway_1_score = 0 < count($gateways) ? $gateways[0] : 0;
    $gateway_2_score = 1 < count($gateways) ? $gateways[1] : 0;
    $gateway_3_score = 2 < count($gateways) ? $gateways[2] : 0;

    // create the report query.
    $report_query = "INSERT INTO `wp_er_reports` (
                            `report_key`, 
                            `report_series_key`, 
                            `report_grade_key`, 
                            `report_tool_key`, 
                            `report_url`,
                            `report_alignment`,
                            `report_usability`,
                            `report_criteria_key`,
                            `report_alignment_kebab`,
                            `report_usability_kebab`,
                            `report_gateway_1`,
                            `report_gateway_2`,
                            `report_gateway_3`) 
                    VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') 
                    ON DUPLICATE KEY UPDATE 
                            `report_key` = '%s', 
                            `report_series_key` = '%s', 
                            `report_grade_key` = '%s', 
                            `report_tool_key` = '%s', 
                            `report_url` = '%s',
                            `report_alignment` = '%s',
                            `report_usability` = '%s',
                            `report_criteria_key` = '%s',
                            `report_alignment_kebab` = '%s',
                            `report_usability_kebab` = '%s',
                            `report_gateway_1` = '%s',
                            `report_gateway_2` = '%s',
                            `report_gateway_3` = '%s';";

    $report_vars = array(
      $report_key, 
      $series_key,
      $grade_key,
      $tool_key,
      $report_url,
      $report_alignment,
      $report_usability,
      $report_criteria_key,
      $report_alignment_kebab,
      $report_usability_kebab,
      $gateway_1_score,
      $gateway_2_score,
      $gateway_3_score
    );
    
    // prepare the query.
    $report_sql = $wpdb->prepare($report_query, array_merge($report_vars, $report_vars));

    // run the query.
    $wpdb->query($report_sql);

    // create a report meta entry.
    $wpdb->query("INSERT IGNORE INTO `wp_er_reports_meta` 
                        (`report_key`, `report_series_key`, `report_adopted`) 
                        VALUES ('$report_key', '$series_key', 'n_a')");
  }

  // load the review tools.
  $series_tools_response = load_edreports_api_items("series/$series_key/tools");
  if ($series_tools_response && $series_tools_response->items) {
    $series_tools = $series_tools_response->items;
    foreach ($series_tools as $series_tool) {
      $series_tool_slug = $series_tool->slug;
      $series_tool_query = "SELECT `tool_key` FROM `wp_er_tools` WHERE `tool_slug`='$series_tool_slug'";
      $series_tool_results = $wpdb->get_results($series_tool_query);

      if (is_array($series_tool_results) && 0 < count($series_tool_results)) {
        $series_tool_result = $series_tool_results[0];
        $series_tool_key = $series_tool_result->tool_key;

        $tool_query = "INSERT IGNORE INTO `wp_er_series_tools` (`series_key`, `series_tool_key`) 
                                     VALUES ('$series_key', '$series_tool_key')";
        $wpdb->query($tool_query);
      } else {
        // @TODO load/add missing tool.
      }
    }
  }
  // create series meta entry.
  $wpdb->query("INSERT IGNORE INTO `wp_er_series_meta` 
                      (`series_key`, `series_enabled_status`) 
                      VALUES ('$series_key', 1)");
}

//--------------------------------------------------
// UTILS
//--------------------------------------------------

/**
 * @param string $endpoint the path to the endpoint relative to the EDREPORTS_API_BASE_URL constant.
 * @param string|null $modified the date (YYYY-MM-DD) since the last update.
 * @param string|null $cursor string the cursor token to be applied to the request (used in paginating requests).
 * @param int $limit the number of response items to limit the request to.
 * @return mixed the decoded response object from the Reports API.
 */
function load_edreports_api_items(string $endpoint, string $modified = null, string $cursor = null, int $limit = 20) {

  global $edreports_api_key;

  // build the request.
  $request = EDREPORTS_API_BASE_URL . "/$endpoint";
  // add the api key to the request along with a modest response limit.
  $request .= "?key=$edreports_api_key&limit=$limit";

  if ($modified) {
    // only request items modified since the last update.
    $request .= "&modified=$modified";
  }

  // apply the cursor if set.
  if ($cursor) {
    $request .= "&cursor=$cursor";
  }

  // load the endpoint content.
  $json = file_get_contents($request);

  // return the decode the json data.
  return json_decode($json);
}

/**
 * Iterates an object and runs htmlentities on all string values. Runs recursively through the object and updates the values.
 * @param $object
 * @return void
 */
function html_encode(&$object) {

  if (!empty($object)) {

    if (is_string($object)) {
      $object = htmlentities($object);
    } else if (is_array($object) || is_object($object)) {
      // iterate items
      foreach ($object as &$value) {
        // encode the item value.
        html_encode($value);
      }
    }
  }
}