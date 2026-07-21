<?php
/**
 * i.class.php 文档添加、管理
 * @copyright konecms.com 2016-2020
 * last update date 2016年12月3日
 */
konecms::load_module_classes("admin_base");

class catalog extends admin_base
{

    function __construct()
    {
        parent::__construct();
        
        $this->conn_i = konecms::load_model_class("i");
      
        $this->conn_catalog = konecms::load_model_class("catalog");
        
        // 获取目录
        $where = "";
        $data = $this->conn_catalog->select("*", $where, "cataid asc");
        $this->select_catalog = parent::select_catalog($data);
    }

    /*
     * 添加目录
     */
    function generate()
    {
        // 获取目录
         $select_catalog = $this->select_catalog;
        // 排序ID
        $arr_ = $this->conn_catalog->get_one("orderid", "1=1", "orderid desc");
        if ($arr_) {
            $orderid = $arr_["orderid"] + 1;
        } else {
            $orderid = 1;
        }
        // 开启对话框
        $ifDialog = true;
        if (isset($_POST["data"])) {
            //目录名称唯一性
            $parentid=$_POST["data"]["parentid"]; 
            $sort = trim($_POST["data"]["sort"]);
            $where_ = "sort='$sort'  and parentid=$parentid"; 
            $rs = $this->conn_catalog->get_one("*", $where_);
            if($rs){
                 showmessage("上传失败！目录名称已经存在", "?c=catalog&a=generate") ; 
                 exit();
            }
            //字段唯一性检查
            $_POST["data"]["ifm"] = $_POST["data"]["ifm"] ? '0' : '1';
            $newField = strtolower(trim($_POST["data"]["fieldname"]));
            $mytb = $_POST["data"]["pi"] == "i" ? "i_tb" : "p_tb";
            $this->conn_tb = $this->conn_i;
            if (! $_POST["data"]["ifm"]&&$this->conn_tb->field_exists($newField)) {  
                    showmessage("上传失败！该字段已经存在", "?c=catalog&a=generate") ;
                    exit();  
            }
            $_POST["data"]["fieldname"]=$newField;
            $orderid = $_POST["data"]["orderid"] ? $_POST["data"]["orderid"] : $orderid;
            $_POST["data"]["ifnav"] = $_POST["data"]["ifnav"] ? '0' : '1';
            // 处理属性
            // 文档或商品表添加新字段：属性复选框选中；有字段名称； 
            if (! $_POST["data"]["ifm"] && $newField ) {
                if (! $this->conn_tb->field_exists($newField)) {
                    $sql = "alter table $mytb add $newField varchar(220) default ' '";
                    $this->conn_tb->query($sql);
                }
            }
            // 2. 批量上传
           
            if (strpos($sort, "\r\n")) {
                $ensort = $_POST["data"]["ensort"];
                $sort_mobile = $_POST["data"]["sort_mobile"];
                $sortArr = explode("\r\n", $sort);
                $ensortArr = explode("\r\n", $ensort);
                if (strpos($sort_mobile, "\r\n")) {
                    $sort_mobileArr = explode("\r\n", $sort_mobile);
                }
                // 模板继承父级
                $parentid = $_POST["data"]["parentid"];
                $tplnameDetail = $_POST["data"]["tplnamedetail"];
                if ($parentid && ! $tplnameDetail) {
                    $data = $this->conn_catalog->get_one("tplnamedetail", "cataid=$parentid");
                    $_POST["data"]["tplnamedetail"] = $data["tplnamedetail"];
                }
                
                $tplnameList = $_POST["data"]["tplnamelist"];
                if ($parentid && ! $tplnameList) {
                    $data = $this->conn_catalog->get_one("tplnamelist", "cataid=$parentid");
                    $_POST["data"]["tplnamelist"] = $data["tplnamelist"];
                }
                //英文名、手机名默认值设置
                
                for ($i = 0; $i < count($sortArr); $i ++) {
                    $sort_=$_POST["data"]["sort"] = trim($sortArr[$i]);
                    if (isset($ensortArr[$i]))
                        $_POST["data"]["ensort"] = $ensortArr[$i];
                    else
                        $_POST["data"]["ensort"] = "";
                    
                    if ($sort_mobileArr && isset($sort_mobileArr[$i])) {
                        $_POST["data"]["sort_mobile"] = $sort_mobileArr[$i];
                    } else {
                        $_POST["data"]["sort_mobile"] = $sort_;
                    }
                    
                    $_POST["data"]["orderid"] = $orderid ++;
                    $this->conn_catalog->insert($_POST["data"]);
                }
            } else {
                
                // 模板继承父级
                $parentid = $_POST["data"]["parentid"];
                $tplnameDetail = $_POST["data"]["tplnamedetail"];
                if ($parentid && ! $tplnameDetail) {
                    $data = $this->conn_catalog->get_one("tplnamedetail", "cataid=$parentid");
                    $_POST["data"]["tplnamedetail"] = $data["tplnamedetail"];
                }
                
                $tplnameList = $_POST["data"]["tplnamelist"];
                if ($parentid && ! $tplnameList) {
                    $data = $this->conn_catalog->get_one("tplnamelist", "cataid=$parentid");
                    $_POST["data"]["tplnamelist"] = $data["tplnamelist"];
                }
                
                $_POST["data"]["sort_mobile"] = $_POST["data"]["sort_mobile"] ? $_POST["data"]["sort_mobile"] : $_POST["data"]["sort"];
                $_POST["data"]["keywords"] = $_POST["data"]["keywords"] ? $_POST["data"]["keywords"] : $_POST["data"]["sort"];
                $_POST["data"]["webtitle"] = $_POST["data"]["webtitle"] ? $_POST["data"]["webtitle"] : $_POST["data"]["sort"];
                $_POST["data"]["short"] = $_POST["data"]["short"] ? $_POST["data"]["short"] : $_POST["data"]["sort"];
                $this->conn_catalog->insert($_POST["data"]);
            }
            
            $this->conn_catalog->insert_id() ? showmessage(L("do_ok"), "?c=catalog&a=generate") : showmessage(L("do_fail"), "?c=catalog&a=generate");
        }
        include parent::load_tpl("catalog_generate");
    }

