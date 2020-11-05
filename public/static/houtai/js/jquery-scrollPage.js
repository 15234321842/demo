/**
 * Created by Administrator on 15-6-26.
 */
+function ($) {
    "use strict";

    $.fn.scrollPage = function(distance,totalPage,doPaging,donePaging){
        var scrollObject = {};

        var currentPage = new Number();
        var scrollHeight = new Number();
        var scrollTop = new Number();
        var height = new Number();
        currentPage = 1;

        scrollObject.disance = distance || 50;
        scrollObject.page = currentPage;
        scrollObject.loading = false;

        $(this).scroll(function(){
            totalPage = parseInt(totalPage);
            if(isNaN(totalPage) || totalPage < 1) totalPage = 1;

            if(currentPage == totalPage){
                $(this).unbind("scroll");
                if($.isFunction(donePaging)) donePaging();
            }

            scrollHeight = $(this).scrollHeight();
            scrollTop = $(this).scrollTop();
            height = $(window).height();

            if((scrollHeight - height - scrollTop) <= scrollObject.disance){
                if(scrollObject.loading) return false;
                scrollObject.loading = true;

                if(totalPage > 1){
                    currentPage += 1;
                    scrollObject.page = currentPage;

                    if(currentPage <= totalPage){
                        if($.isFunction(doPaging)) doPaging(scrollObject);
                    }
                }
            }
        });
    }
}($);
