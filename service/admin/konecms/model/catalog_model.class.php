<?php
konecms::load_lib_class('model', '', 0);
class catalog_model extends model {
	public function __construct() {
		$this->db_file =konecms::load_config('dbconn');
		$this->db_name = 'default';
		$this->table_name = 'catalog_tb';
		parent::__construct();
	}
	/*
	 * 列出目录管理（仅用于目录管理页）
	 */
	public function list_catalog_manage($data, $pId=0, $level = 1)
	{
	    $html = '';
	    foreach ($data as $k => $v) {
	        $flag = "";
	        if ($v['parentid'] == $pId) {
	            if ($pId == 0) {
	                $level = 0;
	            } else {
	                $flag = "├─";
	            }
	            $flag = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level).$flag;
	            $html .= "<tr>";
	            $html .="<td align=center>".$v["cataid"]."</td>";
	            $html .="<td align=left>";
	            if($level==0) $html.="<strong>";
	            $html.=" $flag " . $v['sort'];
	            if($level==0) $html.="</strong>";
	            $html.="</td>";
	            $html .="<td align=center>".$v["mtitle"]."</td>";
	            $html .="<td align=center>";
	            $html .="<a href=?m=admin&c=catalog&a=manage_add&cataid=".$v["cataid"].">添加子目录</a>";
	            $html.="&nbsp;&nbsp;|&nbsp;&nbsp;";
	            $html .="<a href=?m=admin&c=catalog&a=manage_update&cataid=".$v["cataid"].">修改</a>";
	            $html.="&nbsp;|&nbsp;";
	            $html .='<a onclick=catalog_del(this,"?m=admin&c=catalog&a=manage_del&cataid='.$v["cataid"].'","'.$v["sort"].'") href="javascript:;" >删除</a>';
	            $html.="&nbsp;|&nbsp;";
	            $html .="<a href=?m=admin&c=catalog&a=manage_move&cataid=".$v["cataid"].">移动</a>";
	            $html .= "</td>";
	            $html.="</tr>";
	            $html = $html . $this->list_catalog_manage($data, $v['cataid'], $level + 1);
	        }
	    }
	    return $html;
	}
}
?>