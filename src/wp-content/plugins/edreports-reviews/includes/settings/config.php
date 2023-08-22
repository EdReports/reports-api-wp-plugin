<div class="wrap">
  <h1>EdReports Reviews</h1>
  <form method="post" action="options.php">
    <?php settings_fields('edreports-reviews-plugin-config-settings-group'); ?>
    <?php do_settings_sections('edreports-reviews-plugin-config-settings-group'); ?>
    <table class="form-table">
      <tr style="vertical-align: top">
        <th scope="row">EdReports API Key</th>
        <td><input type="text"
                   style="width: 420px; max-width: 100%;"
                   name="edreports_api_key"
                   value="<?php echo esc_attr(get_option('edreports_api_key')); ?>"/></td>
      </tr>
      <tr style="vertical-align: top">
        <th scope="row">Approved Review Tagline</th>
        <td><input type="text"
                   style="width: 420px; max-width: 100%;"
                   name="edreports_approved_review_text"
                   value="<?php echo esc_attr(get_option('edreports_approved_review_text')); ?>"/></td>
      </tr>
      <tr>
        <th scope="row">
          Approved Review Image
        </th>
        <td>
          <input type="hidden"
                 id="edreports_approved_review_image"
                 name="edreports_approved_review_image"
                 value="<?php echo get_option('edreports_approved_review_image'); ?>" />
          <div id="approved_review_image_container">
            <div>
              <img id="approved_review_image" title="Click to replace image"
                   src="<?php echo get_option('edreports_approved_review_image'); ?>" />
              <a id="clear_approved_review_image_btn"
                 title="Click to clear the image"
                 href="javascript:void(0);">[<span>x</span>]</a>
            </div>
          </div>
          <input id="select_approved_review_image_btn" type="button" value="Select Image" />
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
<script type="text/javascript">

  console.log("image", (jQuery('#edreports_approved_review_image').val()));

  if (jQuery('#edreports_approved_review_image').val()) {
    jQuery('#select_approved_review_image_btn').hide();
    jQuery('#approved_review_image_container').show();
  } else {
    jQuery('#approved_review_image_container').hide();
    jQuery('#select_approved_review_image_btn').show();
  }

  jQuery('#select_approved_review_image_btn').click(function() {
    wpOpenGallery(null, function(data) {
      var imageUrl = data[0] ? data[0].url : null;
      if (imageUrl) {
        jQuery('#approved_review_image').attr('src', imageUrl);
        jQuery('#approved_review_image_container').show();
        jQuery('#select_approved_review_image_btn').hide();
        jQuery('#edreports_approved_review_image').val(imageUrl);
      }
    });
  });

  jQuery('#approved_review_image').click(function() {
    wpOpenGallery(null, function(data) {
      var imageUrl = data[0] ? data[0].url : null;
      if (imageUrl) {
        jQuery('#approved_review_image').attr('src', imageUrl);
        jQuery('#edreports_approved_review_image').val(imageUrl);
      }
    });
  });

  jQuery('#clear_approved_review_image_btn').click(function() {
    jQuery('#approved_review_image_container').hide();
    jQuery('#select_approved_review_image_btn').show();
    jQuery('#approved_review_image').attr('src', '');
    jQuery('#edreports_approved_review_image').val('');
  });

</script>