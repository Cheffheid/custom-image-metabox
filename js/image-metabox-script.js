var JJImageMetabox = (function ($) {
  'use strict';

  var uploader = function (id) {
    var frame = wp.media({
      title: JJImageMetaboxi18n.frame_title,
      multiple: false,
      library: { type: 'image' },
      button: { text: JJImageMetaboxi18n.button_title },
    });

    // Handle results from media manager.
    frame.on('close', function () {
      var attachments = frame.state().get('selection').toJSON();
      JJImageMetabox.render(id, attachments[0]);
    });

    frame.open();
    return false;
  };

  var render = function (id, attachment) {
    console.log(id, attachment);
    if (attachment) {
      $(id + ' .hidden').removeClass('hidden');
      var $img = $(id + ' .img_preview img');
      var append = false;

      if ($img.length === 0) {
        $img = $('<img />');
        append = true;
      }

      if (attachment.sizes.hasOwnProperty('thumbnail')) {
        $img.attr('src', attachment.sizes.thumbnail.url);
        $img.attr('width', attachment.sizes.thumbnail.width);
        $img.attr('height', attachment.sizes.thumbnail.height);
      } else {
        $img.attr('src', attachment.sizes.full.url);
        $img.attr('width', attachment.sizes.full.width);
        $img.attr('height', attachment.sizes.full.height);
      }

      $(id + ' .image_id').val(attachment.id);

      if (append) {
        var $button = $('<button />');
        $button.addClass('clear');

        $button.click(function () {
          JJImageMetabox.clear(id);
        });

        $button.text('X');
        $button.attr('aria-label', JJImageMetaboxi18n.clear_image);

        $(id + ' .img_preview').append($img);
        $(id + ' .img_preview').append($button);
      }
    }
  };

  var clear = function (id) {

    $(id + ' .img_preview').empty();
    $(id + ' .image_id').val('');

    return false;
  };

  return {
    uploader: uploader,
    render: render,
    clear: clear,
  };
})(jQuery);