    /*
     * 商品类型
     * 1. 展示信息
     * 2. 处理搜索
     */
    function manage()
    {
        $str = parent::listCatalog();
        include parent::load_tpl("catalog_manage");
    }

    /*
     * 修改目录
     */
    function manage_mod()
    {
        ! isset($_GET["cataid"]) && showmessage(L("错误的ID值"), "?c=i&a=manage_catalog");
        $where = "cataid=" . $_GET["cataid"];
        $data = $this->conn_catalog->get_one("*", $where);

        // 获取目录
        $select_catalog = $this->select_catalog;
        
        $cataid0 = $data["cataid"];
        $sort0 = $data["sort"];
        $parentid0 = $data["parentid"];
        $oldField = strtolower($data["fieldname"]);
        
        if (isset($_POST["data"])) {
            
            // 处理图片
            if (! $_POST["data"]["picdir"])
                unset($_POST["data"]["picdir"]);
            
            $_POST["data"]["ifnav"] = $_POST["data"]["ifnav"] ? '0' : '1';

            //目录名称唯一性
            $sort=$_POST["data"]["sort"];
            $parentid=$_POST["data"]["parentid"];
            if($sort0 != $sort || $parentid!=$parentid0){
                $where_ = "sort='$sort'  and parentid=$parentid";
                $rs = $this->conn_catalog->get_one("*", $where_);
                if($rs):
                    showmessage("修改失败！目录名称已经存在", "?c=catalog&a=manage_mod&cataid=$cataid0") ;
                    exit();
                endif;
            }

            //字段唯一性检查
            $_POST["data"]["ifm"] = $_POST["data"]["ifm"] ? '0' : '1';
            $newField = strtolower(trim($_POST["data"]["fieldname"]));
            $mytb = $_POST["data"]["pi"] == "i" ? "i_tb" : "p_tb";
            $this->conn_tb = $_POST["data"]["pi"] == "i" ? $this->conn_i : $this->conn_p;
            if (! $_POST["data"]["ifm"] && $newField != $oldField &&$this->conn_tb->field_exists($newField)) { 
                    showmessage("修改失败！该字段已经存在", "?c=catalog&a=manage_mod&cataid=$cataid0") ;
                    exit(); 
            }
              
            // 修改或添加字段：属性复选框选中；有字段名称且名称已经改变；是顶级目录。
            if (! $_POST["data"]["ifm"] && $newField && $newField != $oldField ) {
                // 目标表存在旧字段
                if (! $this->conn_tb->field_exists($newField) && $this->conn_tb->field_exists($oldField)) {
                    $sql = "alter table $mytb change $oldField $newField varchar(220) default ' '";
                    $this->conn_tb->query($sql);
                }
                // 目标表不存旧字段
                if (! $this->conn_tb->field_exists($newField) && ! $this->conn_tb->field_exists($oldField)) {
                    $sql = "alter table $mytb add  $newField varchar(220) default ' '";
                    $this->conn_tb->query($sql);
                }
            }
            // 删除字段:非属性、新字段名称为空且新旧不相等
            
            if (  $_POST["data"]["ifm"] && ! $newField && $newField != $oldField ) {
                
                // 目标表存在旧字段
                if ($this->conn_tb->field_exists($oldField)) {
                    $sql = "alter table $mytb drop $oldField";
                    $this->conn_tb->query($sql);
                }
            }

            $_POST["data"]["fieldname"]=$newField;
            
            $this->conn_catalog->update($_POST["data"], $where);
            $this->conn_catalog->affected_rows() ? showmessage(L("do_ok"), "?c=catalog&a=manage") : showmessage(L("do_fail"), "?c=catalog&a=manage");
        }
        
        include parent::load_tpl("catalog_mod");
    }

