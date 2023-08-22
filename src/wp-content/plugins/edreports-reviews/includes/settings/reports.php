<?php
$edreports_api_key = get_option('edreports_api_key');
$next = wp_next_scheduled('edreports_data_sync'); ?>
<div class="wrap">
  <h1>EdReports Reviews</h1>
  <p><b>EdReports API Key:</b> <?php echo $edreports_api_key; ?></p>
  <p><b>Next Scheduled Sync:</b> <?php echo date('m/d/Y h:i A (e)', $next); ?></p>
</div>