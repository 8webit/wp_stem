jQuery(function ($) {
    "use strict";

    var wp_stem_wp_media = (function () {
        var wpMediaUploader = {
            wrapper: ".stem-media-uploader",
            add_button: ".stem-upload-wp-media",
            remove_button: ".stem-remove-wp-media",
            media_data: ".media-data",
            input: ".stem-wp-media"
        };

        function wpMedia() {
            $(wpMediaUploader.add_button).on("click", function (event) {
                event.preventDefault();

                var self = $(this);
                var wrapper = self.parents(wpMediaUploader.wrapper);

                // Create a new media frame
                var frame = wp.media({
                    title: "Upload Media",
                    button: {
                        text: "Use this media"
                    },
                    multiple: false // Set to true to allow multiple files to be selected
                });

                frame.on("select", function () {
                    var attachment = frame.state().get("selection").first().toJSON();
                    
                    if (wrapper.find('.media_show_image').length) {
                        switch (attachment.mime) {
                            case "image/jpeg":
                            case "image/png":
                            case "image/bmp":
                            case "image/gif":

                                wrapper.find(wpMediaUploader.media_data)
                                    .append("<img src=\"" + attachment.url + "\" alt=\"\" />");
                                break;
                        }
                    }

                    $("<p/>", {
                        text: attachment.filename
                    }).appendTo(wrapper.find(wpMediaUploader.media_data));

                    wrapper.find(wpMediaUploader.input).val(attachment.id);
                    wrapper.find(wpMediaUploader.remove_button).removeClass("hidden");
                    self.addClass("hidden");

                    frame.close();
                });

                frame.open();
            });


            $(wpMediaUploader.remove_button).on("click", function (event) {
                event.preventDefault();

                var self = $(this);
                var parent = self.parents(wpMediaUploader.wrapper);

                // Clear out the preview image
                parent.find(wpMediaUploader.media_data).html("");

                // Un-hide the add image link
                parent.find(wpMediaUploader.add_button).removeClass("hidden");

                // Delete the image id from the hidden input
                parent.find(wpMediaUploader.input).val("");

                // Hide the delete image link
                self.addClass("hidden");
            });
        }

        return {
            init: function () {
                wpMedia();
            }
        }
    })();


    wp_stem_wp_media.init();
});