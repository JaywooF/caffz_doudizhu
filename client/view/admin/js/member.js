var lib={load:0,avatar:function(_this){layer.open({type:1,shade:false,title:false,content:'<img src="'+$(_this).attr("src")+'" style="max-width:300px;max-height:300px">'})},search:function(_path){var _keyword=$("input[name='keyword']").val().replace(/^\s+|\s+$/g,"");if(!_keyword.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/)){layer.alert("邮箱帐号不符合规范！",{icon:7})}else if(_keyword.length>32){layer.alert("邮箱帐号最长32位！",{icon:7})}else{location.href=_path+"index.php/admin/member/init/keyword:"+encodeURIComponent(_keyword)}},checkbox:function(_id,_this){if(isNaN(_id)){if($(_this).attr("checked")){$(_this).attr("checked",false);$("input[name='"+_id+"'][type='checkbox']").each(function(){$(this).is(":checked")&&$(this).click()})}else{$(_this).attr("checked",true);$("input[name='"+_id+"'][type='checkbox']").each(function(){$(this).is(":checked")||$(this).click()})}}else{if($(_this).attr("checked")){$(_this).attr("checked",false)}else{$(_this).attr("checked",true)}}},add:function(_path,_token){var _member_name=$("input[name='member_name']").val().replace(/^\s+|\s+$/g,"");var _member_pw=$("input[name='member_pw']").val().replace(/^\s+|\s+$/g,"");var _member_rpw=$("input[name='member_rpw']").val().replace(/^\s+|\s+$/g,"");if(!_member_name.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/)){layer.alert("邮箱帐号不符合规范！",{icon:7})}else if(_member_name.length>32){layer.alert("邮箱帐号最长32位！",{icon:7})}else if(/[^\x21-\x7e]|[&][#]?[a-zA-Z0-9]+[;]/.test(_member_pw)){layer.alert("设置密码不符合规范！",{icon:7})}else if(_member_pw.length<6||_member_pw.length>24){layer.alert("设置密码最短6位、最长24位！",{icon:7})}else if(_member_pw!=_member_rpw){layer.alert("两次输入的密码不一致！",{icon:7})}else{this.load=layer.load(1);$.post(_path+"index.php/admin/member/insert",{member_name:_member_name,member_rpw:_member_rpw,token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("新增成功...",{icon:16});setTimeout("location.href='"+_path+"index.php/admin/member'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else if(data==-2){layer.msg("邮箱帐号已存在！",{icon:5})}else{layer.msg(data,{time:0,btn:["确定"]})}})}},edit:function(_id,_path,_token){var _member_pw=$("input[name='member_pw']").val().replace(/^\s+|\s+$/g,"");var _member_nick=$("input[name='member_nick']").val().replace(/^\s+|\s+$/g,"");var _member_gem=$("input[name='member_gem']").val().replace(/^\s+|\s+$/g,"");var _member_pea=$("input[name='member_pea']").val().replace(/^\s+|\s+$/g,"");var _member_lock=$("select[name='member_lock']").val();if(/[^\x21-\x7e]|[&][#]?[a-zA-Z0-9]+[;]/.test(_member_pw)){layer.alert("重设密码不符合规范！",{icon:7})}else if(_member_pw.length>0&&_member_pw.length<6||_member_pw.length>24){layer.alert("重设密码最短6位、最长24位！",{icon:7})}else if(/[&][#]?[a-zA-Z0-9]+[;]/.test(_member_nick)){layer.alert("角色昵称不符合规范！",{icon:7})}else if(_member_nick.length<1||_member_nick.length>24){layer.alert("角色昵称最短1位、最长24位！",{icon:7})}else if(/[^\d]/.test(_member_gem)||_member_gem.match(/^[0][0-9]{1,7}$/)||_member_gem.length<1||_member_gem.length>8){layer.alert("蓝钻余额不符合规范！",{icon:7})}else if(/[^\d]/.test(_member_pea)||_member_pea.match(/^[0][0-9]{1,7}$/)||_member_pea.length<1||_member_pea.length>8){layer.alert("金豆余额不符合规范！",{icon:7})}else{this.load=layer.load(1);$.post(_path+"index.php/admin/member/update",{member_id:_id,member_avatar:$("#avatar").is(":checked")?1:0,member_pw:_member_pw,member_nick:_member_nick,member_gem:_member_gem,member_pea:_member_pea,member_lock:_member_lock,token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("修改成功...",{icon:16});setTimeout("location.href='"+_path+"index.php/admin/member'",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else{layer.msg(data,{time:0,btn:["确定"]})}})}},del:function(_confirm,_id,_path,_token){var _checkbox=[];$("input[name='"+_id+"'][type='checkbox']:checked").each(function(){_checkbox.push($(this).val())});if(_checkbox.length<1){layer.alert("请先勾选要删除的数据！",{icon:7})}else if(_confirm<1){layer.confirm("确定要删除所选的数据吗？",{icon:3,btn:["确定","取消"]},function(){lib.del(1,_id,_path,_token)})}else{this.load=layer.load(1);$.post(_path+"index.php/admin/member/delete",{checkbox:_checkbox,token:_token},function(data){layer.close(lib.load);if(data==1){layer.msg("删除数据成功...",{icon:16});setTimeout("location.reload()",1e3)}else if(data==-1){layer.msg("登录已超时！",{icon:4})}else{layer.msg(data,{time:0,btn:["确定"]})}})}}};