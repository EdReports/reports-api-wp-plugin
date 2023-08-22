var wpOpenGallery = function() {};

(function($) {

  wpOpenGallery = function(o, callback) {
    var options = (typeof o === 'object') ? o : {};

    // Predefined settings
    var defaultOptions = {
      title: 'Select Media',
      fileType: 'image',
      multiple: false,
      currentValue: '',
    };

    var opt = {...defaultOptions, ...options};

    var image_frame;

    if (image_frame) {
      image_frame.open();
    }

    // Define image_frame as wp.media object
    image_frame = wp.media({
      title: opt.title,
      multiple: opt.multiple,
      library: {
        type: opt.fileType,
      }
    });

    image_frame.on('open', function() {
      // On open, get the id from the hidden input
      // and select the appropiate images in the media manager
      var selection = image_frame.state().get('selection');
      var ids = opt.currentValue.split(',');

      ids.forEach(function(id) {
        var attachment = wp.media.attachment(id);
        attachment.fetch();
        selection.add(attachment ? [attachment] : []);
      });
    });

    image_frame.on('close', function() {
      // On close, get selections and save to the hidden input
      // plus other AJAX stuff to refresh the image preview
      var selection = image_frame.state().get('selection');
      var files = [];

      selection.each(function(attachment) {
        files.push({
          id: attachment.attributes.id,
          filename: attachment.attributes.filename,
          url: attachment.attributes.url,
          type: attachment.attributes.type,
          subtype: attachment.attributes.subtype,
          sizes: attachment.attributes.sizes,
        });
      });

      callback(files);
    });

    image_frame.open();
  };
}(jQuery));