<?php
$m = new mysqli('localhost','root','','k_k3_bixingqiu');
if($m->connect_error){ echo "CONN ERR ".$m->connect_error."\n"; exit; }
$m->set_charset('utf8');
$res = $m->query("SELECT * FROM h_tb WHERE ifok='0' and ifauthor='0' ORDER BY riqi desc");
$data = [];
while($row = $res->fetch_assoc()) $data[] = $row;
echo "rows=".count($data)."\n";
$json = json_encode($data);
if($json === false){ echo "JSON_ENCODE_FAILED errno=".json_last_error()." msg=".json_last_error_msg()."\n"; }
else { echo "JSON_OK len=".strlen($json)."\n"; echo substr($json,0,160)."\n"; }
// also test a single value utf8 check
if(isset($data[0])){ echo "first title raw bytes: "; var_dump($data[0]['name'] ?? 'n/a'); }
