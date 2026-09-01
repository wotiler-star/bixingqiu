<?php 
konecms::load_module_classes("admin_base");
require_once __DIR__ . '/../../../source/lib_ratelimit.php';

class Content extends admin_base
{
 
    public function __construct()
    {
        parent::__construct();
        $this->conn_i = konecms::load_model_class("i"); 
        $this->conn_catalog = konecms::load_model_class("catalog");
        $this->conn_feedback = konecms::load_model_class("feedback");
        $this->conn_h = konecms::load_model_class("h");
        $this->conn_mycarehid = konecms::load_model_class("mycarehid");
        $this->conn_site = konecms::load_model_class("site");
        $this->conn_favorate=konecms::load_model_class("favorate");
    }

    /*
     * [安全修复] 当前登录会员 id，一律取服务端 session，绝不采信客户端 hid。
     */
    private function currentHid()
    {
        return isset($_SESSION["HID"]) ? (int) $_SESSION["HID"] : 0;
    }

    /*
     * [安全修复] 写接口鉴权。未登录直接输出 JSON 并终止。
     */
    private function requireLogin()
    {
        $hid = $this->currentHid();
        if ($hid <= 0) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(array("success" => 401, "msg" => "请先登录"), JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $hid;
    }

    /*
     * [安全修复] 取 $_POST["data"] 中的整数字段。
     * route.class.php 对 POST **数组** 不做任何过滤，因此 $_POST["data"]["id"]
     * 会原样进入 "id=$id" 这类裸插值 WHERE，构成注入点（黑名单不拦 OR / =）。
     * 所有数字型主键必须经此函数强转。
     */
    private function postInt($key, $default = 0)
    {
        if (!isset($_POST["data"]) || !is_array($_POST["data"])) {
            return $default;
        }
        return isset($_POST["data"][$key]) ? (int) $_POST["data"][$key] : $default;
    }

    /*
     * [安全修复] 字段白名单，杜绝 insert($_POST["data"]) 的批量赋值越权
     * （原实现可顺带写入 ifok / ifchecked / orderid 等审核控制列）。
     */
    private function pickFields($allow, $src = null)
    {
        if ($src === null) {
            $src = isset($_POST["data"]) && is_array($_POST["data"]) ? $_POST["data"] : array();
        }
        $out = array();
        foreach ($allow as $k) {
            if (isset($src[$k]) && !is_array($src[$k])) {
                $out[$k] = $src[$k];
            }
        }
        return $out;
    }

    /*
     * [安全修复] 统一 JSON 输出，避免 header 未设导致的 XSS/嗅探。
     */
    private function jsonOut($arr)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    public function init()
    {
        
        ! isset($_GET["cataid"]) && showmessage("错误的URL值", "?");
       // unset($_SESSION["HID"]);
        $this->mycataid = $cataid = $_GET["cataid"];
        if(strpos($this->mycataid,"ataid")) $this->mycataid=str_replace("cataid","",$this->mycataid);
        // [安全修复] cataid 落到 "cataid=$this->mycataid" 裸插值 WHERE，强转整型防注入
        $this->mycataid = (int) $this->mycataid;
        $this->sortArr = $this->conn_catalog->get_one("*", "cataid=$this->mycataid");
        !$this->sortArr && showmessage("没有找到页面", "?");
        //目录属性 
         $this->conn=$this->conn_i;
        //当前目录下的所有子目录 
         $this->mysubcataid = Cataid($this->conn_catalog, $this->mycataid); 
        //当前目录下的所有父目录 
         $this->mypcataid = pCataid($this->conn_catalog, $this->mycataid);  
       
        $shux = $this->sortArr["shux"];
        $shux || isset($_GET["id"]) ? $this->detail() : $this->iList();
    }
    
    /*
     * 数据列表
     */
    public function iList()
    {

        //目录联动
        $cataArr=$this->getCataArr($this->mycataid);
        $parentidArr=array_reverse(explode(",",rtrim(pCataid($this->conn_catalog,$this->mycataid),",")));//索引0 为顶级目录，索引1为目录根
        $subArr=$this->navArr["subnav"][$parentidArr[1]];
         
        //属性联动（先检索）
        $shuxArr=$this->getShuxArr($this->mycataid);
        
        // 1. 生成where
        $where = "ifone='1'  and ifhead='1'  and ifhidden='1' and ifchecked='0' and ifpublic='0' ";
        $cataidArr_=explode(",",rtrim($this->mysubcataid,","));//索引0 为顶级目录，索引1为目录根
        $subwhere = $this->genWhere($cataidArr_,$shuxArr);
         $where .= $subwhere;
     
        // 2. 排序及分页链接A中的pagestr
        $orderby = $this->sortArr["orderby"];
        $orderbyList = $this->genOrderbyAndPagestr($orderby, array(
            "riqi",
            "hitnum"
        ));
        $orderby = $orderbyList["orderby"];
        $pagestr = $orderbyList["pagestr"];
     
        // 每页条数
         $pagecount = $this->sortArr["pagecount"];
    
        // 列表数据
        if($this->pi=="p"){
            $cols="id,title,subtitle,picdir_list,cnt_short,bnum,riqi,hitnum,dataurl,lihao,likong,cnt_short";
            $data = $this->conn->i($cols, $where, $orderby, "", 25, "", $pagestr, $pagecount);
             
        }else{
            $cols="id,cataid,source,short,riqi,hitnum,title,dataurl_fname,picdir_list,dataurl,lihao,likong,cnt_short,hid";
            $data = $this->conn->i($cols, $where, $orderby, "", 25, "", $pagestr, $pagecount);
        }
      
        $i=0;
        foreach($data as $a){
            $data[$i]["riqi"]=date("Y-m-d ",strtotime($a["riqi"]));    
           
            $i++;
        }
        // 分页
        $page = $this->conn->pagestr;    
        $amount = $this->conn->count($where); // 总记录数
        $pageCount = ceil($amount / $pagecount); // 总分页数
        $curpage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $prepage = $curpage == '1' ? 1 : $curpage - 1;
        $nextpage = $curpage == $pageCount ? $pageCount : $curpage + 1;
    
        // 路径
        $currootArr=$this->curroot($this->mycataid);
     
        // meta
        $currentSort = $this->sortArr["sort"];
        if ($this->sortArr["webtitle"]) {
            $metaArr["webtitle"] = $this->sortArr["webtitle"];
            $metaArr["keywords"] = $this->sortArr["keywords"];
            $metaArr["short"] = $this->sortArr["short"];
        } else {
            $metaArr["webtitle"] = $currentSort . "-" . $this->usualArr["sitename"];
            $metaArr["keywords"] = $currentSort;
            $metaArr["short"] = $currentSort;
        }
    
        // 头条
        $oneArr=$this->getSetArr("one",$this->mycataid,$this->conn,$cols);
 
        // 置顶
        $headArr=$this->getSetArr("head",$this->mycataid,$this->conn,$cols);
        // 推荐
        
        $hotArr=$this->getSetArr("hot",$this->mycataid,$this->conn,$cols);
          
        // 热点
        $redianArr=$this->getSetArr("redian",$this->mycataid,$this->conn,$cols);

        $i=0;
        foreach($hotArr as $a){
            $hotArr[$i]["riqi"]=date("Y-m-d ",strtotime($a["riqi"]));
            $i++;
        }

        $i=0;
        foreach($subArr as $a){
            $subArr[$i]["riqi"]=date("Y-m-d ",strtotime($a["riqi"]));
            $i++;
        }
        
        $rtnArr=array(
            "subArr"=>$subArr,//二级目录
            "data"=>$data,//列表数据
            "hotArr"=>$hotArr //右侧推荐
           
        );
        echo  json_encode($rtnArr);
    
       // include parent::load_tpl($this->sortArr["tplnamelist"]);
    }
    
    /*
     * 数据详情
     */
    private function detail()
    {
        // 获取数据
        /* [BUG 修复] 原实现里 $where 只在 isset($_GET["id"]) 分支内赋值，
         * 但 init() 对「单页属性(shux)」栏目也会走 detail()（此时无 id），
         * 于是 $where 未定义 → PHP 8 抛 Undefined variable 警告，且 get_one()
         * 在 where 为空时退化成「全站最新一篇」，单页栏目会串内容。
         * 这里给出兜底：无 id 时限定在当前栏目内取最新一篇。 */
        $where = "ifhidden='1' and cataid like '%cataid".$this->mycataid."%'";
        if (isset($_GET["id"])):
        $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
        $where = "cataid like '%cataid".$this->mycataid."%' and id <>$id";
        $aboutArr = $this->conn->select("*", $where, "id desc",6);
        
        /* [安全修复] 原为 "id=" . $_GET["id"] 裸拼接。虽然 route 对 GET 标量
         * 做了 add_slashes，但数字上下文无需引号即可注入（如 id=1 or 1=1），
         * update(hitnum+=1) 会命中全表。改用上面已 intval 的 $id。 */
        $where = "ifhidden='1' and id=" . $id;
        $this->conn->update(array("hitnum" => "+=1"), $where);
        endif;
        $data = $this->conn->get_one("*", $where, "id desc");
        // 文章不存在时，返回安全的空结构，避免前端访问 data[0] 抛错导致详情页白屏
        if (!$data) {
            echo json_encode(array("success"=>1,"data"=>array(),"feedArr"=>array(),"hotArr"=>array(),"nextArr"=>array(),"aboutArr"=>array()));
            return;
        }
        $data["cnt_short"]=strip_tags($data["cnt_short"]);
        $riqi=$data["riqi"];
        $data["riqi"]=str_replace("00:00:00","15:30:12",$riqi);
        //获取目录ID
        if($data){
            $cataidArr_=explode(",",$data["cataid"]);
            $mycataidArr=array();
            foreach($cataidArr_ as $a){
                $mycataidArr[]=str_replace("cataid","",$a);
            }
            //当前频道
            $where_="cataid=".$mycataidArr[0];
            $arr_=$this->conn_catalog->get_one("sort",$where_);
            $subject=$arr_["sort"]; 
            $parentidArr=array_reverse(explode(",",rtrim(pCataid($this->conn_catalog,$mycataidArr[0]),",")));//索引0 为顶级目录，索引1为目录根
        
            //当前路径
            $currootArr=$this->curroot($mycataidArr[0]);
            // 推荐
            if($this->pi=="p"){
                $cols="picdir_list,title,hitnum,cataid,id,cnt_short,riqi,bnum";
            }else{
                $cols="picdir_list,title,hitnum,cataid,id,cnt_short,riqi";
            } 
            
            // 推荐
            $hotArr=$this->getSetArr("hot",$this->mycataid,$this->conn,$cols);
            // 上一页下一页
            if (isset($_GET["id"])) {
                $mywhere=$this->genWhere($mycataidArr);
                $idNow = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
                $where="id<$idNow".$mywhere;
                $preArr = $this->conn->get_one("id,title,picdir_list", $where, "id desc");
    
                $where="id>$idNow".$mywhere;
                $nextArr = $this->conn->get_one("id,title,picdir_list", $where, "id asc");
            }
    
        }//ifdata
        
        // 留言
        $where="pid=".$data["id"];
        $feedArr=$this->conn_feedback->select("*",$where,"riqi desc");
        $num=count($feedArr);
        $data["feednum"]=$num;
        if($feedArr){
            $i=0;
            foreach($feedArr as $a){
                $hid=(int)$a["hid"];
                $hid=$hid?$hid:4;
                $arr_=$this->conn_h->get_one("name,picdir","id=$hid");
                $feedArr[$i]["picdir"]=$arr_["picdir"];
                $feedArr[$i]["name"]=$arr_["name"];
                $i++;
            }
        }
        
        //收藏:是否已收藏
        $data["favorate"]=1;
        $data["favid"]=0;
        if(isset($_GET["hid"])&&$_GET["hid"]){
            $pid=(int)$data["id"];
            $hid=(int)$_GET["hid"];
            $ifFavorate=$this->conn_favorate->get_one("*","pid=$pid and hid=$hid");
            if($ifFavorate){
                $data["favorate"]=0;
                $data["favid"]=$ifFavorate["id"];
            } 
        }
		$data=array($data);
		$nextArr=array($nextArr);
        $data=array(
            "data"=>$data,//详情数据
            "feedArr"=>$feedArr,//留言
            "hotArr"=>$hotArr,//热门推荐
            "nextArr"=>$nextArr,//下一篇
            "aboutArr"=>$aboutArr//相关新闻
            
        );
 
         echo  json_encode($data);
        
        // include parent::load_tpl($this->sortArr["tplnamedetail"]);
    }
    

    /*
     * cataList($curcataid)
     * # 给定目录ID，查找它的所有父目录和下一集目录
     * @curcataid 当前目录ID
     * @return array (cataArr:目录内容；parentidArr:每一行的当前目录ID（用于标红）;lastCataid 最后一个不为_的目录ID )
     *
     */
    private function getCataArr($curcataid)
    {
        
        //当前目录下的所有子目录 
         $mysubcataid = Cataid($this->conn_catalog, $curcataid);
        //当前目录下的所有父目录 
         $mypcataid = pCataid($this->conn_catalog, $curcataid);
         
         $parentidArr=array_reverse(explode(",",rtrim(pCataid($this->conn_catalog,$curcataid),",")));//索引0 为顶级目录，索引1为目录根
           
        $mArr = array(); 
        $i = 0; 
        foreach ($parentidArr as $parentid) {
    
            if ($parentid) {  
                $where_ = "ifm='1' and pi='".$this->pi."' and parentid=".$parentid;
                $data_ = $this->conn_catalog->select("*", $where_, "orderid asc");
                if ($data_) {
                    switch($i){
                        case 0:
                            $title="选择分类";
                            break;
                        case 1:
                            $title="二级分类";
                            break;
                        case 2:
                            $title="三级分类";
                            break;
                        case 3:
                            $title="四级分类";
                            break;
                        default:
                            $title="其它分类";
                            break;                           
                    }
                    $mArr[$i]["fieldname"] = "cataid";
                    $mArr[$i]["title"] = $title;
                    $mArr[$i]["subsort"] = $data_;
                }else {
                    break;
                } 
                $i++;
            }
    
        }
        return $mArr;
    }
    
    
    /*getShuxArr($curcataid)
     * #给定目录ID，向上查找是否有目录属性，有返回其值，无返回FALSE
     */
    private function getShuxArr($curcataid){
        //当前目录下的所有父目录 
         $mypcataid = pCataid($this->conn_catalog, $curcataid);         
         $pCataidArr=explode(",",rtrim(pCataid($this->conn_catalog,$curcataid),",")); 
            
        foreach($pCataidArr as $parentid){//从自身开始，向上查找目录属性，一旦找到则终止遍历并返回结果
            $where_="parentid=$parentid and ifm='0' and fieldname!=''";
            $data_=$this->conn_catalog->select("sort,ensort,fieldname,cataid",$where_);
            if($data_){
                $i=0;
                $shuxArr=array();
                foreach ($data_ as $a) {
                    $sort_ = $a["sort"];
                    $cataid_ = $a["cataid"];
                    $fieldname_ = $a["fieldname"];
                    $where_ = "parentid=$cataid_";
                    $sub_ = $this->conn_catalog->select("*", $where_, "orderid asc");
                    if ($sub_) {
                        $shuxArr[$i]["fieldname"] = $fieldname_;
                        $shuxArr[$i]["title"] = $sort_;
                        $shuxArr[$i]["subsort"] = $sub_;
                        $i ++;
                    }
                }
                
                return $shuxArr;
            }
        }
        return false;
    }

    /*
     * genWhere($cataidArr, $shuxArr=array())
     * #给定查询目录ID数组（和属性数组）生成条件语句
     * @retrun String (  where条件语句  ) 
     * @cataidArr 目录集或者目录ID
     * @shuxArr 属性内容
     *  
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
                
                $str_ .= " find_in_set( '$v_',cataid) or ";
            }
           
            $where .= rtrim($str_, "or ") . ")";
            }else{
                $where=" and (find_in_set( '$cataidArr',cataid))";
            }
        
        // 属性
        if ($shuxArr) {
            foreach ($shuxArr as $a) {
                $fieldname_ = $a["fieldname"];
                if (isset($_GET[$fieldname_])) {
                    $v = $_GET[$fieldname_];
                    if($v!="_"){ 
                        $where .= " and find_in_set('$v',$fieldname_) ";
                    }else{ 
                        $where .= " and $fieldname_ like '%$v%'";
                    }
                }
            }
        }
        return  $where;
    }

    
    /*
     * getSetArr($set,$curCataid,$conn,$cols,$num='3',$rank='0'
     * # 获取设置（头条，置顶，推荐，热点等）的值
     * @set 设置类型
     * @curCataid 当前目录ID
     * @conn 内容链接表（文档，商品）
     * @cols查找的字段字符串
     * @num 默认寻找个数:如果赋予参数NUM值，则优先使用该值
     * @rank 默认匹配模式
     * 
     */
    
    private function getSetArr($set,$curCataid,$conn,$cols,$num='0',$rank='0'){

        //当前目录下的所有子目录
        $mysubcataid = Cataid($this->conn_catalog, $curCataid);
        //当前目录下的所有父目录
        $mypcataid = pCataid($this->conn_catalog,$curCataid);
        
        $pCataidArr=explode(",",rtrim($mypcataid,",")); 
        $root=$pCataidArr[count($pCataidArr)-2];
        $subCataidArr=explode(",",rtrim($mysubcataid,","));         
        $allCataidArr=explode(",",rtrim(Cataid($this->conn_catalog, $root),",")); 
        
        foreach($pCataidArr as $parentid){//从自身开始，向上查找目录属性，一旦找到则终止遍历并返回结果
            $where_="num_one!=''  and cataid=$parentid";
            $data_=$this->conn_catalog->get_one("num_one,one_set,num_head,head_set,num_hot,hot_set,num_redian,redian_set",$where_);
            if($data_){
                 $num_one=$data_["num_one"];
                 $one_set=$data_["one_set"]; 
                 $num_head=$data_["num_head"];
                 $head_set=$data_["head_set"]; 
                 $num_hot=$data_["num_hot"];
                 $hot_set=$data_["hot_set"]; 
                 $num_redian=$data_["num_redian"];
                 $redian_set=$data_["redian_set"]; 
                 if($num_one||$num_head||$num_hot||$num_redian)
                 break;
            }
        }
        $where="ifhidden='1'";
        $num_=0;
        switch($set){
            case "one":
                $num_=$num_one;
                $rank=$one_set;
                $where.=" and ifone='0'";
                break;
            case "head":
                $num_=$num_head;
                $rank=$head_set;
                $where.=" and ifhead='0'";
                break;
            case "hot":
                $num_=$num_hot;
                $rank=$hot_set;
                $where.=" and ifhot='0'";
                break;
            case "redian":
                $num_=$num_redian;
                $rank=$redian_set;
                $where.=" and 1=1";
                break;
        }
        $subwhere="";
        switch($rank){
            case '0'://标准:相同目录+子孙目录 
                $subwhere = $this->genWhere($subCataidArr);
                break;
            case '1'://严格：相同目录
                $subwhere = $this->genWhere($this->mycataid);
                break;
            case '2'://宽松：同一根目录
                $subwhere = $this->genWhere($allCataidArr);
                break;                
        }
        $where.=$subwhere;
        if($num>0&&is_numeric($num)){
            $num_=$num;
        }
        if($num_==""||!is_numeric($num_)){
            $num_=6;
        }
        $data=$conn->select($cols,$where,"orderid desc",$num_); 
        return $data;
    }
    
    /*
     * curroot($curCataid)
     * # 返回路径 
     * @return array cataidArr["cataid"=>"sort"]
     *
     */
    private function curroot($curCataid){
    
        $pCataidArr=array_reverse(explode(",",rtrim(pCataid($this->conn_catalog,$curCataid),",")));//索引0 为顶级目录，索引1为目录根

        $currootArr=array(); 
        if($pCataidArr){
            $cataidStr_="";
            $i=0;
            foreach($pCataidArr as $a){
                if($a){//抛弃顶级目录
                     
                    $cataidStr_.="&cataid=$a";
                    $where_="cataid=$a";
                    $data_=$this->conn_catalog->get_one("sort",$where_);
                    $sort=$data_["sort"]; 
                    $currootArr[$a]=$sort;
                    $i++;
                }
            }
            $curroot=ltrim($curroot," -> ");
        }
        return $currootArr;
    }
    
    /*
     * genOrderbyAndPagestr($orderby = "",$orderbyFieldArr=array())
     * #生成排序字符串及分页链接A追加字符串
     * @return array( orderby排序字符串,pagestr分页字符串
     * @orderby 默认排序方式
     * @$orderbyFieldArr 考虑到的排序字段数组 
     */
    private function genOrderbyAndPagestr($orderby = "", $orderbyFieldArr = array())
    {
        $pageArr = explode("&", rtrim($_SERVER["QUERY_STRING"], "&")); // 当前链接的数组
        foreach ($pageArr as $a) {
            $arr_ = explode("=", $a);
            $k = trim($arr_[0]);
            $v = trim($arr_[1]);
            if ($k != 'page') {
                $pagestr .= $k . "=" . $v . "&"; // 通过当前字符串数组产生的分页字符串，用于加载到分页字符中的A链接，如：?c=p&cataid=12&cataid=15&shux1=yingyong12...
            }
        }
        $pagestr = rtrim($pagestr, "&");
        $pagestr = str_replace("&order=asc", "", $pagestr);
        $pagestr = str_replace("&order=desc", "", $pagestr);
    
        foreach ($orderbyFieldArr as $a) {
            $pagestr = str_replace("&orderby=$a", "", $pagestr);
            if (isset($_GET["orderby"]) && $_GET["orderby"] == $a) {
                $pagestr .= "&orderby=" . $_GET["orderby"];
                if (isset($_GET["order"]) && trim($_GET["order"]) == "asc") {
                    $pagestr .= "&order=asc";
                    $orderby = "$a asc";
                } else {
                    $pagestr .= "&order=desc";
                    $orderby = "$a desc";
                }
            }
        }
        return (array(
            "orderby" => $orderby,
            "pagestr" => $pagestr
        ));
    }
    
  
    public function ajax_feedback(){
        /*
         * pname 文章标题 / pid / hid / hname / cataid / content
         *
         * [安全修复]
         * 1. 原实现无任何鉴权 + 直接 insert($_POST["data"])：匿名可灌评论，
         *    且能伪造 hid/hname 冒充他人，还能顺带写入表内任意列。
         * 2. 现在：必须登录；hid/hname 一律以服务端 session + 会员表为准；
         *    字段白名单；按 IP 限流（10 条 / 10 分钟）；正文长度上限。
         */
        $hid = $this->requireLogin();

        if (!bxq_rl_check('feedback', 10, 600)) {
            $this->jsonOut(array("success" => 429, "msg" => "评论过于频繁，请稍后再试"));
            return;
        }

        $pid    = $this->postInt("pid");
        $cataid = $this->postInt("cataid");
        $content = isset($_POST["data"]["content"]) && !is_array($_POST["data"]["content"])
                 ? trim($_POST["data"]["content"]) : "";
        if ($pid <= 0 || $content === "") {
            $this->jsonOut(array("success" => 1, "msg" => "参数不完整"));
            return;
        }
        if (mb_strlen($content, "UTF-8") > 1000) {
            $content = mb_substr($content, 0, 1000, "UTF-8");
        }

        // 文章标题与会员昵称都从库里取，不采信客户端
        $iRow = $this->conn_i->get_one("title", "id=$pid");
        if (!$iRow) {
            $this->jsonOut(array("success" => 1, "msg" => "文章不存在"));
            return;
        }
        $hRow = $this->conn_h->get_one("hname,name", "id=$hid");
        $hname = $hRow ? ($hRow["name"] ? $hRow["name"] : $hRow["hname"]) : "";

        $conn_feedback=konecms::load_model_class("feedback");
        $row = array(
            "pname"   => $iRow["title"],
            "pid"     => $pid,
            "cataid"  => $cataid,
            "hid"     => $hid,
            "hname"   => $hname,
            "content" => $content,
            "riqi"    => date("Y-m-d H:i:s"),
        );
        $conn_feedback->insert($row);
        $id = $conn_feedback->insert_id();
        bxq_rl_hit('feedback', 600);

        $arr["success"] = $id ? 0 : 1;
        $this->jsonOut($arr);
    }
    
      
    public function ajax_site(){
        /*
         * 网站收录申请（前台 /apply）。业务上允许匿名提交，故不加登录校验，
         * 但必须堵住以下三个原始漏洞：
         *
         * [安全修复]
         * 1. insert($_POST["data"]) 批量赋值 —— 提交者可自行写入 ifok / ifchecked
         *    等审核控制列，直接把自己的外链「审核通过」并挂上首页友链。
         *    现改为字段白名单，审核状态由后台决定。
         * 2. base64_image_content() 无条件调用 —— 传任意字符串会写垃圾文件；
         *    现仅当值确实是 data:image/... base64 时才落盘，其余按普通 URL 处理。
         * 3. 无限流 —— 匿名接口可被脚本灌库。现按 IP 限 5 次 / 10 分钟。
         */
        if (!bxq_rl_check('site_apply', 5, 600)) {
            $this->jsonOut(array("success" => 429, "msg" => "提交过于频繁，请稍后再试"));
            return;
        }

        $row = $this->pickFields(array(
            "sitename", "short", "url", "sort", "alexa", "lxr", "tel"
        ));
        if (!isset($row["sitename"]) || trim($row["sitename"]) === "") {
            $this->jsonOut(array("success" => 1, "msg" => "请填写网站名称"));
            return;
        }
        foreach ($row as $k => $v) {
            $v = trim($v);
            if (mb_strlen($v, "UTF-8") > 500) {
                $v = mb_substr($v, 0, 500, "UTF-8");
            }
            $row[$k] = $v;
        }

        $picdir = isset($_POST["data"]["picdir"]) && !is_array($_POST["data"]["picdir"])
                ? trim($_POST["data"]["picdir"]) : "";
        if ($picdir !== "" && stripos($picdir, "data:image/") === 0) {
            $picdir = base64_image_content($picdir, "konecms_ups/k/image", "/service/");
        } elseif ($picdir !== "" && !preg_match('#^(https?:)?/[^\s"\'<>]{0,300}$#i', $picdir)) {
            $picdir = ""; // 既不是 base64 也不是合法 URL/路径，丢弃
        }
        $row["picdir"] = $picdir;
        $row["riqi"]   = date("Y-m-d H:i:s");

        $conn_site=konecms::load_model_class("site");
        $conn_site->insert($row);
        $id = $conn_site->insert_id();
        bxq_rl_hit('site_apply', 600);

        $arr["success"] = $id ? 0 : 1;
        $this->jsonOut($arr);
    }

    public function ajax_favorate(){
        /*
         * [安全修复]
         * 1. 原实现 hid 取自 POST —— 任何人都能替他人收藏（越权写）。现取 session。
         * 2. pid/hid 裸插值进 WHERE —— 现全部整型强转。
         * 3. insert($_POST["data"]) 批量赋值 —— 现字段白名单。
         * 4. 原来 echo json_encode($_POST["data"]) 把请求体回显（反射型输出，
         *    且前端判断的是 res.success，回显根本不含 success，收藏状态永远判错）。
         *    现返回规范 JSON。
         */
        $hid = $this->requireLogin();
        $arr["success"]=1;
        $pid    = $this->postInt("pid");
        $cataid = $this->postInt("cataid");
        if ($pid <= 0) {
            $this->jsonOut(array("success" => 1, "msg" => "参数不完整"));
            return;
        }

        $data_=$this->conn_favorate->get_one("*","pid=$pid and hid=$hid");
        if($data_){
            // 已收藏，视为成功（幂等），并回传已有记录 id 供前端取消收藏使用
            $this->jsonOut(array("success" => 0, "favid" => (int)$data_["id"], "repeat" => 1));
            return;
        }
        $data2_=$this->conn_i->get_one("title","id=$pid");
        if(!$data2_){
            $this->jsonOut(array("success" => 1, "msg" => "文章不存在"));
            return;
        }
        $row = array(
            "pid"    => $pid,
            "cataid" => $cataid,
            "hid"    => $hid,
            "pname"  => $data2_["title"],
            "riqi"   => date("Y-m-d H:i:s"),
        );
        $this->conn_favorate->insert($row);
        $id = $this->conn_favorate->insert_id();
        if($id){
            $arr["success"]=0;
            $arr["favid"]=(int)$id;
        }
        $this->jsonOut($arr);
    }
    

      /*
     * 专家列表
     */
    function getExpertList()
    { 
        $where = "ifok='0' and ifauthor='0'";
        $cols="*";
        $data = $this->conn_h->select($cols, $where, "riqi desc");
        foreach ($data as &$row) unset($row["pwd"]); // 安全加固：不返回密码哈希
        echo json_encode($data);
        // include parent::load_tpl("h/h_myfeedback");
    }
    
      /*
     *专家排行
     */
    function getExpertListRank()
    { 
        $where = "ifok='0' and ifauthor='0'";
        $cols="*";
        $zhuanjiaArr=$this->conn_h->select($cols,$where,"hitnum desc",5);
        foreach ($zhuanjiaArr as &$row) unset($row["pwd"]); // 安全加固：不返回密码哈希

        if($_POST["data"]["hid"]){
            $myhid=(int)$_POST["data"]["hid"]; // [安全修复] 裸插值 myhid=$myhid，强转整型
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
        
        echo json_encode($zhuanjiaArr);
        // include parent::load_tpl("h/h_myfeedback");
    }

      /*
     * 推荐专家
     */
    function getExpertListHot()
    { 
        $where = "ifok='0' and ifauthor='0' and ifhot='0'";
        $cols="*";
	  $zhuanjiaArr=$this->conn_h->select($cols,$where,"hitnum desc",6);
	  foreach ($zhuanjiaArr as &$row) unset($row["pwd"]); // 安全加固：不返回密码哈希

	  $i=0; 
	  

	  if($_POST["data"]["hid"]){
	      $myhid=(int)$_POST["data"]["hid"]; // [安全修复] 裸插值 myhid=$myhid，强转整型
	      $i=0;
	      foreach($zhuanjiaArr as $a){
	          $hid=$a["id"];
	          $data_=$this->conn_mycarehid->get_one("*","carehid=$hid and myhid=$myhid");
	          $num_wenzhang=$this->conn_i->count("hid=$hid");
	          $zhuanjiaArr[$i]["num_wenzhang"]=$num_wenzhang;
	          if($data_){
	              $zhuanjiaArr[$i]["ifover"]='0';
	          }else{
	              $zhuanjiaArr[$i]["ifover"]='1';
	          }
	          $i++;
	      }
	  }
        echo json_encode($zhuanjiaArr);
        // include parent::load_tpl("h/h_myfeedback");
    }


  /*
     * 专栏
     */
    function getExpertContentList()
    { 
        $where="parentid=25";
        $cols="cataid,sort";
        $cataArr = $this->conn_catalog->select($cols, $where, "cataid asc");

         
        foreach($cataArr as $a){
            $cataid="cataid".$a["cataid"];
            $where = "ifchecked='0' and ifpublic='0' and hid !=0 and ifhidden='1' and cataid like '%$cataid%'";
            $cols="id,title,picdir_list,short,cataid,keywords,cnt_short,ifhot,hid,hitnum";
            $data[$cataid] = $this->conn_i->select($cols, $where, "riqi desc");
        }
       
        foreach($data as $k=>$a2){ 
            foreach($a2 as $k2=>$a){
            $data[$k][$k2]["short"]= strip_tags($a["short"]);
            $data[$k][$k2]["cnt_short"]= strip_tags($a["cnt_short"]);
            $hid=$a["hid"];
            if($hid){
                $arr_=$this->conn_h->get_one("*","id=$hid");
                $data[$k][$k2]["picdir_h"]=$arr_["picdir"];
                $data[$k][$k2]["nickname"]=$arr_["name"]; 
            } 
        }
        }
        $rtnArr=array("cataArr"=>$cataArr,"data"=>$data);
       
        echo json_encode($rtnArr);
        // include parent::load_tpl("h/h_myfeedback");
    }

    function getHotExpertContentList(){
        $pcataid=25;
        $mycataid= rtrim(Cataid($this->conn_catalog, 25),",");
        $arr_=explode(",",$mycataid);
        $subwhere="";
        foreach($arr_ as $a){
            $subwhere.="'cataid".$a."',";
        }
        $subwhere=rtrim($subwhere,",");
         $where = "ifchecked='0' and hid !=0 and ifhidden='1' and ifhot='0' and cataid in ($subwhere)";
        $cols="id,title,picdir_list,short,cataid,keywords,cnt_short,hid,hitnum";
        $data  = $this->conn_i->select($cols, $where, "riqi desc");
        $i=0;
        foreach($data as $a){
        $data[$i]["picdir_h"]="";
        $data[$i]["nickname"]="";
        $data[$i]["short"]= strip_tags($a["short"]);
        $data[$i]["cnt_short"]= strip_tags($a["cnt_short"]);
        $hid=$a["hid"];
        if($hid){
            $arr_=$this->conn_h->get_one("*","id=$hid");
            $data[$i]["picdir_h"]=$arr_["picdir"];
            $data[$i]["nickname"]=$arr_["name"];
        
        }
        $i++;
        }
        //echo $this->conn_i->sql();
        echo json_encode($data);
    }
    
    //导航
    function getSiteList()
    {
        $where="parentid=62";
        $cols="cataid,sort";
        $cataArr = $this->conn_catalog->select($cols, $where, "cataid asc");
        foreach($cataArr as $a){
            $pcataid=$a["cataid"];
            $where="parentid=$pcataid";
            $cols="cataid,sort";
            $subcataArr = $this->conn_catalog->select($cols, $where, "cataid asc");
             
            foreach($subcataArr as $b){
                $cataid=$b["cataid"]; 
                $where = "ifchecked='0'  and cataid =$cataid";
                $cols="*";
                $data["a".$pcataid]["b".$cataid] = $this->conn_site->select($cols, $where, "riqi desc"); 
            }
        }
      
        echo json_encode($data);
        // include parent::load_tpl("h/h_myfeedback");
    }
    
    
    /*
     * 专家黄页
     */
    function getExpert(){
        if (isset($_GET["hid"])) {
            $hid=(int)$_GET["hid"]; // [安全修复] 裸插值 id=$hid
            $this->conn_h->update(array("hitnum" => "+=1"), "id=$hid");
            //会员信息
            $hArr=$this->conn_h->get_one("*","id=$hid");
            unset($hArr["pwd"]); // 安全加固：不向前端返回密码哈希，避免敏感信息泄露
            $sort=$hArr["sort"];
            switch($sort){
                case 'geren':
                    $hArr["sort"]="个人";
                    break;
                case 'meiti':
                    $hArr["sort"]="媒体";
                    break;
                default:
                    $hArr["sort"]="企业";
                    break;
            }
            //粉丝数量
             $hArr["num_fans"]=$this->conn_mycarehid->count("carehid=$hid");
             $hArr["num_contents"]=$this->conn_i->count("hid=$hid");
            $hArr=array($hArr);
            
	  if($_POST["data"]["hid"]){
	      $myhid=(int)$_POST["data"]["hid"]; // [安全修复] 裸插值 myhid=$myhid，强转整型
                $i=0;
                foreach($hArr as $a){
                    $hid=$a["id"];
                    $data_=$this->conn_mycarehid->get_one("*","carehid=$hid and myhid=$myhid");
                    if($data_){
                        $hArr[$i]["ifover"]='0';
                    }else{
                        $hArr[$i]["ifover"]='1';
                    }
                    $i++;
                }
            }
            
            
            //发布的文章
            $iArr=$this->conn_i->select("*","hid=$hid","id desc",10);
            $rtnArr=array("hArr"=>$hArr,"iArr"=>$iArr); 
            echo json_encode($rtnArr);
        }
    
    }
    

    public function getMore(){
    
        /* [安全修复] cataid / id 均来自未过滤的 POST 数组，且被裸插值进
         * "id<$id" 以及 Cataid() 的 SQL。这里统一整型化。 */
        $cataid_= isset($_POST["data"]["cataid"]) && !is_array($_POST["data"]["cataid"])
                ? $_POST["data"]["cataid"] : "";
        $arr_=explode(",",$cataid_);
        $cataid_=(int)str_replace("cataid","",$arr_[0]);
        $id=$this->postInt("id");
        if ($id <= 0) { $id = PHP_INT_MAX; } // 首屏未带 id 时不过滤 id，避免空列表
         
        $where=" ifhidden='1' and id<$id ";
        $mysubcataid = Cataid($this->conn_catalog, $cataid_);
        $cataidArr_=explode(",",rtrim($mysubcataid,","));//索引0 为顶级目录，索引1为目录根
        $subwhere = $this->genWhere($cataidArr_);
        $where .= $subwhere;
        $dataArr=$this->conn_i->select("id,cornertitle,keywords,cataid,source,short,riqi,hitnum,title, picdir_list",$where,"orderid desc",10);
         
        echo json_encode($dataArr);
    }
    
    /*
     * 利好 / 利空 投票（快讯页匿名可投）。
     *
     * [安全修复]
     * 1. $id 原为 $_POST["data"]["id"] 裸插值进 "id=$id"。route.class.php 对
     *    POST 数组完全不过滤，post_check 黑名单又不拦 OR / = ，
     *    传 id=0 or 1=1 即可把全表 lihao 批量自增 —— 数据可被一次刷爆。
     *    现统一整型强转。
     * 2. 无任何防刷，单 IP 可无限刷票。现按 IP 限 60 次 / 10 分钟。
     * 3. 原来没有任何返回体，前端 .then 拿到空串。现返回 JSON。
     */
    private function voteField($field){
        $id   = $this->postInt("id");
        $type = $this->postInt("type");
        if ($id <= 0) {
            $this->jsonOut(array("success" => 1, "msg" => "参数错误"));
            return;
        }
        if (!bxq_rl_check('vote', 60, 600)) {
            $this->jsonOut(array("success" => 429, "msg" => "操作过于频繁"));
            return;
        }
        if ($type) {
            $this->conn_i->update(array($field => "-=1"), "id=$id and $field>0");
        } else {
            $this->conn_i->update(array($field => "+=1"), "id=$id");
        }
        bxq_rl_hit('vote', 600);
        $row = $this->conn_i->get_one("$field", "id=$id");
        $this->jsonOut(array("success" => 0, $field => $row ? (int)$row[$field] : 0));
    }

    //利好
    public function ajax_set_lihao(){
        $this->voteField("lihao");
    }
    //利空
    public function ajax_set_likong(){
        $this->voteField("likong");
    }
}

?>