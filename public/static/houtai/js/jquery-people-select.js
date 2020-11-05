/**
 * Created by Administrator on 2017/6/10.
 */
var people_select = {
    isMult:2
    ,inputType:''
    ,cbName:''
    ,selected:''
    ,dataList:[]
    ,LetterBox : $('.people-select-win .letter')
    /**
     * 确定选择
     */
    ,selectOk:function () {
        var selectIds = [];
        var selectTexts = [];
        $('.people-select-win .member-item input:checked').each(function () {
            var _this = $(this);
            selectIds.push(_this.val());
            selectTexts.push(_this.attr('text'));
        });

        if(!window[people_select.cbName]){
            alert('错误，没有写回调方法  ' + people_select.cbName);
            return;
        }
        window[people_select.cbName]({id:selectIds.join(','),text:selectTexts.join(',')});
    }
    ,
    /**
     * 取消选择
     */
    cancelSelect:function () {
        $('.people-select-win .member-item input:checked').prop('checked',false);
    }
    ,
    /**
     * 搜索
     * @returns {boolean}
     */
    sousuo:function () {
        var text = $('.people-select-win .searchInput').val();
        if(!text){
            people_select.showAll();
            return false;
        }
        var odList = $('.people-select-win .department-list');
        $('.people-select-win .member-item').each(function () {
            var _this = $(this);
            var allEl = _this.find("p.ss_nickname");
            var containsNum = 0;

            //处理小块
            allEl.each(function () {
                var pthis = $(this);
                var label = pthis.parents('label.weui-check__label');
                if(pthis.html().indexOf(text) >= 0){
                    containsNum++;
                    label.show(0);
                }
                else{
                    label.hide(0);
                }
            });

            if(containsNum < 1){
                _this.hide(0);
                odList.find('li[odid="'+_this.attr('odid')+'"]').hide(0);
            }
            else{
                _this.show(0);
                odList.find('li[odid="'+_this.attr('odid')+'"]').show(0);
            }
        });
        return false;
    }
    ,
    showAll:function () {
        console.log('showAll');
        $('.people-select-win .member-item').show(0);
        $('.people-select-win .member-item label.weui-check__label').show(0);
        $('.people-select-win .department-list li').show(0);
    }
    ,
    /**
     * 给已选中打钩
     */
    checkedVal:function () {
        if(people_select.selected){
            var tmp = people_select.selected.split(',');
            for (var i = 0;i< tmp.length;i++){
                // $('.people-select-win .member-item input:checkbox[]')
                $('#s' + tmp[i]).prop('checked',true);
            }
        }
    }
    ,
    /**
     * 初始化右侧
     * @param jsonData
     */
    initDepartment:function (jsonData) {
        var template = $("#peopleSelectDepartmentList").html();
        var compiledTemplate = $.Template7.compile(template);
        var canshu = {
            userList:jsonData
            ,inputType: people_select.inputType
            ,isMult: people_select.isMult
        };
        var html = compiledTemplate(canshu);

        $('.people-select-win .department-list').html(html);
        setTimeout(function () {
            $('.people-select-win .department-list li').click(function () {
                var letter = $(this).attr('odid');
                people_select.LetterBox.html($(this).html()).fadeIn();
                if($('#od'+letter).length>0){
                    var LetterTop = $('#od'+letter).position().top;
                    $('html,body').animate({scrollTop: LetterTop-45+'px'}, 300);
                }
                setTimeout(function(){
                    people_select.LetterBox.fadeOut();
                },1000);
            });
        },1000);
    }
    ,
    /**
     * 初始化会员列表
     * @param jsonData
     */
    initMemberList:function (jsonData) {
        var template = $("#peopleSelectMemberList").html();
        var compiledTemplate = $.Template7.compile(template);
        var canshu = {
            userList:jsonData
            ,inputType: people_select.inputType
            ,isMult: people_select.isMult
        };
        var html = compiledTemplate(canshu);

        $('.people-select-win .member-list').html(html);

        setTimeout(function () {
            people_select.checkedVal();
        },1000);
    }
};
$(function () {
    $.getJSON(jquery_people_select_data_url,{},function (jsonData) {
        people_select.dataList = jsonData;
        people_select.initDepartment(jsonData);
    });

    $('.people-select-win .searchCancel').click(function () {

    })
});
/**
 * 打开人员选择框
 * @param _isMult 是否多选，1单选 2多选，默认多选
 * @param _cbName 回调名称
 * @param _selected 选中 1,2
 */
function showSelectPeople(_isMult,_cbName,_selected) {
    if(!_isMult){
        people_select.isMult = 2;
    }
    else{
        people_select.isMult = _isMult;
    }
    people_select.selected = _selected;
    people_select.inputType = (people_select.isMult == 1 ? 'radio':'checkbox');
    people_select.initMemberList(people_select.dataList);
    if(!_cbName){
        people_select.cbName = 'openPeopleSelectWinCallBack';
    }
    else{
        people_select.cbName = _cbName;
    }

    $('.people-select-win').fadeToggle(600);
    //防止显示下层的元素，建议隐藏
    $('.page-group').fadeToggle(600);
}
function closeSelectPeople() {
    $('.people-select-win').fadeToggle(600);
    //防止显示下层的元素，建议隐藏
    $('.page-group').fadeToggle(600);
}