/**
 * letu 联动选择插件
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-04
 * Time: 17:46:11
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */
;(function($){
    "use strict";
    var defaults = {
        templateHtml:'<div class="page-group letu-popup">\n' +
        '    <header class="bar bar-nav">\n' +
        '        <div class="letu-flex">\n' +
        '            <div class="letu-flex-item" style="border:none;">\n' +
        '                <span class="fa fa-angle-left letu-popup-close"></span>\n' +
        '                <span class="letu-tab-title">{{title}}</span>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </header>\n' +
        '    <div class="letu-linkage">\n' +
        '        <div class="letu-linkage-bar"></div>\n' +
        '        <div class="letu-linkage-body"></div>\n' +
        '    </div>\n' +
        '    <div class="letu-bottom-btn">\n' +
        '        <div class="weui-btn-area letu-btn-area">\n' +
        '            <button type="button" class="weui-btn weui-btn_warn letu-btn-select">确定选择</button>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>',
        tabBodyHtml:'<div class="letu-linkage-body-flex"><div class="weui-cells_checkbox letu-pull-data"></div></div>',
        itemHtml:'<label class="letu-radio-inline weui-check__label">\n' +
        '                <div class="weui-cell__hd">\n' +
        '                    <input type="radio" name="{{radioName}}" class="weui-check letu-radio" value="{{itemKey}}" data-pid="{{parentId}}"/>\n' +
        '                            <i class="weui-icon-checked"></i>\n' +
        '                </div>\n' +
        '                <div class="weui-cell__bd"><p class="letu-radio-text">{{itemValue}}</p></div>\n' +
        '            </label>',
        title:'标题',
        tabs:[],
        url:"",
        pid:0,
        mapKeyName:'',
        mapValueName:'',
        mapParentIdName:'',
        selectCallback:false
    };

    $.fn.letuPickerLinkage = function(options){
        var defaults_options = $.extend({},defaults, options);

        var templateObj = null;
        var templateHtml = defaults_options.templateHtml;
        var tabs = [];
        var bodys = [];
        var selItems = [];

        var maxLength = defaults_options.length > 4 ? 4 : defaults_options.tabs.length;

        var showPopup = function(){
            selItems = [];
            templateHtml = templateHtml.replace(/{{title}}/g, defaults_options.title);

            templateObj = $(templateHtml);

            if(defaults_options.tabs.length > 0){
                //添加选项卡
                for(var i=0;i<maxLength;i++){
                    tabs[i] = $('<div class="letu-linkage-bar-item">' + defaults_options.tabs[i] + '</div>');
                    bodys[i] = $('<div class="letu-linkage-body-flex"><div class="weui-cells_checkbox letu-pull-data"></div></div>');
                }
                if(tabs.length > 0){
                    $('.letu-linkage-bar',templateObj).html(tabs);
                    $('.letu-linkage-body',templateObj).html(bodys);

                    tabShow(0,defaults_options.pid);
                }
            }

            $('body').append(templateObj);

            $('.letu-popup-close',templateObj).bind('click',closePopup);
            $('.letu-linkage-bar-item',templateObj).bind('click',tabSelect);
            $(".letu-linkage-body").on("change","input[type='radio']",itemChange);
            $('.letu-btn-select',templateObj).bind('click',commandSelect);
        };

        var tabShow = function(tabIndex,pid){
            if(tabIndex < maxLength){
                var showLength = tabIndex+1;
                for(var i=0;i<maxLength;i++){
                    if(i >= tabIndex){
                        tabs[i].html(defaults_options.tabs[i]);

                        if(i > tabIndex){
                            bodys[i].find('.letu-pull-data').empty();
                        }
                    }

                    if(i <= showLength){
                        tabs[i].addClass("linkage-item-show");
                    }else{
                        tabs[i].removeClass("linkage-item-show");
                    }
                    tabs[i].removeClass("linkage-bar-select");
                    bodys[i].removeClass("flex-select");
                }

                tabs[tabIndex].addClass("linkage-bar-select");
                bodys[tabIndex].addClass("flex-select");

                if(defaults_options.url != ""){
                    loadData(pid,tabIndex);
                }
            }
        };

        var itemChange = function(){
            var $this = $(this);
            var obj = {};
            obj.itemPid = $this.attr("data-pid");
            obj.itemId = $this.val();
            obj.itemValue = $this.closest("label").find(".letu-radio-text").html();

            var allBodys = $('.letu-linkage-body-flex',templateObj);
            var currentBody = $this.closest(".letu-linkage-body-flex");
            var currentIndex = allBodys.index(currentBody);

            tabs[currentIndex].html(obj.itemValue);

            if(selItems.length > currentIndex){
                selItems[currentIndex] = obj;
            }else{
                selItems.push(obj);
            }

            var nextIndex = currentIndex + 1;
            tabShow(nextIndex,obj.itemId);
        };

        var loadData = function(pid,tabIndex){
            $.ajax({
                url:defaults_options.url,
                data:{pid:pid},
                dataType:'json',
                success:function(response){
                    var pull_data = bodys[tabIndex].find('.letu-pull-data');
                    if(response.success){
                        if(response.data.length > 0){
                            var items = [];
                            for(var i=0;i<response.data.length;i++){
                                var data = response.data[i];
                                var itemHtml = defaults_options.itemHtml;
                                itemHtml = itemHtml.replace(/{{radioName}}/g, 'radio_level_'+tabIndex);
                                itemHtml = itemHtml.replace(/{{itemKey}}/g, data[defaults_options.mapKeyName]);
                                itemHtml = itemHtml.replace(/{{itemValue}}/g, data[defaults_options.mapValueName]);
                                itemHtml = itemHtml.replace(/{{parentId}}/g, data[defaults_options.mapParentIdName]);

                                items[i] = $(itemHtml);
                            }
                        }

                        if(items.length > 0){
                            pull_data.html(items);
                        }
                    }else{
                        pull_data.empty();
                    }
                }
            });
        };

        var commandSelect = function(){
            if($.isFunction(defaults_options.selectCallback)){
                defaults_options.selectCallback(selItems);
            }
            closePopup();
        };

        var tabSelect = function(){
            var $this = $(this);
            var allTabs = $('.letu-linkage-bar-item',templateObj);
            var otherTabs = allTabs.not(this);
            otherTabs.removeClass("linkage-bar-select");
            $this.addClass("linkage-bar-select");

            var currentIndex = allTabs.index(this);
            var allBodys = $('.letu-linkage-body-flex',templateObj);
            allBodys.removeClass("flex-select");
            allBodys.eq(currentIndex).addClass("flex-select");
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
    }

})(jQuery);