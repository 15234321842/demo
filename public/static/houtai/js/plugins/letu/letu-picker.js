/**
 * letu 通用弹出选择插件
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
        '            <div class="letu-flex-item">\n' +
        '                <span class="fa fa-angle-left letu-popup-close"></span>\n' +
        '                <span class="letu-tab-title title-left highlight-text-red">{{tabTitleLeft}}</span>\n' +
        '            </div>\n' +
        '            <div class="letu-flex-item">\n' +
        '                <span class="letu-tab-title title-right">{{tabTitleRight}}</span>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </header>\n' +
        '    <div class="letu-tab tab-body-left tab-select">\n' +
        '        <div class="letu-select-body">\n' +
        '            <div class="letu-select-body-flex">\n' +
        '                <div class="weui-cells_checkbox letu-pull-data" style="width:100%;"></div>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '        <div class="letu-bottom-btn">\n' +
        '            <div class="weui-btn-area letu-btn-area">\n' +
        '                <button type="button" class="weui-btn weui-btn_warn letu-btn-select">{{tabBtnLeftText}}</button>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '    <div class="letu-tab tab-body-right">\n' +
        '        <div class="letu-select-body" style="padding-top: 0;padding-left: 0;padding-right: 0;">\n' +
        '            <form id="letuFrmAdd" method="post">\n' +
        '                <div class="weui-cells weui-cells_form letu-cells_not_top_line">{{formBodyRight}}</div>\n' +
        '            </form>\n' +
        '        </div>\n' +
        '        <div class="letu-bottom-btn">\n' +
        '            <div class="weui-btn-area letu-btn-area">\n' +
        '                <button type="button" class="weui-btn weui-btn_warn letu-btn-add">{{tabBtnRightText}}</button>\n' +
        '            </div>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>',
        formBodyLeftHtml:'<label class="letu-radio-inline weui-check__label">\n' +
        '                        <div class="weui-cell__hd">\n' +
        '                            <input type="radio" name="radio_picker" class="weui-check letu-radio" value="{{itemKey}}"/>\n' +
        '                            <i class="weui-icon-checked"></i>\n' +
        '                        </div>\n' +
        '                        <div class="weui-cell__bd"><p class="letu-radio-text">{{itemValue}}</p></div>\n' +
        '                    </label>',
        formBodyRightHtml:'<div class="weui-cell">\n' +
        '                        <div class="weui-cell__hd"><label class="weui-label">{{fieldText}}：</label></div>\n' +
        '                        <div class="weui-cell__bd">\n' +
        '                            <input type="text" name="{{fieldName}}" class="weui-input" placeholder="请输入{{fieldText}}" maxlength="100" />\n' +
        '                        </div>\n' +
        '                    </div>',
        tabTitleLeft:'选择',
        tabTitleRight:'添加',
        tabBtnLeftText:'确定选择',
        tabBtnRightText:'确定添加',
        tabFormRight:[],
        tabFormLeftUrl:'',
        tabFormRightUrl:'',
        tabFormLeftKeyName:'',
        tabFormLeftValueName:'',
        tabSelectCallback:false
    };

    $.fn.letuPicker = function(options){
        var defaults_options = $.extend({},defaults, options);

        var templateObj = null;
        var templateHtml = defaults.templateHtml;

        var initTabFormLeft = function(){
            if(defaults_options.tabFormLeftUrl != ''){
                $.ajax({
                    url:defaults_options.tabFormLeftUrl,
                    dataType:'json',
                    success:function(response){
                        if(response.success){
                            var formBodyLeftHtmlList = '';
                            if(response.data.length > 0){
                                for(var i=0;i<response.data.length;i++){
                                    var data = response.data[i];
                                    var formBodyLeftHtml = defaults_options.formBodyLeftHtml;
                                    formBodyLeftHtml = formBodyLeftHtml.replace(/{{itemKey}}/g, data[defaults_options.tabFormLeftKeyName]);
                                    formBodyLeftHtml = formBodyLeftHtml.replace(/{{itemValue}}/g, data[defaults_options.tabFormLeftValueName]);

                                    formBodyLeftHtmlList += formBodyLeftHtml;
                                }
                            }

                            if(formBodyLeftHtmlList != ''){
                                $('.letu-pull-data',templateObj).html(formBodyLeftHtmlList);
                            }
                        }
                    }
                });
            }
        };

        var showPopup = function(obj){
            templateHtml = templateHtml.replace(/{{tabTitleLeft}}/g, defaults_options.tabTitleLeft);
            templateHtml = templateHtml.replace(/{{tabTitleRight}}/g, defaults_options.tabTitleRight);
            templateHtml = templateHtml.replace(/{{tabBtnLeftText}}/g, defaults_options.tabBtnLeftText);
            templateHtml = templateHtml.replace(/{{tabBtnRightText}}/g, defaults_options.tabBtnRightText);

            //组装左面板表单
            initTabFormLeft();

            //组装右面板表单
            var formBodyRightHtmlList = '';
            if($.isArray(defaults_options.tabFormRight) && defaults_options.tabFormRight.length > 0){
                for(var i=0;i<defaults_options.tabFormRight.length;i++){
                    var field = defaults_options.tabFormRight[i];
                    var formBodyHtml = defaults_options.formBodyRightHtml;
                    formBodyHtml = formBodyHtml.replace(/{{fieldText}}/g, field.fieldText);
                    formBodyHtml = formBodyHtml.replace(/{{fieldName}}/g, field.fieldName);

                    formBodyRightHtmlList += formBodyHtml;
                }
            }

            if(formBodyRightHtmlList != ''){
                templateHtml = templateHtml.replace(/{{formBodyRight}}/g, formBodyRightHtmlList);
            }

            templateObj = $(templateHtml);

            $('body').append(templateObj);
            $('.letu-popup-close',templateObj).bind('click',closePopup);
            $('.title-left',templateObj).bind('click',selectLeft);
            $('.title-right',templateObj).bind('click',selectRight);
            $('.letu-btn-add',templateObj).bind('click',commandAdd);
            $('.letu-btn-select',templateObj).bind('click',commandSelect);
        };

        var selectLeft = function(){
            $('.title-right',templateObj).removeClass('highlight-text-red');
            $('.title-left',templateObj).addClass('highlight-text-red');

            $('.tab-body-right',templateObj).removeClass('tab-select');
            $('.tab-body-left',templateObj).addClass('tab-select');

            $('.letu-pull-data',templateObj).html('');
            initTabFormLeft();
        };

        var selectRight = function(){
            $('.title-left',templateObj).removeClass('highlight-text-red');
            $('.title-right',templateObj).addClass('highlight-text-red');

            $('.tab-body-left',templateObj).removeClass('tab-select');
            $('.tab-body-right',templateObj).addClass('tab-select');

            var form = $('#letuFrmAdd',templateObj);
            form.resetForm();
        };

        var commandAdd = function(){
            if(defaults_options.tabFormRightUrl != ''){
                var form = $('#letuFrmAdd',templateObj);
                form.attr("action",defaults_options.tabFormRightUrl);
                form.ajaxForm({
                    dataType:'json',
                    success:function(data){
                        if(data.success){
                            $.toptip(data.msg, 'success');
                        }else{
                            $.toptip(data.msg, 'error');
                        }
                    }
                });
                form.submit();
            }
        };

        var commandSelect = function(){
            var tab_body_left = $('.tab-body-left',templateObj);
            var $radio = $("input.letu-radio:checked",tab_body_left);
            var $radioText =$radio.closest("label").find(".letu-radio-text");
            if($.isFunction(defaults_options.tabSelectCallback)){
                if($radio.length > 0){
                    defaults_options.tabSelectCallback($radio.val(),$radioText.html());
                }else{
                    defaults_options.tabSelectCallback("","");
                }
            }
            closePopup();
        };

        var closePopup = function(){
            if(templateObj != null){
                $('.letu-popup-close',templateObj).unbind();
                templateObj.remove();
            }
        };

        return this.each(function(){
            $(this).on("click",this,showPopup);
        });
    }
})(jQuery);