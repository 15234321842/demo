/**
 * letu 人员选择插件
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-17
 * Time: 15:50:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */
;(function($){
    "use strict";
    var defaults = {
        templateHtml:'<div class="page-group letu-popup">\n' +
        '    <header class="bar bar-nav">\n' +
        '        <div class="letu-flex">\n' +
        '            <div class="letu-flex-item">\n' +
        '                <span class="fa fa-angle-left letu-popup-close"></span>\n' +
        '                <span class="letu-tab-title">{{titleLeft}}</span>\n' +
        '            </div>\n' +
        '            <div class="letu-flex-item">\n' +
        '                <span class="letu-tab-title highlight-text-red">{{titleRight}}</span>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </header>\n' +
        '    <div class="letu-user">\n' +
        '        <div class="letu-user-body">\n' +
        '            <div class="weui-cells_notbefore letu-customer">\n' +
        '                <div class="weui-flex">\n' +
        '                    <div class="weui-flex__item pull-data-left">\n' +
        '                    </div>\n' +
        '                    <div class="weui-flex__item pull-data-right">\n' +
        '                    </div>\n' +
        '                </div>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '    <div class="letu-bottom-btn">\n' +
        '        <div class="weui-btn-area letu-btn-area">\n' +
        '            <button type="button" class="weui-btn weui-btn_warn letu-btn-select">确定选择</button>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>',
        itemHtml:'<div class="weui-cell">\n' +
'                            <div class="weui-cell__bd">\n' +
'                                <span><span class="weui-badge">{{badgeText}}</span>{{itemValue}}</span>\n' +
'                                <span data-key="{{itemKey}}" data-value="{{itemValue}}" class="letu-command fa pull-right"></span>\n' +
'                            </div>\n' +
'                        </div>',
        url:'',
        multiple:false,
        mapKeyName:'',
        mapValueName:'',
        mapBadgeText:'',
        mapBadgeId:'',
        hiddenId:'',
        selectCallback:false
    };

    $.fn.letuPickerUser = function(options){
        var defaults_options = $.extend({},defaults, options);

        var templateObj = null;
        var templateHtml = defaults_options.templateHtml;

        var showPopup = function(){
            templateHtml = templateHtml.replace(/{{titleLeft}}/g, defaults_options.titleLeft);
            templateHtml = templateHtml.replace(/{{titleRight}}/g, defaults_options.titleRight);

            templateObj = $(templateHtml);


            $('body').append(templateObj);

            if($.trim(defaults_options.url) != ""){
                loadData();
            }

            $('.letu-popup-close',templateObj).bind('click',closePopup);
            $('.letu-btn-select',templateObj).bind('click',commandSelect);
            $(templateObj).on('click','.letu-command.fa-plus-square',commandPullRight);
            $(templateObj).on('click','.letu-command.fa-minus-square',commandPullLeft);
        };

        var commandSelect = function(){
            if($.isFunction(defaults_options.selectCallback)){
                var selItems = [];
                var itemList = $(".letu-user-body .pull-data-right .weui-cell .letu-command");
                if(itemList.length > 0){
                    for(var i=0;i<itemList.length;i++){
                        var obj = {};
                        obj.key = itemList.eq(i).attr("data-key");
                        obj.value = itemList.eq(i).attr("data-value");

                        selItems.push(obj);
                    }
                }
                defaults_options.selectCallback(selItems);
            }
            closePopup();
        };

        var commandPullLeft = function(){
            var $this = $(this);
            $this.removeClass('fa-minus-square');
            $this.addClass('fa-plus-square');
            $this.closest(".weui-cell").appendTo(".letu-user-body .pull-data-left")
        };

        var commandPullRight = function(){
            var itemList = $(".letu-user-body .pull-data-right .weui-cell");
            if(!defaults_options.multiple){
                if(itemList.length > 0){
                    $.toptip('只能选择一条记录！', 'error');
                    return false;
                }
            }

            var $this = $(this);
            $this.removeClass('fa-plus-square');
            $this.addClass('fa-minus-square');
            $this.closest(".weui-cell").appendTo(".letu-user-body .pull-data-right")
        };

        var loadData = function(){
            var dataJson = {};
            if($.trim(defaults_options.hiddenId) != ""){
                var hiddenId = $("#"+defaults_options.hiddenId);
                if(hiddenId.length > 0){
                    dataJson.ids = hiddenId.val();
                }
            }
            $.ajax({
                url:defaults_options.url,
                dataType:'json',
                data:dataJson,
                success:function(response){
                    if(response.success){
                        var leftLength = response.data.listLeft.length;
                        var rightLength = response.data.listRight.length;
                        if(leftLength > 0){
                            var pull_data = $(".pull-data-left",templateObj);
                            var items = [];
                            for(var i=0;i<leftLength;i++){
                                var data = response.data.listLeft[i];
                                var itemHtml = defaults_options.itemHtml;
                                itemHtml = itemHtml.replace(/{{itemValue}}/g, data[defaults_options.mapValueName]);
                                itemHtml = itemHtml.replace(/{{itemKey}}/g, data[defaults_options.mapKeyName]);
                                itemHtml = itemHtml.replace(/{{badgeText}}/g, data[defaults_options.mapBadgeText]);

                                items[i] = $(itemHtml);
                                items[i].find(".letu-command").addClass('fa-plus-square');
                                if(data[defaults_options.mapBadgeId] == 1){
                                    items[i].find(".weui-badge").addClass('badge-old');
                                }
                            }

                            if(items.length > 0){
                                pull_data.html(items);
                            }
                        }

                        if(rightLength > 0){
                            var pull_data = $(".pull-data-right",templateObj);
                            var items = [];
                            for(var i=0;i<rightLength;i++){
                                var data = response.data.listRight[i];
                                var itemHtml = defaults_options.itemHtml;
                                itemHtml = itemHtml.replace(/{{itemValue}}/g, data[defaults_options.mapValueName]);
                                itemHtml = itemHtml.replace(/{{itemKey}}/g, data[defaults_options.mapKeyName]);
                                itemHtml = itemHtml.replace(/{{badgeText}}/g, data[defaults_options.mapBadgeText]);

                                items[i] = $(itemHtml);
                                items[i].find(".letu-command").addClass('fa-minus-square');
                                if(data[defaults_options.mapBadgeId] == 1){
                                    items[i].find(".weui-badge").addClass('badge-old');
                                }
                            }

                            if(items.length > 0){
                                pull_data.html(items);
                            }
                        }
                    }
                }
            });
        };

        var closePopup = function(){
            if(templateObj != null){
                $('.letu-popup-close',templateObj).unbind();
                templateObj.remove();
            }
        };

        return this.each(function(){
            $(this).on("click",showPopup);
        });
    };
})(jQuery);