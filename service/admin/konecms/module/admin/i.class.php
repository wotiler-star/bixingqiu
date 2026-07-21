<?php
/**
 * i.class.php 文档添加、管理
 * @copyright konecms.com 2016-2020
 * last update date 2016年12月3日
 */
konecms::load_module_classes("admin_base");

class i extends admin_base
{

    function __construct()
    {
        parent::__construct();
        
        $this->conn_i = konecms::load_model_class("i"); 
        $this->conn_catalog = konecms::load_model_class("catalog");
        
         
    }

    /*
     * 添加文档
     */
    function generate()
    {
        
        //排序权重
        $orderArr = $this->conn_i->get_one("orderid", "", "orderid desc");
        $orderid=$orderArr["orderid"]+1;

        //目录属性
        $this->fieldnameArr=array();//目录属性字段集合
        //目录
        $mArr=array();
        $where_="ifm='1' and  fieldname='' and pi='i' and parentid='0'";
        $mArr_=$this->conn_catalog->select("cataid,sort ",$where_,"orderid asc");
        
        if($mArr_){
            $i=0;
            foreach($mArr_ as $a){
                $sort_=$a["sort"];
                $cataid_=$a["cataid"];
                $fieldname_='cataid';
                $mylist=parent::cataidCheckbox($this->conn_catalog,$cataid_,$fieldname_,'cata');
                $mArr[$i]["mylist"]=$mylist;
                $mArr[$i]["title"]=$sort_;
                $mArr[$i]["cataid"]=$cataid_;
                $mArr[$i]["fieldname"]=$fieldname_;
                $i++;
            }
            $this->fieldnameArr[]="cataid";
        }
        $cataArr=$mArr;
         
        //属性
        $mArr=array();
        $where_="ifm='0' and  fieldname!='' and pi='i'";
        $mArr_=$this->conn_catalog->select("cataid,sort,fieldname",$where_,"orderid asc");
        if($mArr_){
            $i=0;
            foreach($mArr_ as $a){
                $sort_=$a["sort"];
                $cataid_=$a["cataid"];
                $fieldname_=$a["fieldname"];
                $this->fieldnameArr[]=$fieldname_;
                $mylist=parent::cataidCheckbox($this->conn_catalog,$cataid_,$fieldname_);
                $mArr[$i]["mylist"]=$mylist;
                $mArr[$i]["title"]=$sort_;
                $i++;
            }
        }
        $shuxArr=$mArr;
        //目录属性结束
        
        if (isset($_POST["cnt"])) {
            
            // 1. 获取开关值
            $_POST["data"]["ifindex"] = isset($_POST["data"]["ifindex"]) ? 0 : 1;
            $_POST["data"]["ifhot"] = isset($_POST["data"]["ifhot"]) ? 0 : 1;
            $_POST["data"]["ifhead"] = isset($_POST["data"]["ifhead"]) ? 0 : 1;
            $_POST["data"]["ifone"] = isset($_POST["data"]["ifone"]) ? 0 : 1;
            $_POST["data"]["ifgun"] = isset($_POST["data"]["ifgun"]) ? 0 : 1;
            $_POST["data"]["ifbold"] = isset($_POST["data"]["ifbold"]) ? 0 : 1;
            $_POST["data"]["ifonly"] = isset($_POST["data"]["ifonly"]) ? 0 : 1;
            $_POST["data"]["ifhidden"] = isset($_POST["data"]["ifhidden"]) ? 0 : 1;
            
            // 2.SEO默认为文档标题: 
            $title= $_POST["data"]["title"];
            $_POST["data"]["webtitle"]=$_POST["data"]["webtitle"]?$_POST["data"]["webtitle"]:$title;
            $_POST["data"]["keywords"]=$_POST["data"]["keywords"]?$_POST["data"]["keywords"]:$title;
             
            // 3. 内容与正文
            //简要
            $cnt_short = trim($_POST["cnt_short"]);
            $cnt_short_phone = trim($_POST["cnt_short_phone"]);
            //正文
            $cnt = trim($_POST["cnt"]);
            $cnt_phone = trim($_POST["cnt_phone"]);
            
            if ($cnt == "") $cnt = "正文"; 
            if($cnt_phone=="") $cnt_phone=$cnt;
            
            $cnt2 = strip_tags($cnt);
            if ($cnt_short == "" && $cnt2 != "") {
                $cnt_short= cutStr($cnt2, 200);
            }
            if($cnt_short_phone==""){
                $cnt_short_phone=$cnt_short;
            }

            $_POST["data"]["cnt"] = $cnt;
            $_POST["data"]["riqi"] = date("Y-m-d H:i:s");
            $_POST["data"]["cnt_short"] = $cnt_short;
            
            $_POST["data"]["cnt_phone"] = $cnt_phone;
            $_POST["data"]["cnt_short_phone"] = $cnt_short_phone;
            $_POST["data"]["short"]=$_POST["data"]["short"]?$_POST["data"]["short"]:strip_tags(trim($cnt_short));
            
            
            // 3.上传者为当前登录账号
            $_POST["data"]["uploader"] = $_SESSION["ADMINNAME"];
            
            // 4. 目录属性值 
            if($this->fieldnameArr){
                foreach($this->fieldnameArr as $a){
                    $fieldname_=$a;
                    $v_="";
                    foreach($_POST[$fieldname_] as $v){
                        $v_ .=$v.",";
                    }
                    $_POST["data"][$fieldname_]=rtrim($v_,",");
                }
            }
            
            // 5 自定义重复上传次数
                $unum=1;
                if(is_numeric($_POST["unum"])&&$_POST["unum"]>=1){
                    $_SESSION["UNUM_I"]=$unum=$_POST["unum"];
                }
               for($i=1;$i<=$unum;$i++){ 
                 $this->conn_i->insert($_POST["data"]);
               }
           $this->conn_i->insert_id()?showmessage(L("do_ok"), "?m=admin&c=i&a=generate") : showmessage(L("do_fail"), "?m=admin&c=i&a=generate");
        }
        include parent::load_tpl("i_generate");
    }

