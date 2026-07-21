<?php
class page {
	private $amount; // 总记录数
	private $page; // 当前页
	private $pageSize; // 每页显示条数
	public $pageCount; // 总页面数
	private $arr; // 其他URL参数
	private $pstr; // 分页字符串
	private $getpage; // 分页参数，默认为 page[=1....][实现一个页面多个分页字符串]
	function page($amount, $pageSize = 10, $getpage = "", $arr = array()) {
		if ($getpage == "")
			$getpage = "page";
		$this->getpage = $getpage;
		$this->url = "";
		$this->pstr = "";
		$this->page = isset ( $_GET [$getpage] ) ? $_GET [$getpage] : 1;
		if ($amount <= 0) {
			exit ( "总记录数为空" );
		}
		 $str = "&";
		 $this->pageCount = ceil ( $amount / $pageSize );
		if ($arr != null) {
		    if(is_array($arr)){ 
		         
		        foreach ( $arr as $key => $value ) {
		            $str .= $key . "=" . $value . "&";
		        }
		        $str=rtrim ( $str, "&" );
		    }else{
		          $str.=$arr;
		    }
			$this->url = $str;
		}
	}
	function show($sort) {
		switch ($sort) {
			case 0 :
				$this->number (); // 纯数字
				break;
			case 1 :
				return $this->pageStr (); // 纯文本：首页，上一页，下一页，末页
				break;
			case 2 :
				$this->numberAndPageStr (); // 文本与全部数字
				break;
			case 3 :
				$this->partNumberAndPageStr (); // 文本与每页固定10个数目的数字
				break;
			case 4 :
				return $this->perfect ();
		}
	}
	private function number() {
		for($i = 1; $i <= $this->pageCount; $i ++) {
			$link = $i == $this->page ? "linking" : "unlink";
			$str .= "<a href=?$this->getpage=$i#here class=$link>$i</a>";
		}
		return $str;
	}
	private function pageStr() {
		$this->firstPage ();
		$this->prevPage ();
		$this->nextPage ();
		$this->endPage ();
		return $this->pstr;
	}
	private function firstPage() {
		$this->pstr .= "<a href=?$this->getpage=1" . $this->url . "#here title=" . L ( "first" ) . ">" . L ( "first" ) . "</a>";
	}
	private function endPage() {
		$amount = $this->pageCount;
		$this->pstr .= "<a href=?$this->getpage=$amount" . $this->url . "#here title=" . L ( "last" ) . ">" . L ( "last" ) . "</a>";
	}
	private function prevPage() {
		if ($this->page != 1) {
			$prev = $this->page - 1;
			$this->pstr .= "<a href=?$this->getpage=$prev" . $this->url . "#here title=" . L ( "previous" ) . ">" . L ( "previous" ) . "</a>";
		} else {
			$this->pstr .= "<a href=?$this->getpage=1" . $this->url . "#here title=" . L ( "previous" ) . ">" . L ( "previous" ) . "</a>";
		}
	}
	private function nextPage() {
		$amount = $this->pageCount;
		if ($this->page != $amount) {
			$next = $this->page + 1;
			$this->pstr .= "<a href=?$this->getpage=$next" . $this->url . "#here title=" . L ( "next" ) . ">" . L ( "next" ) . "</a>";
		} else {
			$this->pstr .= "<a href=?$this->getpage=$amount" . $this->url . "#here title=" . L ( "next" ) . ">" . L ( "next" ) . "</a>";
		}
	}
	private function numberAndPageStr() {
		$this->prevPage ();
		$this->number ();
		$this->nextPage ();
	}
	private function partNumberAndPageStr() {
		$this->prevPage ();
		$this->partNumber ();
		$this->nextPage ();
	}
	private function partNumber() {
		$int = floor ( $this->page / 10 );
		$int2 = floor ( $this->pageCount / 10 );
		$count = $int == $int2 ? $this->pageCount : $int * 10 + 9;
		for($i = $int == 0 ? 1 : $int * 10; $i <= $count; $i ++) {
			$link = $i == $this->page ? "linking" : "unlink"; 
			echo "<a href=?$this->getpage=$i#here class=$link>$i</a>";
		}
	}
	private function perfect() {
		$this->prevPage ();
		$this->perfectNumber ();
		$this->nextPage ();
		return $this->pstr;
	}
	private function perfectNumber() {
		if ($this->pageCount <= 10) {
			for($i = 1; $i <= $this->pageCount; $i ++) {
				$link = $i == $this->page ? "linking" : "unlink";
				$this->pstr .= "<a href=?$this->getpage=$i" . $this->url . "#here class=$link>$i</a>";
			}
		} elseif ($this->page > 10) {
			$from = $this->page - 4;
			$to = $this->pageCount - 5 > $this->page ? $this->page + 5 : $this->pageCount;
			for($i = $from; $i <= $to; $i ++) {
				$link = $i == $this->page ? "linking" : "unlink";
				$this->pstr .= "<a href=?$this->getpage=$i" . $this->url . "#here class=$link>$i</a>";
			}
		} else {
			for($i = 1; $i <= 10; $i ++) {
				$link = $i == $this->page ? "linking" : "unlink";
				$this->pstr .= "<a href=?$this->getpage=$i" . $this->url . "#here class=$link>$i</a>";
			}
		}
	}
}
?>