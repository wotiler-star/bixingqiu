<?php
/**
 * i.class.php 文档添加、管理
 * @copyright konecms.com 2016-2020
 * last update date 2016年12月3日
 */
konecms::load_module_classes("admin_base");

class h extends admin_base
{

    function __construct()
    {
        parent::__construct();
        
        $this->conn_h = konecms::load_model_class("h"); 
        $this->conn_h_info_geren = konecms::load_model_class("h_info_geren");
        $this->conn_h_info_qiye = konecms::load_model_class("h_info_qiye");
        $this->conn_h_info_meiti = konecms::load_model_class("h_info_meiti");
        
    }
   
    
    /*
     * 会员管理
     *
     */
    function manage()
    {  
        // 1. 初始化
        $where = "1=1";
       
        $c="h";
        $a="manage";
        $wFieldArr=array("name","hname");
        $dataListArr=dataList($c,$a,$wFieldArr);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"]; 
        $where=$where.$mywhere;
        
       
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        
        $cols = "*";
        $listArr = $this->conn_h->i($cols, $where, "id desc", "", 15, "", "", $percount);

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
        
        
        $amount = $this->conn_h->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
        
        include parent::load_tpl("h_manage");
    }
   
    /* 专栏会员
    *
    */
    function manage_vip()
    {
        // 1. 初始化
        $where = "ifauthor is not null ";
         
        $c="h";
        $a="manage_vip";
        $wFieldArr=array("name","hname");
        $dataListArr=dataList($c,$a,$wFieldArr);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];
        $where=$where.$mywhere;
    
         
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
    
        $cols = "*";
        $listArr = $this->conn_h->i($cols, $where, "id desc", "", 15, "", "", $percount);
    
        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
    
    
        $amount = $this->conn_h->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
    
        include parent::load_tpl("h_manage_vip");
    }
    /* 专栏会员
     *
     */
    function manage_vip_mod()
    {
       
        ! isset($_GET["id"]) && showmessage(L("错误的ID值"), "?c=h&a=manage_vip");
        $id= $_GET["id"];
        $where0 = "id=$id";
        $data=$this->conn_h->get_one("*",$where0);
         $sort=$data["sort"];
    
        switch($sort){
            case "geren": 
                
                $conn=$this->conn_h_info_geren;
                break;
            case "qiye": 
                $conn=$this->conn_h_info_qiye;
                break;
            case "meiti": 
                $conn=$this->conn_h_info_meiti;
                break;
        }
        $where="hid=$id";
        $data2=$conn->get_one("*",$where);
        if(isset($_POST["data"])){
           
            $this->conn_h->update($_POST["data"],$where0);
        
            $url=modReturnURL("manage_vip");
        
            $this->conn_h->affected_rows() ? showmessage(L("do_ok"), "?$url") : showmessage(L("do_fail"), "?$url");
        }
         
         
        include parent::load_tpl("h_manage_vip_show");
    }
     
    /*
     * 异步设置：批量删除
     */
    function ajax_dels_h()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids);
        foreach ($idsArr as $id) {
            $where = "id=$id";
            $this->conn_h->delete($where);
    
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步：单个删除
     */
    function ajax_del_h()
    {
        $id = $_POST["id"];
        $where = "id=$id";
        $this->conn_feedback->delete($where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步设置：有效状态
     */
     
    function ajax_set_ifok()
    {
        $arr = array(
            "ifok" => $_POST["ifok"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_h->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }

    /*
     * 异步设置：专栏审核状态
     */
     
    function ajax_set_ifauthor()
    {
        $arr = array(
            "ifauthor" => $_POST["ifauthor"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_h->update($arr, $where);
    
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步设置：专栏审核状态
     */
     
    function ajax_set_ifhot()
    {
        $arr = array(
            "ifhot" => $_POST["ifhot"]
        );
        $where = "id=" . $_POST["id"];
        $this->conn_h->update($arr, $where);
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    

    /*
     * 异步：删除单个搜索条件
     */
    function ajax_del_so_feedback()
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
      
    
}//class