    /*
     * 文档管理（列表页） 
     */
    function manage()
    {
       
        // 1. 初始化
        $where = "1=1";  
        $cols="";

        //2 .目录属性
        $this->fieldnameArr=array();//目录属性字段集合
        //目录 
        // 获取目录
        $where_ = "pi='i' and ifm='1'";
        $data_ = $this->conn_catalog->select("*", $where_, "cataid asc");
        $cataSelect = parent::select_catalog($data_);
        //属性
        $mArr=array();
        $where_="ifm='0' and  fieldname!='' and pi='i'";
        $mArr_=$this->conn_catalog->select("cataid,sort,fieldname",$where_,"orderid asc");
        if($mArr_){
            $i=0;
            foreach($mArr_ as $a){
                $sort_=$a["sort"];
                $cataid_=$a["cataid"];
                $fieldname_=$a["fieldname"];
        
                $cols.="$fieldname_".","; 
                $mylist=parent::cataidSelect($this->conn_catalog,$cataid_,$fieldname_);
                 
                $mArr[$i]["mylist"]=$mylist;
                $mArr[$i]["title"]=$sort_;
                $mArr[$i]["cataid"]=$cataid_;
                $mArr[$i]["fieldname"]=$fieldname_;
                $i++;
            }
        }
        $shuxArr=$mArr;
         
        //目录属性结束
        

        // 3. 形成条件语句、a链接、page链接
        $c="i";
        $a="manage";
        $wFieldArr=array("title");
           $riqi_field="riqi";
        $conn=$this->conn_catalog;
        $dataListArr=dataList($c,$a,$wFieldArr,$riqi_field,$conn);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
         $where=$where.$mywhere;
       
        
        // 4. 加载数据
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        $orderby="orderid desc";
        $cols.= "id, cataid,title,ifindex,ifhead,ifchecked,ifonly,ifhot,ifhidden,ifgun,ifbold,ifone,hitnum,picdir_index,picdir_list,redirect,dataurl,tcolor,uploader,orderid,riqi";
        $listArr = $this->conn_i->i($cols, $where, $orderby, "", 15, "","",$percount);
       
            $i=0; 
            foreach($listArr as $b){
                //目录
                 $j=0;
                $mymArr=array();
                if(isset($b["cataid"])){

                    $tmpArr_=explode(",",$b["cataid"]);
                    if($tmpArr_){
                        $myv_="";
                        foreach($tmpArr_ as $c){
                             
                            if($c){
                                $mytmp_=str_replace("cataid", "", $c);
                                if($mytmp_){
                                    $where_="cataid=$mytmp_";
                                    $data_=$this->conn_catalog->get_one("sort",$where_);
                                    $myv_.=$data_["sort"]." ";
                                }
                                
                            }
                        }
                        $mymArr[$j]["mysort"]="所属目录";
                        $mymArr[$j]["myv"]=$myv_;
                        $j++;
                    }
                }
                //属性
                if($shuxArr){
                 
                foreach($shuxArr as $a){
                    $sort_=$a["title"];
                    $fieldname_=$a["fieldname"];
                     $tmp_=trim($b[$fieldname_]);
                    $tmpArr_=explode(",",$tmp_);
        
                    if($tmpArr_){
                        $myv_="";
                        foreach($tmpArr_ as $c){
                            if($c){
                                $mytmp_=str_replace($fieldname_, "", $c);
                                $where_="cataid=$mytmp_";
                                $data_=$this->conn_catalog->get_one("sort",$where_);
                                $myv_.=$data_["sort"]." ";
                            }
                        }
                        $mymArr[$j]["mysort"]=$sort_;
                        $mymArr[$j]["myv"]=$myv_;
                    }
                    $j++;
        
                }//foreach mArr 
                
                $listArr[$i]["mymArr"]=$mymArr;
                $i++; 
            }//ifshuxArr
            
            
        }//foreach listArr
        // 5. 当前搜索条件结果展示
         
        $pi="i";
        $conn=$this->conn_catalog;
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
         
        // 6. 分页及统计
        $amount = $this->conn_i->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
        
        // 7. 加载模板
        include parent::load_tpl("i_manage");
    }

  

        
    /*
     * 修改文档
     */
    function manage_mod()
    {
        
        // 开启对话框
        $ifDialog = true;
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?m=admin&c=i&a=manage");
        $where = "id=" . $_GET["id"];
        $data = $this->conn_i->get_one("*", $where);
         //目录属性
        $this->fieldnameArr=array();//目录属性字段集合
        //目录
        $mArr=array();
        $where_="ifm='1' and  fieldname='' and pi='i' and parentid='0'";
        $mArr_=$this->conn_catalog->select("cataid,sort ",$where_,"orderid asc");
        
        if($mArr_){
            $i=0;
            foreach($mArr_ as $a){
                $sort_=$a["sort"];
                $cataid_=$a["cataid"];
                $fieldname_='cataid';
                $mylist=parent::cataidCheckbox($this->conn_catalog,$cataid_,$fieldname_,'cata',$data);
                $mArr[$i]["mylist"]=$mylist;
                $mArr[$i]["title"]=$sort_;
                $mArr[$i]["cataid"]=$cataid_;
                $mArr[$i]["fieldname"]=$fieldname_;
                $i++;
            }
            $this->fieldnameArr[]="cataid";
        }
        $cataArr=$mArr;
         
        //属性
        $mArr=array();
        $where_="ifm='0' and  fieldname!='' and pi='i'";
        $mArr_=$this->conn_catalog->select("cataid,sort,fieldname",$where_,"orderid asc");
        if($mArr_){
            $i=0;
            foreach($mArr_ as $a){
                $sort_=$a["sort"];
                $cataid_=$a["cataid"];
                $fieldname_=$a["fieldname"];
                $this->fieldnameArr[]=$fieldname_;
                $mylist=parent::cataidCheckbox($this->conn_catalog,$cataid_,$fieldname_,'shux',$data);
                $mArr[$i]["mylist"]=$mylist;
                $mArr[$i]["title"]=$sort_;
                $i++;
            }
        }
        $shuxArr=$mArr;
        //目录属性结束
        
        if (isset($_POST["data"])) {
             
            // 1. 获取开关值
            $_POST["data"]["ifindex"] = isset($_POST["data"]["ifindex"]) ? 0 : 1;
            $_POST["data"]["ifhot"] = isset($_POST["data"]["ifhot"]) ? 0 : 1;
            $_POST["data"]["ifhead"] = isset($_POST["data"]["ifhead"]) ? 0 : 1;
            $_POST["data"]["ifone"] = isset($_POST["data"]["ifone"]) ? 0 : 1;
            $_POST["data"]["ifgun"] = isset($_POST["data"]["ifgun"]) ? 0 : 1;
            $_POST["data"]["ifbold"] = isset($_POST["data"]["ifbold"]) ? 0 : 1;
            $_POST["data"]["ifonly"] = isset($_POST["data"]["ifonly"]) ? 0 : 1;
            $_POST["data"]["ifhidden"] = isset($_POST["data"]["ifhidden"]) ? 0 : 1;
            
            
            // 2.SEO默认为文档标题: 
            $title= $_POST["data"]["title"];
            $_POST["data"]["webtitle"]=$_POST["data"]["webtitle"]?$_POST["data"]["webtitle"]:$title;
            $_POST["data"]["keywords"]=$_POST["data"]["keywords"]?$_POST["data"]["keywords"]:$title;
             
            // 3. 内容与正文
            //简要
            $cnt_short = trim($_POST["cnt_short"]);
            $cnt_short_phone = trim($_POST["cnt_short_phone"]);
            //正文
            $cnt = trim($_POST["cnt"]);
            $cnt_phone = trim($_POST["cnt_phone"]);
            
            if ($cnt == "") $cnt = "正文"; 
            if($cnt_phone=="") $cnt_phone=$cnt;
            
            $cnt2 = strip_tags($cnt);
            if ($cnt_short == "" && $cnt2 != "") {
                $cnt_short= cutStr($cnt2, 200);
            }
            if($cnt_short_phone==""){
                $cnt_short_phone=$cnt_short;
            }

            $_POST["data"]["cnt"] = $cnt;
            $_POST["data"]["cnt_short"] = $cnt_short;
            
            $_POST["data"]["cnt_phone"] = $cnt_phone;
            $_POST["data"]["cnt_short_phone"] = $cnt_short_phone;
            $_POST["data"]["short"]=$_POST["data"]["short"]?$_POST["data"]["short"]:strip_tags(trim($cnt_short));
             

            // 4. 目录属性值
            //处理属性
            if($this->fieldnameArr){
                foreach($this->fieldnameArr as $a){
                    $fieldname_=$a;
                    $v_="";
                    foreach($_POST[$fieldname_] as $v){
                        $v_ .=$v.",";
                    }
                    $_POST["data"][$fieldname_]=rtrim($v_,",");
                }
            }
            
             
            $this->conn_i->update($_POST["data"], $where);
            
           
            // 5. 原路返回
            $url=modReturnURL();
              
            $this->conn_i->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url");
        }
        
        include parent::load_tpl("i_manage_mod");
    }
   
