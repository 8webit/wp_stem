jQuery(function($){
    "use strict";

    var wp_stem_spectrum =(function(){
        function spectrum() {
            $('.spectrum').spectrum({
                preferredFormat: 'hex',
                showInput: true,
                allowEmpty:true,
                showAlpha: true
            });
        }

        return{
            init:function(){
                spectrum();
            }
        }
    })();

    wp_stem_spectrum.init();
});
