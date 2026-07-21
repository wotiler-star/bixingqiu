<?php
/**
 * uaual.class.php 系统配置
 * @copyright konecms.com 2016-2020
 * last update date 2016年3月27日
 */
konecms::load_module_classes("admin_base");
class usual extends admin_base{
    function __construct(){
        parent::__construct();
        $this->db=konecms::load_model_class("usual"); 
        $this->data=$this->db->get_one("*");
    }
    
    /*
     * 城市配置
     */
    function city(){
        $pinyin=konecms::load_lib_class("pinyin");
         
        $province=$this->conn_province->select("*");
        
        $info=file_get_contents("konecms/model/data/info_tb.txt");
        $brand=file_get_contents("konecms/model/data/brand_ask_tb.txt");
        $ad=file_get_contents("konecms/model/data/market_ad_ask_tb.txt");
        $p=file_get_contents("konecms/model/data/p_tb.txt");
        $p_mlist_m=file_get_contents("konecms/model/data/p_mlist_m_tb.txt");
        $p_mlist_so=file_get_contents("konecms/model/data/p_mlist_so_tb.txt");
        
        for($i=0;$i<count($province);$i++){
            $pcode=$province[$i]["code"];
            $where="provincecode='$pcode'";
            $c=$this->conn_city->select("code,name,ifok",$where);
            for($j=0;$j<count($c);$j++){
                $cname=$c[$j]["name"];
                $cname=iconv("utf-8","gbk",$cname);
                $cname=$pinyin->getAllPY($cname);
                $c[$j]["pinyin"]=$cname;
            }
            $province[$i]["c"]=$c;
        } 
        if(isset($_POST["city"])){
            $city=$_POST["city"];

            $arr=array("ifok"=>1);
            $this->conn_city->update($arr,"1=1");
         
            foreach($city as $k=>$v){

                //新建表
                
                //撮合信息
                $info_sql=str_replace("tbname",$v,$info);
                $this->conn_city->query($info_sql);
                //品牌
                $brand_sql=str_replace("tbname",$v,$brand);
                $this->conn_city->query($brand_sql);
                //广告位
                $ad_sql=str_replace("tbname",$v,$ad);
                $this->conn_city->query($ad_sql);
                //产品
                $p_sql=str_replace("tbname",$v,$p);
                $this->conn_city->query($p_sql);
                //产品-属性ID
                $p_mlist_m_sql=str_replace("tbname",$v,$p_mlist_m);
                $this->conn_city->query($p_mlist_m_sql);
                $p_mlist_so_sql=str_replace("tbname",$v,$p_mlist_so);
                $this->conn_city->query($p_mlist_so_sql);
                
                //修改状态
                $where="code='$k'";
                $arr=array("ifok"=>0);
                $this->conn_city->update($arr,$where);
            
            }
            $this->conn_city->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=city"):showmessage(L("do_fail"),"?c=usual&a=city");
        }
        
        include parent::load_tpl("usual_city");
    }
    
    
    /*
     * 网站配置
     */
    function web(){
        
        if(isset($_POST["data"])){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=web"):showmessage(L("do_fail"),"?c=usual&a=web");
            }else{
                $this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=web") : showmessage(L("do_fail"), "?m=admin&c=usual&a=web");
           }
            
        }
        $data=$this->data;
        include parent::load_tpl("usual_web");
    }

    /*
     * 资料配置
     */
    function contact(){
    
      if(isset($_POST["data"])){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=contact"):showmessage(L("do_fail"),"?c=usual&a=contact");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=contact") : showmessage(L("do_fail"), "?m=admin&c=usual&a=contact");
         }
        }
        $data=$this->data;
        include parent::load_tpl("usual_contact");
    }
    
    /*
     * 后台配置
     */
    function sysmanage(){
    
        if(isset($_POST["data"])){
            $percount=$_POST["data"]["percount"];
            $sessiontime=$_POST["data"]["sessiontime"];
            $_SESSION["PERCOUNT"]=$percount;
            $_SESSION["SESSIONTIME"]=$sessiontime;
            $flag=false;
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $flag=$this->db->affected_rows()?true:false;
            }else{
                $insertid=$this->db->insert($_POST["data"]);
                $flag=$this->db->insert_id()?true:false;
            }
            $flag?showmessage(L("do_ok"), "?m=admin&c=usual&a=sysmanage") : showmessage(L("do_fail"), "?m=admin&c=usual&a=sysmanage");
             
        }
        $data=$this->data;
        include parent::load_tpl("usual_sysmanage");
    }

    /*
     * 支付宝配置
     */
    function alipay(){
    
     if(isset($_POST["data"])){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=alipay"):showmessage(L("do_fail"),"?c=usual&a=alipay");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=alipay") : showmessage(L("do_fail"), "?m=admin&c=usual&a=alipay");
         }
        }
        $data=$this->data;
        include parent::load_tpl("usual_alipay");
    }

    /*
     * 运费
     */
    function yunf(){
    
     if(isset($_POST["data"])){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=yunf"):showmessage(L("do_fail"),"?c=usual&a=yunf");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=yunf") : showmessage(L("do_fail"), "?m=admin&c=usual&a=yunf");
            }
      }
        $data=$this->data;
        include parent::load_tpl("usual_yunf");
    }

    /*
     * 自取点
     */
    function pickupaddr(){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=pickupaddr"):showmessage(L("do_fail"),"?c=usual&a=pickupaddr");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=pickupaddr") : showmessage(L("do_fail"), "?m=admin&c=usual&a=pickupaddr");
            }
        $data=$this->data;
        include parent::load_tpl("usual_pickupaddr");
    }
    

    /*
     * 折算配置
     */
    function price(){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=price"):showmessage(L("do_fail"),"?c=usual&a=price");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=price") : showmessage(L("do_fail"), "?m=admin&c=usual&a=price");
            }
        $data=$this->data;
        include parent::load_tpl("usual_price");
    }


    /*
     * 图像配置
     */
    function pic(){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=pic"):showmessage(L("do_fail"),"?c=usual&a=pic");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=pic") : showmessage(L("do_fail"), "?m=admin&c=usual&a=pic");
            }
        $data=$this->data;
        include parent::load_tpl("usual_pic");
    }


    /*
     * 邮箱配置
     */
    function email(){
        if(isset($_POST["data"])){
            if($this->data){
                $this->db->update($_POST["data"],"1=1");
                $this->db->affected_rows()?showmessage(L("do_ok"),"?c=usual&a=web"):showmessage(L("do_fail"),"?c=usual&a=web");
            }else{
                $insertid=$this->db->insert($_POST["data"]);   
                $this->db->insert_id()?showmessage(L("do_ok"), "?m=admin&c=usual&a=email") : showmessage(L("do_fail"), "?m=admin&c=usual&a=email");
         } 
        }
        $data=$this->data;
        include parent::load_tpl("usual_email");
    }
 
    
    
}