    /*
     * 异步检查目录名称是否可用
     */
    function ajax_check_sort()
    {
        // 修改目录时如果目录名称没有变化则正常返回
        if (isset($_GET["cataid"])) {
            $where = "cataid=" . intval($_GET["cataid"]);
            $rs = $this->db->get_one("sort", $where);
            $rs["sort"] == iconv("utf-8", "gbk", $_GET["sort"]) ? exit("1") : "";
        }
        $where = "sort='" . iconv("utf-8", "gbk", $_GET["sort"]) . "' and parentid=" . intval($_GET["parentid"]) . " and mid=" . intval($_GET["mid"]);
        
        $rs = $this->db->get_one("*", $where);
        $str = var_export($rs, true);
        
        $rs ? exit("0") : exit("1");
    }

    /*
     * 异步检查文档唯一性
     */
    function ajax_check_ifonly()
    {
        if (isset($_POST["sortid"])) {
            $sortid = $_POST["sortid"];
            $where = "sortid='$sortid'";
            $row = $this->db->get_one("id,ifonly", $where);
            if ($row) {
                $ifonly = $row["ifonly"];
                if ($ifonly == '0') {
                    $id = $row["id"];
                    $arr['id'] = $id;
                    $arr['success'] = 0;
                } else {
                    $arr['success'] = 1;
                }
            } else {
                $arr['success'] = 1;
            }
            echo json_encode($arr);
        }
    }

 