    /*
     * 异步检查目录名称是否可用
     */
    function ajax_check_catalog()
    {

        $parentid=$_POST["parentid"];
        $sort=$_POST["sort"];
        
        // 修改目录时如果目录名称没有变化则正常返回
        if (isset($_POST["sort0"])) {
            $sort0=$_POST["sort0"];
            $parentid0=$_POST["parentid0"]; 
            if($sort0 != $sort || $parentid!=$parentid0){
                $where = "sort='$sort'  and parentid=$parentid";
                $rs = $this->conn_catalog->get_one("*", $where);
                $arr['success']=$rs ? '1' : '0';
            }else{
                $arr['success']='0';
            }
        }else{
            $where = "sort='$sort'  and parentid=$parentid";
            $rs = $this->conn_catalog->get_one("*", $where);
            $arr['success']=$rs ? '1' : '0';
        }
        echo json_encode($arr);
    }

    /*
     * 异步检查字段名称是否可用
     */
    function ajax_check_fieldname(){
         $pi=$_POST["pi"];
         $newFieldname= strtolower($_POST["fieldname"]);
         $mytb = $pi == "i" ? "i_tb" : "p_tb";
         $this->conn_tb = $pi == "i" ? $this->conn_i : $this->conn_p;

         if (isset($_POST["oldField"])) {
            $oldField=$_POST["oldField"]; 
             if(strtolower($oldField) != $newFieldname){
                 $arr['success']=$this->conn_tb->field_exists($newFieldname)?'1' : '0';
             }else{
                 $arr['success']='0';
             }
         }else{
             $arr['success']=$this->conn_tb->field_exists($newFieldname)?'1' : '0';
         }
         
         echo json_encode($arr);
    }
    /*
     * 异步：单个删除:
     */
    function ajax_del_catalog()
    {
        $cataid = $_POST["cataid"];
        $where = "cataid=$cataid";
        $this->conn_catalog->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
}