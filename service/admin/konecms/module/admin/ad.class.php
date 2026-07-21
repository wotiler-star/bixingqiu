<?php
/**
 * ad.class.php 广告管理
 * @copyright konecms.com 2016-2020
 * last update date 2016年12月3日
 */
konecms::load_module_classes("admin_base");

class ad extends admin_base
{

    function __construct()
    {
        parent::__construct();
        
        $this->conn_ad = konecms::load_model_class("ad"); 
        $this->conn_catalog = konecms::load_model_class("catalog_ad");
          
    }
    /*
     * 添加广告
     */
    function generate()
    {
        //获取位置
        $data = $this->conn_catalog->select("*");
        
        $orderArr= $this->conn_ad->get_one("orderid","","orderid desc");
         
        if (isset($_POST["data"])) {
           // for($i=0;$i<=10;$i++){
                $this->conn_ad->insert($_POST["data"]);
           // }
           $this->conn_ad->insert_id() ? showmessage(L("do_ok"), "?c=ad&a=generate") : showmessage(L("do_fail"), "?c=ad&a=generate");
        }
        include parent::load_tpl("ad_generate");
    }
    /*
     * 修改广告 
     */
    function manage_mod()
    {
        !isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=i&a=manage");
        //获取位置
        $data_catalog = $this->conn_catalog->select("*");
        $where = "id=" . $_GET["id"];
        $data = $this->conn_ad->get_one("*", $where);
    
        if (isset($_POST["data"])) {
            $this->conn_ad->update($_POST["data"], $where);

            $url=modReturnURL();
            
            $this->conn_ad->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url"); 
        }
    
        include parent::load_tpl("ad_manage_mod");
    }
    
    /*
     * 广告管理
     *
     */
    function manage()
    { 
        // 1. 初始化
        $where = "1=1"; 
        //组选项
        $catalogArr=$this->conn_catalog->select("sort");
       
        $c="ad";
        $a="manage";
        $wFieldArr=array("title","sort");
        $dataListArr=dataList($c,$a,$wFieldArr);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
        $where=$where.$mywhere;

        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        $cols = "*";
        $listArr = $this->conn_ad->i($cols, $where, "orderid desc", "", 15, "", "",$percount);
 

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
        
        $amount = $this->conn_ad->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
         
        
        include parent::load_tpl("ad_manage");
    }
    
    
    /*
     * 添加位置
     */
    function generate_catalog()
    { 
       
        if (isset($_POST["data"]["sort"])) {
            // 1. 获取开关值 
            $this->conn_catalog->insert($_POST["data"]);
    
            $this->conn_catalog->insert_id()?showmessage(L("do_ok"), "?c=ad&a=generate_catalog") : showmessage(L("do_fail"), "?c=ad&a=generate_catalog");
        }
        include parent::load_tpl("ad_generate_catalog");
    }

    /*
     * 广告位置管理
     *
     */
    function manage_catalog()
    { 
        // 1. 初始化
        $where = "1=1";
       
        $c="ad";
        $a="manage_catalog"; 
        $wFieldArr=array("sort");
        $dataListArr=dataList($c,$a,$wFieldArr); 
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
        $where=$where.$mywhere;

        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        $cols = "*";
        $listArr = $this->conn_catalog->i($cols, $where, "id desc", "", 15, "", "",$percount);

        $i = 0;
        foreach ($listArr as $a) {
            $sort = $a["sort"];
            $where_ = "sort='$sort'";
            $listArr[$i]["mycount"] = $this->conn_ad->count($where_);
            $i ++;
        }

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
        
        
        $amount = $this->conn_catalog->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
         
        
        include parent::load_tpl("ad_manage_catalog");
    }
    /*
     * 修改广告位置
     */
    function manage_catalog_mod()
    {
        !isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=ad&a=manage_catalog");
        $where = "id=" . $_GET["id"];
        $data = $this->conn_catalog->get_one("*", $where);
    
        if (isset($_POST["data"])) { 
            $this->conn_catalog->update($_POST["data"], $where);
            $url=modReturnURL("manage_catalog"); 
            $this->conn_catalog->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url");
            
            $this->conn_catalog->affected_rows() ? showmessage(L("do_ok"), "?c=ad&a=manage_catalog") : showmessage(L("do_fail"), "?c=ad&a=manage_catalog");
        }
    
        include parent::load_tpl("ad_manage_catalog_mod");
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
        $this->conn_ad->update($arr, $where);
    
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
                    $this->conn_ad->delete($where);
                    
        }
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
     * 异步：删除单个搜索条件
     */
    function ajax_del_so_catalog()
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
     * 异步：单个删除
     */
    function ajax_del()
    {
        $id = $_POST["id"];
        $where = "id=$id";
        $this->conn_ad->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    /*
     * 异步：单个删除: 
     */
    function ajax_del_catalog()
    {
        
        $cataid = $_POST["cataid"];
        $where = "id=$cataid";
        $this->conn_catalog->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
        
    }
    /*
     * 异步设置：批量删除
     */
    function ajax_dels_catalog()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids);
        foreach ($idsArr as $id) {
            $where = "id=$id";
            $this->conn_catalog->delete($where);
        }
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
            $this->conn_ad->update($post, $where);
            $i ++;
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
}//class





