
<?php
/**
 * @copyright konecms.com
 * @lastmodify 2016年2月26日
*/

            
konecms::load_module_classes("admin_base");
class index extends admin_base{
    
	public function __construct(){
	     parent::__construct();
        $this->conn_i=konecms::load_model_class("i"); 
        $this->conn_catalog=konecms::load_model_class("catalog"); 
	    //广告
	    $this->conn_ad=konecms::load_model_class("ad");
	    //专家
	    $this->conn_h=konecms::load_model_class("h");
	    $this->conn_mycarehid=konecms::load_model_class("mycarehid");
	    //关注专家
	    $this->conn_mycarehid=konecms::load_model_class("mycarehid");
	    //
	    $this->conn_usual=konecms::load_model_class("usual");
	    

	    $this->conn_feedback=konecms::load_model_class("feedback");
	}

	/*
	 * 站点首页
	 */
	function init(){  
	    //定制cataid
	    $sort_=array("新闻","7X24H快讯");
	    $k_=array("news","kuai");
	    $i=0;
	    $indexCataidArr=array();
	    foreach($sort_ as $a){
	        $where_="sort='$a'";
	        $arr_=$this->conn_catalog->get_one("cataid",$where_);
	        $indexCataidArr[$k_[$i]]=$arr_["cataid"];
	        $i++;
	    } 
	    //新闻-》头条
	          $newsCataid=$this->navArr["subnav"][$indexCataidArr["news"]][0]["cataid"];
	          $cataid_=11;
              $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i1Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);

	     //新闻-》行情
	          $cataid_=12;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i2Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          //新闻->研报
	          $cataid_=13;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i3Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          //新闻-》人物
	          $cataid_=14;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i4Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          //新闻-》宏观
	          $cataid_=15;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i5Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);

	          //新闻-》技术
	          $cataid_=17;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i6Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          
	          //新闻-》政策
	          $cataid_=54;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i7Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          
	          //新闻-》评级
	          $cataid_=55;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i8Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          
	          //新闻-》全球
	          $cataid_=57;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i9Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          
	          //新闻-》八卦
	          $cataid_=58;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i10Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	          
	          //新闻-》挖矿
	          $cataid_=59;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i11Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);

	          //新闻-》OTC
	          $cataid_=83;
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $i12Arr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",20);
	           
	          
       // 最新资讯
	          $kuaiCataid=$this->navArr["subnav"][$indexCataidArr["kuai"]][0]["cataid"];
	          $where="  ifindex='0' and ifhidden='1' ";
	          $mysubcataid = Cataid($this->conn_catalog, $kuaiCataid);
	          $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	          $subwhere = $this->genWhere($cataidArr_);
	          $where .= $subwhere;
	          $kuaiArr=$this->conn_i->select("id,cornertitle,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl",$where,"riqi desc",20);
