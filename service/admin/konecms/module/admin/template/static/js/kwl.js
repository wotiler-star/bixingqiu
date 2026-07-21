$(document).ready(function () {
//更新页面中默认选中当前的目录
	$(".sortidselect").children("option").each(function(){
        var sortid = $(this).val();
        var sortid0=$(".sortid0").val();
       if(sortid == sortid0){  
             $(this).attr("selected","selected");  
       }  
    });  

	//添加文档唯一性：选择目录提示已添加并跳转至对应修改页面 
	$(".sortidselect").change(function(){
		 sortid=$(this).val(); 
				$.ajax({
					cache : "FALSE",
					type : "POST",
					url : "?c=i&a=ajax_check_ifonly",
					dataType : "json",
					data : {
						"sortid" : sortid
					},
					timeout : 15000, 
					success : function (json) {
						if (json.success == 0) {
							if(confirm("该目录下的文档具有唯一性,请勿重复上传！是否跳转至对应修改页面？")){
								window.location.href="?c=i&a=manage_mod&id="+json.id;
							}
							return true;
						}else {
							return false;
						}
					} //success
				}); //ajax
	});
	// 搜索文档时进行目录查找
	$(".rCon-sort [name='sortid']").click(function () {
		$sortid = $(this).attr("sortid");
		$(".sortidselect option").each(function () {
			$val = $(this).val();
			if ($val == $sortid) {
				$(this).attr("selected", "selectd");
			}
		});
		showDialog.hide();
	});

 

	//可疑文件排查
	$(".blgo").click(function () {
		$(this).html("重新检查");
		$('.bl').html('');
		$('.bl').css('background-image', "none");
		$(".bl-all").css("display", "none");
		$days = $(".days").val();
		if (isNaN($days)) {
			alert("天数必须为数字（可为小数）");
			$(".days").val(1);
		} else {
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : "ajax/ajax.php",
				dataType : "json",
				data : {
					"days" : $days,
					"mytb" : "none"
				},
				timeout : 15000,
				beforeSend : function () {
					$('.bl').css('background-image', "url(img/jz3.gif)");
				},
				success : function (json) {
					if (json.success == 0) {

						if (json.num != 0) {
							$('.bl').css('background-image', "none");
							$(".bl-all").css("display", "block");
							$(".blspan").attr("file", json.myfile);
							$(".bl").html(json.msg);
							alert("找到文件数：" + json.num);

						} else {
							$('.bl').css('background-image', "none");
							alert("没有找到任何可疑文件！");
							$('.bl').html('');
						}
						return true;
					} else {
						alert("读取文件失败！");
						return false;
					}
				} //success
			}); //ajax
		}
	});

	//删除可疑文件

	$(".blspan").click(function () {
		$file = $(this).attr("file");
		$file = $.trim($file);
		// alert($file);
		if (confirm("是否提交")) {
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : "ajax/ajax.php",
				dataType : "json",
				data : {
					"unsetfile" : $file,
					"mytb" : "none"
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("删除成功");
						location.reload(true);
						return true;
					} else {
						alert("删除失败！");
						return false;
					}
				} //success
			}); //ajax
		}

	});

	//编辑模板：选择模板文件

	$(".span span").click(function () {

		$filename = $(this).attr("filename"); //选择的模板文件
		$("[name='filename']").val($filename); //设置
		$.ajax({
			cache : "FALSE",
			type : "POST",
			url : "ajax/ajax.php",
			dataType : "json",
			data : {
				"filename" : $filename,
				"mytb" : "none"
			},
			timeout : 15000,
			success : function (json) {
				if (json.success == 0) {
					// alert(json.msg);
					$(".cviewcnt").val(json.msg);
					return true;
				} else {
					alert("读取文件失败！");
					return false;
				}
			} //success
		}); //ajax

	});

	//目录操作选择：父复选框只对打开的子div中的checkbox起作用
	$(".sortbox [type='checkbox']").click(function () {

		$sortbox = $(this).parents(".sortbox");
		$ifopen = $sortbox.find(".add").attr("ifopen");
		$subsortbox = $sortbox.find(".add").attr("name");
		if ($(this).is(":checked")) { //点击选择
			if ($ifopen == "open") {
				$("." + $subsortbox).find("[type='checkbox']").attr("checked", "checked");
			}
		} else { //取消选择
			if ($ifopen == "open") {
				$("." + $subsortbox).find("[type='checkbox']").removeAttr("checked");
			}
		}
	});

	//全选
	$(".pall").click(function () {
		$ifall = $(this).attr("ifall");
		if ($ifall == "all") {
			$(this).text("取消全选");
			$(this).attr("ifall", "notall");
			$(":checkbox").attr("checked", "checked");
		} else {
			$(this).text("全选");
			$(this).attr("ifall", "all");
			$(":checkbox").removeAttr("checked");
		}

	});

	$(".title").keyup(function () {
		var $title;
		$title = $(this).val();
		$(".webtitle").val($title);
	}); 
	//是否显示跳转链接开关值
	$("[name='ifredirect']").click(function () {
		if (true == $(this).attr("checked")) {
			$(".rtr").css("display", "block");
		} else {
			$(".rtr").css("display", "none");
		}
	});
	//高级参数设置标题颜色
	$(".pickcolor").click(function () {
		$(".tcolor-pick").css("display", "block");
	});
	$(".tcolor-pick td span").each(function () {
		$class = "#" + $(this).attr("class");
		$(this).css("backgroundColor", $class);
	});
	$(".tcolor-pick td span").click(function () {
		$(".tcolor-pick").css("display", "none");
		$class = "#" + $(this).attr("class");
		$(".tcolor").val($class);
	});

	//全选
	$(".checkall").click(function () {
		$(":checkbox").attr("checked", "checked");
	});
	//全不选
	$(".notcheckall").click(function () {
		$(":checkbox").removeAttr("checked");
	});
	
	//返回上一步操作
	$(".goback").click(function(){
		window.history.back(-1); 
	});
	//排序
	$(".orderid").click(function () {
		if (confirm("是否提交")) { 
			var val = "";
			var idArr = "";
			$("[name='orderid']").each(function () {
				val += $(this).val() + " ";
				idArr += $(this).attr("idArr") + " ";
			});

			faceto = $(".faceto").val();
			tourl="?c="+faceto+"&a=ajax_set_orderid"
			//alert(val+idArr+tourl);
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"orderidArr" : val,
					"idArr" : idArr
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("排序操作成功 ！ ");
						location.reload();
						return true;
					} else {
						alert("排序操作失败");
						return false;
					}
				} //success
			}); //ajax

		} //confirm
	});
 
	//设置为：显示
	$(".ifhidden").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifhidden']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val(); 
			tourl="?c="+faceto+"&a=ajax_set_ifhidden"
			myid = $(".myid").val(); 
			 
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("显示设置操作成功 ！ ");
						return true;
					} else {
						alert("显示设置操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});
	 
	//设置为：显示
	$(".ifso").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifso']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val(); 
			tourl="?c="+faceto+"&a=ajax_set_ifso"
			myid = $(".myid").val(); 
			 
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("检索设置操作成功 ！ ");
						return true;
					} else {
						alert("检索设置操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});
	
	//设置为：推荐
	$(".ifhot").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifhot']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val();
			tourl="?c="+faceto+"&a=ajax_set_ifhot"
			myid = $(".myid").val();
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("设置推荐操作成功 ！ ");
						return true;
					} else {
						alert("设置推荐操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});


	//设置为：有效
	$(".ifok").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifok']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val();
		    tourl="?c="+faceto+"&a=ajax_set_ifok"
			myid = $(".myid").val();
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("设置生效操作成功 ！ ");
						return true;
					} else {
						alert("设置生效操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});

	//设置为：发货
	$(".ifsend").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifsend']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val();
		    tourl="?c="+faceto+"&a=ajax_set_ifsend"
			myid = $(".myid").val(); 
		     
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("设置发货操作成功 ！ ");
						return true;
					} else {
						alert("设置发货操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});
	//设置为：上架
	$(".ifdown").click(function () {
		if (confirm("是否提交")) {
			var idArr = "";
			$("[name='ifdown']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val();
			tourl="?c="+faceto+"&a=ajax_set_ifdown"
			myid = $(".myid").val();
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr,
					"myid" : myid
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("上架设置操作成功 ！ ");
						return true;
					} else {
						alert("上架设置操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});

	//更新价格
	$(".price").click(function () {
		if (confirm("是否提交")) { 
			var val_price = "";
			var val_price_web = "";
			var val_price_new = "";
			var val_price_web_new = "";
			var idArr = "";
			$("[name='price']").each(function () {
				val_price += $(this).val() + " ";
				idArr += $(this).attr("idArr") + " ";
			});
			$("[name='price_web']").each(function () {
				val_price_web += $(this).val() + " "; 
			});
			$("[name='price_new']").each(function () {
				val_price_new += $(this).val() + " "; 
			});
			$("[name='price_web_new']").each(function () {
				val_price_web_new += $(this).val() + " "; 
			});
 
			faceto = $(".faceto").val();
			if(faceto=='p') tourl="?c=p&a=ajax_set_price"
				
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"priceArr" : val_price,
					"price_webArr" : val_price_web,
					"price_newArr" : val_price_new,
					"price_web_newArr" : val_price_web_new,
					"idArr" : idArr
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("更新价格操作成功 ！ ");
						location.reload();
						return true;
					} else {
						alert("更新价格操作失败");
						return false;
					}
				} //success
			}); //ajax

		} //confirm
	});
	
	

	//更改订单金额
	$(".d_money").click(function () {
		
			var val = ""; 
			var idArr = "";
			$("[name='moneyid']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
				val+=$(this).parents("tr").find(".mymoney").val() + " ";
			});
			if(val==""){
				alert("没有可供更新订单金额的项");
				return false;
			}

			faceto = $(".faceto").val();
			tourl="?c="+faceto+"&a=ajax_set_d_money";
			
			if (confirm("是否提交")) { 
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"moneyArr" : val,
					"idArr" : idArr
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						alert("订单金额更新成功 ！ ");
						location.reload();
						return true;
					} else {
						alert("订单金额更新失败");
						return false;
					}
				} //success
			}); //ajax

		} //confirm
	});
	
	
	
	//批量删除
	$(".del").click(function () {
			var idArr = "";
			$("[name='delid']:checked").each(function () {
				idArr += $(this).attr("idArr") + " ";
			});
			faceto = $(".faceto").val();
			if(faceto=="m") tourl="?c=p&a=ajax_set_del_m";
			else tourl="?c="+faceto+"&a=ajax_set_del" ;
   if(idArr!=""){
      if (confirm("确定删除？\n警告：删除后将无法恢复，请谨慎操作！")) {
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"idArr" : idArr
				},
				timeout : 15000,
				success : function (json) {
					if (json.success == 0) {
						//作用于目录删除操作                 
						if(typeof(json.undeleteNum)!="undefined"){
							msg="删除失败："+json.undeleteNum+" 个。";
							if(json.undeleteNum>0){
								msg=msg+"原因：该项存在子类。";
							}
							msg=msg+"\n成功删除："+json.deleteNum +" 个。";
							alert(msg);
						} 
						location.reload(true);
						return true;
					} else {
						alert("删除操作失败");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
   }else{
            	alert("没有可供删除的项。");
      }
	});

	//单项删除
	$(".sdel").click(function () {
		if (confirm("确定删除？\n警告：删除后将无法恢复，请谨慎操作！")) {
			id = $(this).attr("sdelid"); 
			faceto = $(this).attr("faceto"); 
			if(faceto=='i') tourl="?c=i&a=ajax_set_del_single"
			if(faceto=='p') tourl="?c=p&a=ajax_set_del_single"
			if(faceto=='pic') tourl="?c=pic&a=ajax_set_del_single"
			if(faceto=='catalog') tourl="?c=catalog&a=ajax_set_del_single"
			$.ajax({
				cache : "FALSE",
				type : "POST",
				url : tourl,
				dataType : "json",
				data : {
					"id" :  id, 
				},
				timeout : 15000,
				success : function (json) {
					alert(json);
					if (json.success == 0) {
						location.reload(true);
						return true;
					} else {
						alert("删除操作失败\n原因：该项存在子类。");
						return false;
					}
				} //success
			}); //ajax
		} //confirm
	});
	

 

});
