<?php
/**
 * so.class.php
 * @copyright konecms 2016-2020
 * last update date 2018年6月17日
 */

konecms::load_module_classes("admin_base");

class so extends admin_base
{

    public function __construct()
    {
        parent::__construct();
        $this->conn_i = konecms::load_model_class("i"); 
        $this->conn_catalog = konecms::load_model_class("catalog");
    }

    public function init()
    {
        ! isset($_GET["w"]) && showmessage("错误的URL值", "?");
          $w = $_GET["w"];
          $mydata="";
          $where="title like '%$w%'";
          $data=$this->conn_i->select("id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl,lihao,likong,cnt_short,hid",$where);
          if($data){
              $i=0;
              foreach($data as $a){
                  $cataid=$a["cataid"];
                  $arr_=explode(",",$cataid);
                  $mycataid=str_replace("cataid","",$arr_[0]);
                  $data[$i]["cataid"]=$mycataid;
                  $where_="cataid=$mycataid";
                  $arr_=$this->conn_catalog->get_one("sort",$where_);
                  $data[$i]["sort"]=$arr_["sort"];
                  $data[$i]["cataid"]="cataid".$mycataid;
                  $i++;
              } 
          } 
          echo json_encode($data);        
   // include  parent::load_tpl("so");
    }
}