    /*
     * 异步设置：推荐
     */
   
    function ajax_set_ifhot()
    {
        $arr = array(
            "ifhot" => $_POST["ifhot"]
        );
        $where = "id=" . $_POST["id"];  
                $this->conn_i->update($arr, $where);
                
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步设置：
     */
     
    function ajax_set_ifchecked()
    {
        $arr = array(
            "ifchecked" => $_POST["ifchecked"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_i->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    

    /*
     * 异步设置：推荐
     */
     
    function ajax_set_ifhead()
    {
        $arr = array(
            "ifhead" => $_POST["ifhead"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_i->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步设置：推荐
     */
     
    function ajax_set_ifone()
    {
        $arr = array(
            "ifone" => $_POST["ifone"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_i->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }

    /*
     * 异步设置：首页
     */
    function ajax_set_ifindex()
    {
        $arr = array(
            "ifindex" => $_POST["ifindex"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_i->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    /*
     * 异步设置：显示
     */
     
    function ajax_set_ifhidden()
    {
        $_POST["ifhidden"]=$_POST["ifhidden"]=='0'?'1':'0';
        $arr = array(
            "ifhidden" => $_POST["ifhidden"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_i->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
 
    /*
     * 异步设置：批量删除
     */
    function ajax_dels()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids); 
        foreach ($idsArr as $id) {
            $where = "id=$id"; 
                    $this->conn_i->delete($where);
                    
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步：单个删除
     */
    function ajax_del()
    {
        $id = $_POST["id"];
        $where = "id=$id"; 
        $this->conn_i->delete($where); 
        $arr['success'] = 0;
        echo json_encode($arr);
    }

    
    /*
     * 异步：删除单个搜索条件
     */
    function ajax_del_so()
    {
        $k = $_POST["k"];
        $pageurl = $_POST["pageurl"];
    
        $tmp_=explode("&",$pageurl);
        if($tmp_){
            $url_="";
            foreach($tmp_ as $a){
                $arr_=explode("=",$a);
                $k_=$arr_[0];
                $v_=isset($arr_[1])?$arr_[1]:'';
                if($k_!=$k){
                    $url_.=$k_."=".$v_."&";
                }
            }
            $url=rtrim($url_,"&");
        }
    
        $arr['url'] = $url_;
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    

    /*
     * 异步设置：排序
     *
     */
    function ajax_orderid()
    {
        $ids = rtrim($_POST["ids"],",");
        $orderid = rtrim($_POST["orderid"],",");
        $idArr = explode(",", $ids);
        $orderidArr = explode(",", $orderid);
    
        $i = 0;
        foreach ($orderidArr as $a) {
            $post["orderid"] = $a;
            $id = $idArr[$i];
            $where = "id=$id";
            $this->conn_i->update($post, $where);
            $i ++;
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
}//class





