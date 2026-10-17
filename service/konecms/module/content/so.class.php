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
          $w = (string) $_GET["w"];
          /* [SQLi 修复 #27] 用安全助手替代裸拼接：where_like 内部做 escape_once(幂等) + 通配符转义，
             不再依赖 route 层 addslashes + 可绕过的黑名单。 */
          $where = $this->conn_i->where_like('title', $w);
          /* [BUG 修复] 原来 select() 不传 order / limit，一个宽泛关键词
           * （如"币"、"区块链"）会把 i_tb 全表几千行一次性查出来再 JSON 序列化，
           * 接口动辄数秒、内存飙升，前端也会卡死。现按 id 倒序取最新 200 条。 */
          $data=$this->conn_i->select("id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl,lihao,likong,cnt_short,hid",$where,"id desc",200);
          if(!is_array($data)){ $data = array(); } // 查询异常时返回 [] 而不是 false，避免前端 .map 崩溃
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