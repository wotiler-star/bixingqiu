$(document).ready(function(){
  	var  hintArr=new Array("ok :)","需要","至少6个字符","格式不准确","字符个数不准确");
	var telreg = /^13[0-9]{9}$|14[0-9]{9}$|15[0-9]{9}$|18[0-9]{9}$/;
	var reg =/^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
	//reg.test($email);
////////////////////////////////////////////////////////////////////////////登录//////////////////////////////////////////////////////////    
 $(".loginfrm .clickme").click(function(){
      hname=$(".loginfrm [name='hname']").val();
      pwd=$(".loginfrm [name='pwd']").val();
      yzm=$.trim($(".loginfrm [name='yzm']").val());   
	  dlist="";  
	  cataid="";  
	  pid=""; 
	  v="";
	 
	  
	  if($(".loginfrm [type='hidden']").hasClass("dlist")){
		  dlist="1";
		  v=$(".dlist").val();
	  } 
	  if($(".loginfrm [type='hidden']").hasClass("cataid")){
		  cataid="1";
		  mycataid=$(".cataid").val();
	  } 
	  if($(".loginfrm [type='hidden']").hasClass("pid")){
		  pid="1";
		  myid=$(".pid").val();
	  } 
	  
       if(yzm=="") {
		   alert("请输入图形验证码！");
		   return false;
		} 
		
	  
	  if(hname!=""&&pwd!=""){
	  data={"hname":hname,"pwd":pwd,"yzm":yzm}; 
	  $.ajax({
	      type:"post",
		  dataType:"json",
		  url:"?c=h&a=ajax_login",
		  data:{"data":data},
		  success:function(json){
			  
		      if(json.success=='0'){
			     
				if(dlist!=""){
				    window.location.href="?c=Content&cataid="+v;
					return true;
				}  
				if(cataid!=""&&pid!=""){
				    window.location.href="?c=Content&cataid="+mycataid+"&id="+myid;
					return true;
				}  
				    window.location.href="?c=h";
				 
			    
			  }else{
				   if(json.success=='1'){
			          alert("密码错误 ！");
				   }else if(json.success=='2'){
			          alert("账号错误 ！");
				   }else{
					   alert("图形验证码错误 ！");
				   }
			  }
		  }
	  });
	  
	  }else{
		  alert("账号或密码不能为空 ！");
	  }
   });
   
////////////////////////////////////////////////////////////////////////////注册与密码找回//////////////////////////////////////////////////////////
	 //两次密码输入检查是否一致
      $(".rsgfrm [name='pwd2']").blur(function(){
            var $pwd2=$(this).val();
            var $pwd1=$(".rsgfrm  [name='pwd']").val();
			 
            if ($pwd1!=$pwd2) {  
				 alert("两次密码输入不一样，请重试！");
			} 
      });
	  
  //会员注册   
  $(".rsgfrm .clickme").click(function(e){
	    e.preventDefault();
		
       var hname=$.trim($(".rsgfrm [name='hname']").val());  
       var pwd= $(".rsgfrm [name='pwd']").val(); 
       var pwd2= $(".rsgfrm [name='pwd2']").val();
       var yzm=$.trim($(".rsgfrm [name='yzm']").val());   
       
		
	   if($(".rsgfrm .callpwd").length){
			 data={"hname":hname,"pwd":pwd,"msg":msg,"yzm":yzm};
			 ajax_url="?c=h&a=ajax_callpwd";
			 msg="成功找回密码，请登录";
		}else{ 
			 data={"hname":hname,"pwd":pwd,"yzm":yzm };
			 ajax_url="?c=h&a=ajax_rsg";
			 msg="注册成功，请登录"; 
	    
		}
       
	    if(   
			 isNaN(hname) ||hname.length!=11
	   ){
		   alert("请正确输入手机号！");
		   return false;
		}  
       if(yzm=="") {
		   alert("请输入图形验证码！");
		   return false;
		} 
		
       if(pwd.length<6) {
		   alert("密码必须大于6位数 ！");
		   return false;
		}
       if(pwd!=pwd2) {
		   alert("两次密码输入不一样！");
		   return false;
		}
		 
			$.ajax({
        cache:"FALSE",
        type:"POST",
         url:ajax_url,
     dataType:"json",
        data:{ 
		     "data":data
				},
	 timeout:15000,
     success:function(json){
             if(json.success==0){
				 alert(msg); 
			     window.location.href="?c=h&a=login";
				 return true;
             }else{
				 alert(json.msg);
				 return false;
			 }
        }//success
      });//ajax
  });

  //修改密码
  $(".pwd2frm .clickme").click(function(e){

	    e.preventDefault();
       var hname=$.trim($(".pwd2frm [name='hname']").val()); 
       var msg=$.trim($(".pwd2frm [name='msg']").val());  
       var pwd= $(".pwd2frm [name='pwd']").val(); 
       var pwd2= $(".pwd2frm [name='pwd2']").val();
	    if(   
			 isNaN(hname) ||hname.length!=11
	   ){
		   alert("请正确输入手机号！");
		   return false;
		} 
		
       if(pwd.length<6) {
		   alert("密码必须大于6位数 ！");
		   return false;
		}
       if(pwd!=pwd2) {
		   alert("两次密码输入不一样！");
		   return false;
		}

       if(msg=="") {
		   alert("请输入手机短信验证码！");
		   return false;
		} 
		   
		data={"hname":hname,"pwd":pwd,"msg":msg};
		
			$.ajax({
        cache:"FALSE",
        type:"POST",
         url:"?c=h&a=ajax_pwd2",
     dataType:"json",
        data:{ 
		     "data":data
				},
	 timeout:15000,
     success:function(json){
             if(json.success==0){
				 alert("修改密码成功 ！"); 
			     window.location.href="?c=h&a=home";
				 return true;
             }else{
				 alert(json.msg);
				 return false;
			 }
        }//success
      });//ajax  
  });
   
   //发送验证码倒计时
var InterValObj; //timer变量，控制时间
var count = 60; //间隔函数，1秒执行
var curCount;//当前剩余秒数

function sendMessage() {
  　curCount = count;
　　//设置button效果，开始计时
     $("#btnSendCode").attr("disabled", "true");
     $("#btnSendCode").text(curCount + "秒内输入");
     InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
　　  //向后台发送处理数据
    
}

//timer处理函数
function SetRemainTime() {
            if (curCount == 0) {                
                window.clearInterval(InterValObj);//停止计时器
                $("#btnSendCode").removeAttr("disabled");//启用按钮
                $("#btnSendCode").text("重新发送验证码");
				$("#piccode").attr("src","yzm.php?"+new Date().getTime());
            }
            else {
                curCount--;
                $("#btnSendCode").text( curCount + "秒内输入");
            }
        }
		
	   
//发送短信验证码
  $(".msgclickme").click(function(e){  
	    e.preventDefault();
       var phone=$.trim($(this).parents("form").find("[name='hname']").val());  
       var yzm=$.trim($(this).parents("form").find("[name='yzm']").val());  
       
       if(   
			 isNaN(phone) ||phone.length!=11
	   )
	   {
		   alert("请准确填写您的手机号！");
		   return false;
		} 
		 
       if(yzm==""||yzm=="请输入图形验证码..."){
			 alert("请填写图形验证码！");
			 return false;
	   }	 
	   if($(".rsgfrm .callpwd").length){
		   
			 data={"phone":phone,"yzm":yzm,"callpwd":0};
			 
		}else{
			 data={"phone":phone,"yzm":yzm};
		}
         
			$.ajax({
        cache:"FALSE",
        type:"POST",
         url:"?c=h&a=ajax_sendMessage",
     dataType:"json",
        data:data,
	 timeout:15000, 
     success:function(json){
             if(json.success==0){
				  alert("手机验证信息已发送,请注意查收！ ");
				sendMessage();
				 return true;
             }else{
				 alert(json.msg);
				// $("#piccode").attr("src","yzm.php?"+new Date().getTime());
				 return false;
			 }
			  } 
			  });//ajax  */
	  
  });
  ///保存修改性别
  $(".clickme_mysex").click(function(){
	  $(".sexfrm").submit();
  });
  ///保存银行卡
  $(".clickme_bankfrm").click(function(){
	  $(".bankfrm").submit();
  });
  ///保存我的昵称
  $(".clickme_mynamefrm").click(function(){
	  $(".mynamefrm").submit();
  });
  
  //////////////////////////////////////////////////////////////顶部搜索/////////////////////////////////////////////////////////
  $(".wfrm .clickme").click(function(){
	  
	  var w=$(".wfrm [name='w']").val();
	  if(w!=""){
		  $(".wfrm").submit();
	  }
  });
  
  });
	
	 