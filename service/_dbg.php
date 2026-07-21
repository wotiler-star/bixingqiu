<?php
$conn = new mysqli('localhost','root','','k_k3_bixingqiu');
$sql = "SELECT * FROM h_tb WHERE ifok='0' and ifauthor='0' ORDER BY riqi desc";
// 1) 不设置字符集
$res = $conn->query($sql);
$rows=array(); while($r=$res->fetch_assoc()) $rows[]=$r;
echo "NO-NAMES json_encode: "; var_dump(json_encode($rows));
echo "\n";
// 2) 设置 utf8
$conn->set_charset('utf8');
$res2 = $conn->query($sql);
$rows2=array(); while($r=$res2->fetch_assoc()) $rows2[]=$r;
echo "UTF8 json_encode: "; var_dump(json_encode($rows2));
echo "\nUTF8 sample: "; echo substr(json_encode($rows2, JSON_UNESCAPED_UNICODE),0,160);
echo "\n";