if($kuaiArr){
    $i=0;
    $startdate=date("Y-m-d H:i:s");
    foreach($kuaiArr as $a){
        $enddate=$riqi=$a["riqi"]; 
        $date=floor((strtotime($startdate)-strtotime($enddate))/(3600*24));
         
        $hour=floor((strtotime($startdate)-strtotime($enddate))/3600);
        
        $minute=floor((strtotime($startdate)-strtotime($enddate))/60);
         
        $second=floor((strtotime($startdate)-strtotime($enddate)));
        
        if($second<=59){
            $time=$second."秒前";
        }elseif($minute<=59){
            $time=$minute."分前";            
        }elseif($hour<=24){
            $time=$hour."小时前";            
        }else{ 
            $time=$date."天前";
        }
        
        $kuaiArr[$i]["time"]=$time;
        $i++;
    }
}
      //一周点击排行 
      $myriqi=date("Y-m-d",strtotime("-1 week"));
	          $where="  ifindex='0' and ifhidden='1' and riqi >='$myriqi'"; 
	          $pai1Arr=$this->conn_i->select("id, cataid, riqi,hitnum,pinglunnum,title, picdir_list",$where,"hitnum desc",6);
	          $curday=time();
	          $i=0;
	          foreach($pai1Arr as $a){
	              $id=$a["id"];
	              $day=strtotime($a["riqi"]);
	              $pai1Arr[$i]["num_days"]=floor(($curday-$day)/86400);
	              $count=$this->conn_feedback->count("pid=$id");
	              $pai1Arr[$i]["pinglunnum"]=$count;
	              $i++;
	          }
      //一周评论排行 
	          $where="  ifindex='0' and ifhidden='1'  and riqi >='$myriqi'"; 
	          $pai2Arr=$this->conn_i->select("id, cataid, riqi,hitnum,pinglunnum,title, picdir_list",$where,"pinglunnum desc",6);

	          $i=0;
	          foreach($pai2Arr as $a){
	              $id=$a["id"];
	              $day=strtotime($a["riqi"]);
	              $pai2Arr[$i]["num_days"]=floor(($curday-$day)/86400);
	              $count=$this->conn_feedback->count("pid=$id");
	              $pai2Arr[$i]["pinglunnum"]=$count;
	              $i++;
	          }
	          
	          foreach ($pai2Arr as $key => $row) { 
	              $money[$key] = $row['pinglunnum'];
	          }
	          array_multisort($money, SORT_DESC, $pai2Arr);
	          
	      
      //专家
	  $where="  ifok='0' and ifauthor='0' "; 
	  $zhuanjiaArr=$this->conn_h->select("id,picdir,name,short",$where,"hitnum desc",6);
	   
	  if($_POST["data"]["hid"]){
	      $myhid=$_POST["data"]["hid"];
	      $i=0;
	      foreach($zhuanjiaArr as $a){
	          $hid=$a["id"];
	          $data_=$this->conn_mycarehid->get_one("*","carehid=$hid and myhid=$myhid");
	          if($data_){
	              $zhuanjiaArr[$i]["ifover"]='0';
	          }else{
	              $zhuanjiaArr[$i]["ifover"]='1';
	          }
	          $i++;
	      }
	  }

	    //banner
	    $where="sort='首页轮播大图' and ifhidden='1'";
	    $showArr=$this->conn_ad->select("*",$where,"orderid asc");

	    //subbanner
	    $where="sort='首页轮播推荐位用图' and ifhidden='1'";
	    $subshowArr=$this->conn_ad->select("*",$where,"orderid asc",2);

	    // 
	    $where="sort='首页广告横幅' and ifhidden='1'";
	    $adshowArr=$this->conn_ad->get_one("*",$where,"orderid asc",1);

	    //
	    $where="sort='首页推广广告' and ifhidden='1'";
	    $tuishowArr=$this->conn_ad->select("*",$where,"orderid desc");
	     
		$adshowArr=array($adshowArr);
		//评论
		$feedArr=$this->conn_feedback->select("*","","riqi desc",50);
		if($feedArr){
		    $i=0;
		    foreach($feedArr as $a){
		        $hid=$a["hid"];
		        $arr_=$this->conn_h->get_one("name,picdir","id=$hid");
		        $feedArr[$i]["picdir"]=$arr_["picdir"];
		        $feedArr[$i]["name"]=$arr_["name"];
		        $i++;
		    }
		}
		
	    $rtnArr=array(
		   "showArr"=>$showArr,//大BANNER
		   "subshowArr"=>$subshowArr,//BANNER右侧2图
		   "adshowArr"=>$adshowArr,//广告横幅
		   "i1Arr"=>$i1Arr,//头条
	        "i2Arr"=>$i2Arr,//行情
	        "i3Arr"=>$i3Arr,//研报
	        "i4Arr"=>$i4Arr,//人物
	        "i5Arr"=>$i5Arr,//宏观
	        "i6Arr"=>$i6Arr,//技术
	        "i7Arr"=>$i7Arr,//政策
	        "i8Arr"=>$i8Arr,//评级
	        "i9Arr"=>$i9Arr,//全球
	        "i10Arr"=>$i10Arr,//八卦
	        "i11Arr"=>$i11Arr,//挖矿
	        "i12Arr"=>$i12Arr,//OTC
	        "kuaiArr"=>$kuaiArr,//快讯
	        "pai1Arr"=>$pai1Arr,//一周点击排行
	        "pai2Arr"=>$pai2Arr,//一周评论排行
	        "zhuanjiaArr"=>$zhuanjiaArr,//专家
	        "tuishowArr"=>$tuishowArr, //推广
	        "feedArr"=>$feedArr//评论
	    );
	    echo  json_encode($rtnArr);
	     
	      //include self::load_tpl("index");
	}
	 
	/*
	 * 关注专家
	 */
	function carehid(){
	    if (isset($_POST["data"])) {
	        $hid=$_POST["data"]["hid"]=$_POST["data"]["hid"];
	        $carehid=$_POST["data"]["hid"]=$_POST["data"]["mycarehid"];
	        $riqi=date("Y-m-d H:i:s");
	        $arr=array("carehid"=>$carehid,"myhid"=>$hid,"riqi"=>$riqi);
            $this->conn_mycarehid->insert($arr); 
	        $myArr["success"]=0;
	        echo json_encode($myArr);
	    }
	     
	}
	/*
	 * genWhere($cataidArr, $shuxArr=array())
	 * #给定查询目录ID数组（和属性数组）生成条件语句
	 * @retrun String (  where条件语句  )
	 * @cataidArr 目录集或者目录ID
	 * @shuxArr 属性内容
	 *
	 */
	private function genWhere($cataidArr, $shuxArr = array())
	{
	    // 目录
	    $where = "";
	    $str_ = "";
	    if(is_array($cataidArr)){
	        $where .= " and (";
	        foreach ($cataidArr as $a) {
	            $v_ = "cataid" . $a;
	            $str_ .= " cataid like '%$v_%' or ";
	        }
	        $where .= rtrim($str_, "or ") . ")";
	    }else{
	        $where=" and (cataid like '%$cataidArr%')";
	    }
	    // 属性
	    if ($shuxArr) {
	        foreach ($shuxArr as $a) {
	            $fieldname_ = $a["fieldname"];
	            if (isset($_GET[$fieldname_])) {
	                $v = $_GET[$fieldname_];
	                $where .= " and $fieldname_ like '%$v%'";
	            }
	        }
	    }
	    return  $where;
	}

	public function link(){
	    //subbanner
	    $where="sort='友情链接' and ifhidden='1'";
		$linkArr=$this->conn_ad->select("*",$where,"orderid asc",20);
		echo json_encode($linkArr);
	}
	
	public function usual(){	     
		$linkArr=$this->conn_usual->select("*");
		echo json_encode($linkArr);
	}
	
	public function getMore(){	
	    $cataid_=$_POST["data"]["cataid"];
	    $arr_=explode(",",$cataid_);
	    $cataid_=str_replace("cataid","",$arr_[0]);
	    $id=$_POST["data"]["id"];	    
	    $where="ifindex='0' and ifhidden='1' and id<$id ";
	    $mysubcataid = Cataid($this->conn_catalog, $cataid_);
	    $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
	    $subwhere = $this->genWhere($cataidArr_);
	    $where .= $subwhere;
	    $dataArr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",10);
	    echo json_encode($dataArr);
	}




}












