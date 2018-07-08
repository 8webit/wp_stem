jQuery(function($){
    "use strict";

    var wp_stem_autocomplete =(function(){
        var url = 'http://localhost/wordpress/wp-json/wp/v2/';

        function author() {
            var authors = [];

            if($('.autocomplete-user').length > 0){
                $.ajax({
                    url: url + 'users',
                    method: 'GET',
                    dataType: "json",

                    error: function(){
                        authors = [{
                            label: 'Something Went Terribly Wrong!',
                            value: 0
                        }];
                    }
                }).done(function (data){
                    $('.autocomplete-user').autocomplete({
                        source: function(request,response){
                            response($.map(data,function(item){
                                return {
                                    label: item.slug +'('+ item.name + ')',
                                    value: item.slug
                                }
                            }));
                        },
                        minLength: 1
                    });

                }).fail(function (xhr) {
                    console.error('faild to get users, xhr-', xhr);
                });
            }
        }

        return{
            init:function(){
                author();
            }
        }
    })();

    wp_stem_autocomplete.init();
});