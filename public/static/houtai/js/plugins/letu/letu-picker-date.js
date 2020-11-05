/**
 * letu 年、月、日 选择插件
 * Author: ls-huang
 * Email: 282130106@qq.com
 * Date: 2018-10-18
 * Time: 22:00:00
 * Copyright (c) 2018～2118 http://www.letu33.com All rights reserved.
 */
;(function($) {
    "use strict";

    var defaults;
    var formatNumber = function (n) {
        return n < 10 ? "0" + n : n;
    };

    var Datetime = function(input, params) {
        this.input = $(input);
        this.params = params || {};

        this.initMonthes = params.monthes;

        this.initYears = params.years;

        var p = $.extend({}, params, this.getConfig());
        $(this.input).picker(p);
    };

    Datetime.prototype = {
        getDays : function(max) {
            var days = [];
            for(var i=1; i<= (max||31);i++) {
                days.push(i < 10 ? "0"+i : i);
            }
            return days;
        },

        getDaysByMonthAndYear : function(month, year) {
            var int_d = new Date(year, parseInt(month)+1-1, 1);
            var d = new Date(int_d - 1);
            return this.getDays(d.getDate());
        },
        getConfig: function() {
            var today = new Date(),
                params = this.params,
                self = this,
                lastValidValues;

            var config = {
                rotateEffect: false,  //为了性能
                cssClass: 'datetime-picker',

                value: [today.getFullYear(), formatNumber(today.getMonth()+1), formatNumber(today.getDate()), formatNumber(today.getHours()), (formatNumber(today.getMinutes()))],

                onChange: function (picker, values, displayValues) {
                    var cols = picker.cols;

                    if(params.showDay){
                        var days = self.getDaysByMonthAndYear(values[1], values[0]);
                        var currentValue = values[2];
                        if(currentValue > days.length) currentValue = days.length;
                        picker.cols[4].setValue(currentValue);
                    }

                    if(params.showMonth){
                        //check min and max
                        var current = new Date(values[0]+'-'+values[1]+'-'+values[2]);
                        var valid = true;
                        if(params.min) {
                            var min = new Date(typeof params.min === "function" ? params.min() : params.min);

                            if(current < +min) {
                                picker.setValue(lastValidValues);
                                valid = false;
                            }
                        }
                        if(params.max) {
                            var max = new Date(typeof params.max === "function" ? params.max() : params.max);
                            if(current > +max) {
                                picker.setValue(lastValidValues);
                                valid = false;
                            }
                        }
                    }

                    valid && (lastValidValues = values);

                    if (self.params.onChange) {
                        self.params.onChange.apply(this, arguments);
                    }
                },

                formatValue: function (p, values, displayValues) {
                    return self.params.format(p, values, displayValues);
                },

                cols: [
                    {
                        values: this.initYears
                    },
                    {
                        divider: true,  // 这是一个分隔符
                        content: params.yearSplit
                    },
                    {
                        values: this.initMonthes
                    },
                    {
                        divider: true,  // 这是一个分隔符
                        content: params.monthSplit
                    },
                    {
                        values: (function () {
                            var dates = [];
                            for (var i=1; i<=31; i++) dates.push(formatNumber(i));
                            return dates;
                        })()
                    },
                ]
            };

            if(!params.showDay){
                config.cols.splice(4,1);
                config.cols.splice(3,1);

                config.value.splice(2,1);
            }

            if(!params.showMonth){
                config.cols.splice(2,1);
                config.cols.splice(1,1);

                config.value.splice(1,1);
            }

            if(!params.showYear){
                config.cols.splice(0,1);

                config.value.splice(0,1);
            }

            if (params.dateSplit) {
                config.cols.push({
                    divider: true,
                    content: params.dateSplit
                })
            }

            if(self.params.showTime){
                var times = self.params.times();
                if (times && times.length) {
                    config.cols = config.cols.concat(times);
                }
            }

            var inputValue = this.input.val();
            if(self.params.showTime){
                inputValue = formatNumber(today.getDate()) + "/" + formatNumber(today.getMonth()+1) + "/" + today.getFullYear() + " " + inputValue + ":00";
            }

            if(inputValue) config.value = params.parse(inputValue);
            if(this.params.value) {
                this.input.val(this.params.value);
                config.value = params.parse(this.params.value);
            }

            return config;
        }
    };

    $.fn.letuPickerDate = function(params) {
        params = $.extend({}, defaults, params);
        return this.each(function() {
            if(!this) return;
            var $this = $(this);
            var datetime = $this.data("datetime");
            if(!datetime) $this.data("datetime", new Datetime(this, params));
            return datetime;
        });
    };

    defaults = $.fn.letuPickerDate.prototype.defaults = {
        input: undefined, // 默认值
        min: undefined, // YYYY-MM-DD 最大最小值只比较年月日，不比较时分秒
        max: undefined,  // YYYY-MM-DD
        showYear:true,
        showMonth:true,
        showDay:true,
        showTime:false,
        yearSplit: '-',
        monthSplit: '-',
        dateSplit: '',  // 默认为空
        datetimeSplit: ' ',  // 日期和时间之间的分隔符，不可为空
        monthes: ('01 02 03 04 05 06 07 08 09 10 11 12').split(' '),
        years: (function () {
            var days = new Date();
            var maxYears = days.getFullYear() + 10;
            var arr = [];
            for (var i = 1950; i <= maxYears; i++) { arr.push(i); }
            return arr;
        })(),
        times: function () {
            return [  // 自定义的时间
                {
                    values: (function () {
                        var hours = [];
                        for (var i=0; i<24; i++) hours.push(formatNumber(i));
                        return hours;
                    })()
                },
                {
                    divider: true,  // 这是一个分隔符
                    content: ':'
                },
                {
                    values: (function () {
                        var minutes = [];
                        for (var i=0; i<60; i++) minutes.push(formatNumber(i));
                        return minutes;
                    })()
                }
            ];
        },
        format: function (p, values) { // 数组转换成字符串
            return p.cols.map(function (col) {
                return col.value || col.content;
            }).join('');
        },
        parse: function (str) {
            // 把字符串转换成数组，用来解析初始值
            // 如果你的定制的初始值格式无法被这个默认函数解析，请自定义这个函数。比如你的时间是 '子时' 那么默认情况这个'时'会被当做分隔符而导致错误，所以你需要自己定义parse函数
            // 默认兼容的分隔符
            var t = str.split(this.datetimeSplit);
            return t[0].split(/\D/).concat(t[1].split(/:|时|分|秒/)).filter(function (d) {
                return !!d;
            })
        }
    }

})(jQuery);