(function($){
    $('.side-menu > ul > li > a').on('mouseover',function(){
        $(this).next('ul.sub-menu').slideDown('fast');
    });
    $('.side-menu').on('mouseleave',function(){
        $('.sub-menu').slideUp('fast');
    });
})(jQuery);