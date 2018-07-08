jQuery(function($){
    "use strict";

    var wp_stem_googel_fonts =(function(){
        /**
         * instead of normal index array,key is font family name
         */
        var cache;

        var initial_values = [];
        var count_popup = 0;

        function init_picker(){
            $('.wp_stem-font-picker-content').each(function(){
                wp_stem_popup({
                    title: 'Google Font Picker',
                    button: $(this).prev('.wp_stem-font-picker'),
                    content: $(this).clone().html()
                });
                var font_family_val = $(this).find('.wp_stem-google-fonts-family').val();

                if(font_family_val){
                    $(this).prev('.wp_stem-font-picker').html(font_family_val);
                }
            }).remove();

            $('.wp_stem-font-picker').on('click',function () {
                set_intial_values($(this).next('.wp_stem-popup'));
            });
        }

        function set_intial_values(self){
            var key  = self.attr('id');

            initial_values[key] = [];
            initial_values[key]['family'] = self.find('.wp_stem-google-fonts-family').first().val();
            initial_values[key]['size'] = self.find('.wp_stem-google-fonts-size').first().val();
            initial_values[key]['variants'] = self.find('.wp_stem-google-fonts-variants').first().val();
            initial_values[key]['subsets'] = self.find('.wp_stem-google-fonts-subsets').first().val();

            populate_select(self.find('.wp_stem-google-fonts-family'),'.wp_stem-google-fonts-variants','variants');
            populate_select(self.find('.wp_stem-google-fonts-family'),'.wp_stem-google-fonts-subsets','subsets');

            self.find('.wp_stem-google-fonts-variants').val(initial_values[key]['variants']);
            self.find('.wp_stem-google-fonts-subsets').val(initial_values[key]['subsets']);

        }

        /**
         * sends ajax request to retrieve google fonts data and caches too
         *
         * @returns {*}
         */
        function ajax(){
            return $.when(
                cache ||
                $.ajax({
                    url: 'https://www.googleapis.com/webfonts/v1/webfonts',
                    method: 'GET',
                    dataType: "jsonp",
                    data: {
                        key: 'AIzaSyAJ5XD_BT5yGTrRn1I1j9pQjhrqElEKYWA',
                        sort: 'popularity',
                        fields: 'items'
                    },
                    success: function(data) {
                        var items = data.items;
                        cache = [];

                        for(var i = 0; i < items.length; i++) {
                            cache[items[i].family] = items[i];
                        }
                    },
                    error: function () {
                        console.error('Google Fonts Api Error');
                    }
                })
            );
        }

        function family(){
            var family_selector = '.wp_stem-google-fonts-family';
            var variants_selector = '.wp_stem-google-fonts-variants';
            var subsets_selector = '.wp_stem-google-fonts-subsets';

            $(family_selector).attr("disabled","disabled");

            $(family_selector).on('focus',function(){
                clear_border_colors();
            });

            ajax().then(function() {
                if($.isFunction( $.fn.autocomplete ) ){
                    $(family_selector).removeAttr("disabled");

                    $(family_selector).autocomplete({
                        source: Object.keys(cache),
                        search: function(event,ui){
                            $(this).attr('value',$(this).val());

                            if(!populate_select($(this), variants_selector,'variants')){
                                wp_stem_popup_message('No Fonts Variants Found');
                            }

                            if(!populate_select($(this), subsets_selector,'subsets')){
                                wp_stem_popup_message('No Fonts Subsets Found');
                            }
                        },
                        select: function(event, ui){
                            $(this).attr('value',ui.item.value);

                            populate_select($(this), variants_selector, 'variants');
                            populate_select($(this), subsets_selector, 'subsets');
                        }
                    });
                }else{
                    wp_stem_popup_message('Error: jQuery autocomplete is not laoded',true);
                }
            });
        }


        /**
         *  populates select tag values by font family value
         *
         * @param font_family_element     font family input selector
         * @param selector_element        selector of select tag
         * @param font_attribute          field of cache object
         */
        function populate_select(font_family_element, selector_element, font_attribute){
            selector_element = font_family_element.parent().find(selector_element);

            selector_element.attr("disabled","disabled");
            selector_element.empty();

            if(font_family_element.val() && cache[font_family_element.val()]){
                var attr = cache[font_family_element.val()][font_attribute];

                for(var i=0; i < attr.length; i++) {
                    $('<option>', {
                        value: attr[i],
                        text: attr[i]
                    }).appendTo(selector_element);
                }

                selector_element.removeAttr("disabled");

                return true;
            }else{
                selector_element.attr("disabled","disabled");

                return false;
            }
        }



        function wp_stem_popup_message(options){
            if(options.global){
                $('<div/>',{
                    class: 'error',
                    text: options.message
                }).appendTo('.wp_stem-popup-content');
            }
        }

        function wp_stem_popup(options){
            var parent = $(options.button).parent();

            count_popup++;

            while($('#wp_stem-popup-' + count_popup).length !== 0 ){
                count_popup++;
            }

            $('<div/>',{
                class: 'wp_stem-popup',
                id: 'wp_stem-popup-' + count_popup
            }).appendTo(parent);

            var popup = parent.find('.wp_stem-popup').first();

            $('<h4/>',{
                class: 'wp_stem-popup-header',
                text: options.title
            }).appendTo(popup);

            $('<span/>',{
                class: 'dashicons dashicons-no-alt wp_stem-popup-close'
            }).appendTo(popup);

            $('<a/>',{
                href: "https://fonts.google.com/",
                target: "_blank",
                class: 'wp_stem-popup-link',
                text: "Preview Google Fonts"
            }).appendTo(popup);

            $('<p/>',{
                class: 'wp_stem-popup-hint',
                text: "Hint: Don't nerd over typefaces, just pick one"
            }).appendTo(popup);

            $('<div/>',{
                class: 'wp_stem-popup-content',
                html: options.content
            }).appendTo(popup);

            $('<button/>',{
                class: "button button-primary button-popup-submit",
                text: "Save"
            }).appendTo(popup.children('.wp_stem-popup-content'));

            $(options.button).on('click',function(event){
                event.preventDefault();

                $(this).next(popup).show();
            });

            $(popup).find('.wp_stem-popup-close').on('click',function (event) {
                event.preventDefault();

                $(this).parent(popup).hide();
            });
        }

        function save(popup){
            var family = popup.find('.wp_stem-google-fonts-family');
            var variants = popup.find('.wp_stem-google-fonts-variants');
            var subsets = popup.find('.wp_stem-google-fonts-subsets');
            var size =  popup.find('.wp_stem-google-fonts-size');

            if(validate(family) && validate(variants)  && validate(subsets) && validate(size)){
                clear_border_colors();
                popup.prev('.wp_stem-font-picker').html(family.val());
                popup.hide();
            }

            set_intial_values(popup);
        }

        function cancel(popup){
            var key = popup.attr('id');

            if(initial_values[key]) {
                var family = popup.find('.wp_stem-google-fonts-family');
                var variants = popup.find('.wp_stem-google-fonts-variants');
                var subsets = popup.find('.wp_stem-google-fonts-subsets');
                var size =  popup.find('.wp_stem-google-fonts-size');

                family.val(initial_values[key]['family']);
                size.val(initial_values[key]['size']);

                populate_select(family,variants,'variants');
                populate_select(family,subsets,'subsets');

                variants.val(initial_values[key]['variants']);
                subsets.val(initial_values[key]['subsets']);

                clear_border_colors();
            }
        }

        /**
         * when clicked on popup save button,checks for field validation
         * and hides popup
         */
        function on_click_save_btn(){
            $('.button-popup-submit').on('click',function(event){
                event.preventDefault();

                save($(this).parents('.wp_stem-popup').first());
            });
        }

        function on_click_cancel_btn(){
            $('.wp_stem-popup-close').on('click',function(event){
                event.preventDefault();

                cancel($(this).parent());
            });
        }

        function on_keypress(){
            $('.wp_stem-google-fonts-family').on('keypress', function(event){
                if(event.which == 13){// Enter
                    event.preventDefault();

                    save($(this).parents('.wp_stem-popup').first());
                }

                if(event.which == 0 ){ // Esc
                    event.preventDefault();

                    cancel($(this).parents('.wp_stem-popup').first());
                }
            });
        }

        function clear_border_colors(){
            $('.wp_stem-google-fonts-family').css('border-color','');
            $('.wp_stem-google-fonts-variants').css('border-color','');
            $('.wp_stem-google-fonts-subsets').css('border-color','');
            $('.wp_stem-google-fonts-size').css('border-color','');
        }

        /**
         * basic validation.just checks if has value,if hasn't changes border color and
         * returns false
         *
         * @param element
         * @returns {boolean}
         */
        function validate(element){
            if( !element.val() ){
                element.css('border-color','orangered');

                element.on('focus',function(){
                    element.css('border-color','');
                });

                return false;
            }

            return true;
        }

        return{
            init:function(){
                init_picker();
                family();

                on_click_save_btn();
                on_click_cancel_btn();
                on_keypress();
            }
        }
    })();

    wp_stem_googel_fonts.init();
});