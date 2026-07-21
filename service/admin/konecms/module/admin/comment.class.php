<?php
/**
 * i.class.php 文档添加、管理
 * @copyright konecms.com 2016-2020
 * last update date 2016年12月3日
 */
konecms::load_module_classes("admin_base");

class comment extends admin_base
{

    function __construct()
    {
        parent::__construct();
        
        $this->conn_feedback = konecms::load_model_class("feedback"); 
        
    }
   
    
    /*
     * 站点留言
     *
     */
    function feedback()
    {  
        // 1. 初始化
        $where = "1=1";
       
        $c="comment";
        $a="feedback";
        $wFieldArr=array("name","tel","content");
        $dataListArr=dataList($c,$a,$wFieldArr);
        $url = $dataListArr["url"];//a链接
        $pageurl = rtrim($dataListArr["pageurl"],"&");//分页字符串中除了page的字段链接
        $mywhere = $dataListArr["mywhere"];//分页字符串中除了page的字段链接
        $where=$where.$mywhere;
        
       
        $percount = $_SESSION["PERCOUNT"]?$_SESSION["PERCOUNT"]:10;
        
        $cols = "*";
        $listArr = $this->conn_feedback->i($cols, $where, "id desc", "", 15, "", "", $percount);

        // 4. 当前搜索条件结果展示
        $mArr=array();
        $pi="";
        $conn="";
        $soArr=soArr($pageurl,$mArr,$pi,$conn);
        
        
        $amount = $this->conn_feedback->count($where); // 总记录数
        $pageCount = ceil($amount / $percount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
        
        include parent::load_tpl("feedback");
    }
   
    /*
     * 异步设置：批量删除
     */
    function ajax_dels_feedback()
    {
        $ids = $_POST["ids"];
        $idsArr = explode(",", $ids);
        foreach ($idsArr as $id) {
            $where = "id=$id";
            $this->conn_feedback->delete($where);
    
        }
        $arr['success'] = 0;
        echo json_encode($arr);
    }
    
    /*
     * 异步：单个删除
     */
    function ajax_del_feedback()
    {
        $id = $_POST["id"];
        $where = "id=$id";
        $this->conn_feedback->delete($where);
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





