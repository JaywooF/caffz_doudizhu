var lib={load:0,fast:function(_house,_path,_token){this.load=layer.load(3);$.post(_path+"index.php/hall/fast",{house:_house,rid:0,token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("匹配成功，请稍等...",{icon:16});setTimeout("location.href='"+_path+"index.php/room'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else if(data==-2){layer.msg("没有匹配到空闲房间！",{icon:5})}else if(data==-3){layer.msg("金豆余额未满足准入条件！",{icon:5})}else if(data==-4){layer.msg("请勿重复匹配！",{icon:2})}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})},put:function(_path,_token){var _put_type=$(".layui-layer-tab select[name='put_type']").val();var _put_mode=$(".layui-layer-tab select[name='put_mode']").val();var _put_house=$(".layui-layer-tab select[name='put_house']").val();this.load=layer.load(3);$.post(_path+"index.php/hall/put",{put_type:_put_type,put_mode:_put_mode,put_house:_put_house,token:_token},function(data){layer.close(lib.load);var _text=_put_type<2?"蓝钻":"金豆";if(data==1){layer.msg("创建成功，请稍等...",{icon:16});setTimeout("location.href='"+_path+"index.php/room'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else if(data==-2){layer.msg("请勿重复创建！",{icon:2})}else if(data==-3){layer.msg(_text+"余额未满足准入条件！",{icon:5})}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})},get:function(_path,_token){var _get_type=$(".layui-layer-tab select[name='get_type']").val();var _get_rid=$(".layui-layer-tab input[name='get_rid']").val().replace(/^\s+|\s+$/g,"");if(!_get_rid.match(/^[1-9][0-9]{0,9}$/)){layer.alert("房间编号不符合规范！",{icon:7})}else{this.load=layer.load(3);$.post(_path+"index.php/hall/get",{get_type:_get_type,get_rid:_get_rid,token:_token},function(data){layer.close(lib.load);var _text=_get_type<2?"蓝钻":"金豆";if(data==1){layer.msg("加入成功，请稍等...",{icon:16});setTimeout("location.href='"+_path+"index.php/room'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else if(data==-2){layer.msg("请勿重复加入！",{icon:2})}else if(data==-3){layer.msg("编号无效或已满员！",{icon:5})}else if(data==-4){layer.msg(_text+"余额未满足准入条件！",{icon:5})}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})}},score:function(_path,_token){this.load=layer.load(3);$.post(_path+"index.php/hall/score",{mid:0,house:0,token:_token},function(_data){layer.close(lib.load);if(_data.state==1){lib.dialog("dialog_score","玩家信息");$(".layui-layer-page .dialog-win").next().text(_data.win);$(".layui-layer-page .dialog-lose").next().text(_data.lose)}else if(_data.state==-1){layer.msg("登录已超时！",{icon:4})}else if(_data.state==-2){layer.msg("玩家不存在！",{icon:2})}else{layer.msg(_data.state,{time:0,btn:["确定"]})}},"json").fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})},order:function(_type,_start,_path,_token){this.load=layer.load(3);$.post(_path+"index.php/hall/order",{type:_type,start:_start,token:_token},function(_data){layer.close(lib.load);if(_data.state==1){var _colspan=$(".layui-layer-page .col-md-6 tbody tr:last td").attr("colspan");$(".layui-layer-page .col-md-6 tbody tr").last().remove();if(_data.total<10){$(".layui-layer-page .col-md-6 tbody").append(_data.msg+'<tr><td colspan="'+_colspan+'" style="background-color:#fff;cursor:auto">没有了</td></tr>')}else{var _now=_start+10;var _onclick="lib.order('"+_type+"', "+_now+", '"+_path+"', '"+_token+"')";$(".layui-layer-page .col-md-6 tbody").append(_data.msg+'<tr onclick="'+_onclick+'"><td colspan="'+_colspan+'">加载更多</td></tr>')}}else if(_data.state==-1){layer.msg("登录已超时！",{icon:4})}else{layer.msg(_data.state,{time:0,btn:["确定"]})}},"json").fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})},buy:function(_id,_path,_token){var _buy_type=$(".layui-layer-tab #"+_id+" select[name='buy_type']").val();var _buy_secret=$(".layui-layer-tab #"+_id+" input[name='buy_secret']").val().replace(/^\s+|\s+$/g,"");if(!_buy_secret.match(/^[a-z0-9]{32}$/)){layer.alert("充值密钥不符合规范！",{icon:7})}else{this.load=layer.load(3);$.post(_path+"index.php/hall/buy",{buy_type:_buy_type,buy_secret:_buy_secret,token:_token},function(_data){layer.close(lib.load);var _type=_buy_type<2?["蓝钻",".hall-gem p"]:["金豆",".hall-pea p"];if(_data.state==1){$(_type[1]).text(_data.msg);$(".layui-layer-tab #"+_id+" input[name='buy_secret']").val(null);layer.msg(_type[0]+"充值成功！",{icon:1})}else if(_data.state==-1){layer.msg("登录已超时！",{icon:4})}else if(_data.state==-2){layer.msg("密钥无效或已使用！",{icon:5})}else{layer.msg(_data.state,{time:0,btn:["确定"]})}},"json").fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})}},pw:function(_path,_token){var _member_mpw=$(".layui-layer-tab input[name='member_mpw']").val().replace(/^\s+|\s+$/g,"");var _member_pw=$(".layui-layer-tab input[name='member_pw']").val().replace(/^\s+|\s+$/g,"");var _member_rpw=$(".layui-layer-tab input[name='member_rpw']").val().replace(/^\s+|\s+$/g,"");if(/[^\x21-\x7e]|[&][#]?[a-zA-Z0-9]+[;]/.test(_member_mpw)){layer.alert("当前密码不符合规范！",{icon:7})}else if(_member_mpw.length<6||_member_mpw.length>24){layer.alert("当前密码最短6位、最长24位！",{icon:7})}else if(/[^\x21-\x7e]|[&][#]?[a-zA-Z0-9]+[;]/.test(_member_pw)){layer.alert("重设密码不符合规范！",{icon:7})}else if(_member_pw.length<6||_member_pw.length>24){layer.alert("重设密码最短6位、最长24位！",{icon:7})}else if(_member_pw==_member_mpw){layer.alert("重设密码不能与当前密码相同！",{icon:7})}else if(_member_pw!=_member_rpw){layer.alert("两次输入的密码不一致！",{icon:7})}else{this.load=layer.load(3);$.post(_path+"index.php/hall/pw",{member_mpw:_member_mpw,member_rpw:_member_rpw,token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("修改成功，请稍等...",{icon:16});setTimeout("location.href='"+_path+"'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else if(data==-2){layer.msg("当前密码错误！",{icon:2})}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})}},info:function(_path,_token){var _member_nick=$(".layui-layer-tab input[name='member_nick']").val().replace(/^\s+|\s+$/g,"");if(/[&][#]?[a-zA-Z0-9]+[;]/.test(_member_nick)){layer.alert("游戏昵称不符合规范！",{icon:7})}else if(_member_nick.length<1||_member_nick.length>24){layer.alert("游戏昵称最短1位、最长24位！",{icon:7})}else if(_member_nick==$("#tab_info input[name='member_nick']").attr("value")){layer.alert("游戏昵称未发生变化！",{icon:7})}else{this.load=layer.load(3);$.post(_path+"index.php/hall/info",{member_nick:_member_nick,token:_token},function(data){layer.close(lib.load);if(data==1){$(".hall-nick").text(_member_nick);$("#tab_info input[name='member_nick']").attr("value",_member_nick);$("#dialog_score .dialog-nick").next().text(_member_nick);layer.msg("昵称更新成功！",{icon:1})}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})}},logout:function(_confirm,_path,_token){if(_confirm<1){layer.confirm("确定要退出登录吗？",{icon:3,btn:["确定","取消"]},function(){lib.logout(1,_path,_token)})}else{this.load=layer.load(3);$.post(_path+"index.php/hall/logout",{token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("退出成功，请稍等...",{icon:16});setTimeout("location.href='"+_path+"'",1e3)}else{layer.msg(data,{time:0,btn:["确定"]})}}).fail(function(){layer.close(lib.load);layer.msg("网络异常！",{shift:6})})}},progress:function(_evt){var _per=Math.round(_evt.loaded/_evt.total*100);$(".layui-layer-tab .tab-progress").text(_per+"%")},failed:function(){layer.close(lib.load);$(".layui-layer-tab .tab-progress").hide();$(".layui-layer-tab .tab-upload").removeAttr("style");layer.msg("网络异常！",{shift:6})},complete:function(_evt){layer.close(lib.load);$(".layui-layer-tab .tab-progress").hide();$(".layui-layer-tab .tab-upload").removeAttr("style");var _data=JSON.parse(_evt.target.responseText);if(isNaN(_data.state)){layer.msg(_data.state)}else if(_data.state==-1){layer.msg("登录已超时！")}else{var _img=_data.msg+"upload/avatar/"+_data.state+".png?"+Math.random();$(".hall-avatar img").attr("src",_img);$("#tab_info .tab-info img").attr("src",_img);$("#dialog_score .tab-info img").attr("src",_img);$(".layui-layer-tab .tab-info img").attr("src",_img);layer.msg("上传成功！")}},upload:function(_path,_token){var _file=$(".layui-layer-tab .tab-info input")[0].files[0];var _arr=[".png",".jpg",".jpeg",".gif"];var _ext=_file.name.substr(_file.name.lastIndexOf("."));if(_arr.indexOf(_ext)==-1){layer.msg("文件有误！")}else if(_file.size>1048576){layer.msg("文件过大！")}else{this.load=layer.load(3);$(".layui-layer-tab .tab-upload").hide();$(".layui-layer-tab .tab-progress").removeAttr("style");var _fd=new FormData();_fd.append("avatar",_file);_fd.append("token",_token);var _xhr=new XMLHttpRequest();_xhr.open("post",_path+"index.php/hall/upload");_xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");_xhr.onload=this.complete;_xhr.onerror=this.failed;_xhr.upload.onprogress=this.progress;_xhr.send(_fd)}},page:function(_id,_title,_type,_path,_token){layer.closeAll("page");layer.open({title:_title,type:1,anim:1,shadeClose:true,area:$(window).width()<768?["90%","90%"]:["650px","650px"],content:$("#"+_id).html()});if(_type=="buy"){$(".layui-layer-page .col-md-6 thead").html("<tr><th>积分类型</th><th>积分数量</th><th>充值密钥</th><th>充值时间</th></tr>");$(".layui-layer-page .col-md-6 tbody").html("<tr onclick=\"lib.order('"+_type+"', 0, '"+_path+"', '"+_token+'\')"><td colspan="4">加载更多</td></tr>')}else if(_type=="bill"){$(".layui-layer-page .col-md-6 thead").html("<tr><th>收支方式</th><th>积分类型</th><th>积分数量</th><th>房间编号</th><th>结算时间</th></tr>");$(".layui-layer-page .col-md-6 tbody").html("<tr onclick=\"lib.order('"+_type+"', 0, '"+_path+"', '"+_token+'\')"><td colspan="5">加载更多</td></tr>')}this.order(_type,0,_path,_token)},tab:function(_id,_title,_modify){layer.tab({anim:1,shadeClose:true,tab:[{title:_title[0],content:$("#"+_id[0]).html()},{title:_title[1],content:$("#"+_id[1]).html()}]});if(_modify[0]==1){$(".layui-layer-tab .layui-layer-title span").first().removeClass("layui-this");$(".layui-layer-tab .layui-layer-title span").last().addClass("layui-this");$(".layui-layer-tab .layui-layer-tabmain li").first().attr("style","display:none");$(".layui-layer-tab .layui-layer-tabmain li").last().attr("style","display:list-item")}if(_modify[1]==1){$(".layui-layer-tab .layui-layer-tabmain li:last form").attr("id","buy_last");$(".layui-layer-tab .layui-layer-tabmain li:last select[name='buy_type']").val(2)}else if(_modify[1]==2){$(".layui-layer-tab select[name='get_type']").val(2);$(".layui-layer-tab select[name='put_type']").val(2);$(".layui-layer-tab select[name='put_mode']").parent().hide();$(".layui-layer-tab select[name='put_house']").parent().removeAttr("style")}},dialog:function(_id,_title){layer.open({title:_title,type:1,anim:1,shadeClose:true,content:$("#"+_id).html()})},swap:function(_type){if(_type>0){$("#hall_house").fadeOut("fast",function(){$("#hall_home").fadeIn("slow")})}else{$("#hall_home").fadeOut("fast",function(){$("#hall_house").fadeIn("slow")})}}};