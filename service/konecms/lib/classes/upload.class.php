<?php

class upload
{
    // 保存路径
    private $uploaddir;
    // 上传的图片文件地址
    public $imgFile;
    // 图像资源
    private $sim;
    // 图片宽度
    private $sw;
    // 图片高度
    private $sh;
    // 缩放后的文件名称
    public $resizedFile;
    // 指定尺寸后的文件名称
    public $tosizedFile;
    // 图片加入水印文字
    public $addtxtFile;
    // 图片加入logo
    public $addpicFile;
    // 反馈上传结果提示:0成功，1.并未上传；2.格式不准确；3.大小不合要求；4.系统出错（权限）
    public $rstno;
    // 提示信息
    public $MSG;
    // 当前上传文件大小
    public $picsize;
    // 格式
    public $type;
    // 上传后的文件名称
    public $name;

    function __construct()
    {
        $this->MSG = "";
        $this->PICSIZE = "";
        $this->imgFile = array();
        $this->rstno = array();
    }

    private function mydir($dirArr)
    { // 生成嵌套路径，上传的文件可以放入到不同的文件中
        $path = "";
        $size = count($dirArr);
        for ($i = 0; $i < $size; $i ++) {
            if ($i == 0) {
                $path .= $dirArr[0];
                if (! file_exists($path)) {
                    mkdir($path);
                }
            } else {
                $path .= "/" . $dirArr[$i];
                if (! file_exists($path)) {
                    mkdir($path);
                }
            }
        }
        return $path;
    }

    /*
     * 上传表单为数组
     */
    function upload($uploaddir = "uploadfiles")
    {
        if (isset($_FILES) && count($_FILES) > 0) {
             /*  echo "<pre>";
            print_r($_FILES);   */  
            $dir = trim($uploaddir, "/");
            $dirArr = explode("/", $dir);
            $this->uploaddir = $this->mydir($dirArr);
            
            foreach ($_FILES as $picform => $vArr) {
                if ($picform == "file")
                    $picform = "image_b";
                $myfile = array();
                $tmpfile = array();
                $count = count($vArr["tmp_name"]);
 
                for ($i = 0; $i < $count; $i ++) {
                    $name = $vArr["name"][$i];
                    $type = $vArr["type"][$i];
                    $tmpfile[] = $vArr["tmp_name"][$i];
                    $error = $vArr["error"][$i];
                    $size = $vArr["size"][$i];
                    $mytype = strtolower(ltrim(strrchr($name, "."), "."));
                    // 存在上传资源时，检验资源类型
                    if ($name) {
                        if ($picform == "dataurl") { // 资源类
                            if (! in_array($mytype, array(
                                "doc",
                                "pdf",
                                "docx",
                                "zip",
                                "rar"
                            ))) {
                                // 反馈上传结果提示:0成功，1.并未上传；2.格式不准确；3.大小不合要求；4.系统出错（权限）
                                $this->rstno[$picform] = 2;
                                break;
                            }
                        } else {
                            if (! in_array($mytype, array( // 图像类
                                "jpg",
                                "jpeg",
                                "gif",
                                "png",
                                "bmp"
                            ))) {
                                // 反馈上传结果提示:0成功，1.并未上传；2.格式不准确；3.大小不合要求；4.系统出错（权限）
                                    $this->rstno[$picform] = 2;
                                    break; // 同一表单下有任意一项不足则终止该项资源上传（不影响兄弟表单上传）
                                }
                                // 校验真实图片内容，防止伪装图片上传造成 RCE
                                if (! @getimagesize($tmpfile[$i])) {
                                    $this->rstno[$picform] = 2;
                                    break;
                                }
                            }
                        } // if name
                    
                    $path = $this->uploaddir;
                    $myname = "konecms" . time() . rand(100, 900);
                    if ($name) {
                        $myfile[] = $path . "/" . $myname . "." . $mytype;
                    } else {
                        $myfile[] = '0';
                    }
                     
                } // for
                
                // B.3 执行上传
                
                    $i = 0;
                    foreach ($myfile as $a) {
                        if ($a != '0') {
                            if (move_uploaded_file($tmpfile[$i], $myfile[$i])) {
                                $this->rstno[$picform] = 0;
                            } else {
                                $this->rstno[$picform] = 4;
                            }
                        }
                        $i++;
                    }
                
                // B.4 提供调用接口
                $myfile =str_replace("../","./",implode("*", $myfile));
                
                $this->imgFile[$picform] = $myfile;
            }
        }
    }

    function reSize($per)
    { // 按比例生成文件
        if ($this->webFile != "" && $this->webFile != "./images/nopic.jpg") {
            if (empty($per))
                $per = 1;
            $this->sim();
            $this->size();
            $myW = $this->sw * $per;
            $myH = $this->sh * $per;
            $myim = imagecreatetruecolor($myW, $myH);
            imagecopyresampled($myim, $this->sim, 0, 0, 0, 0, $myW, $myH, $this->sw, $this->sh);
            $per *= 100;
            $MSG = "<font color=green>按比例生成成功！</font>";
            $this->resizedFile = str_replace("../", "./", $this->newFile("reSized"));
            $MSG = $this->image($myim, $this->type, $this->newFile("reSized"), $MSG);
            imagedestroy($myim);
            imagedestroy($this->sim);
            $this->MSG .= $MSG . "&nbsp;";
        }
    }

    function toSize($new_width, $new_height)
    {
        if ($this->webFile != "" && $this->webFile != "./images/nopic.jpg") {
            $this->sim();
            $this->size();
            $src_img = $this->sim;
            $w = $this->sw;
            $h = $this->sh;
            $ratio_w = 1.0 * $new_width / $w;
            $ratio_h = 1.0 * $new_height / $h;
            $ratio = 1.0;
            if (($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
                if ($ratio_w < $ratio_h) {
                    $ratio = $ratio_h;
                } else {
                    $ratio = $ratio_w;
                }
                $inter_w = (int) ($new_width / $ratio);
                $inter_h = (int) ($new_height / $ratio);
                $inter_img = imagecreatetruecolor($inter_w, $inter_h);
                imagecopy($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h);
                $new_img = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height, $inter_w, $inter_h);
            } else {
                $ratio = $ratio_h > $ratio_w ? $ratio_h : $ratio_w;
                $inter_w = (int) ($w * $ratio);
                $inter_h = (int) ($h * $ratio);
                $inter_img = imagecreatetruecolor($inter_w, $inter_h);
                imagecopyresampled($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h, $w, $h);
                // 定义一个新的图像
                $new_img = imagecreatetruecolor($new_width, $new_height);
                imagecopy($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height);
            }
            $MSG = "<font color=green>成功生成缩略图！&nbsp;</font>";
            $this->tosizedFile = str_replace("../", "./", $this->newFile("toSized"));
            $MSG = $this->image($new_img, $this->type, $this->newFile("toSized"), $MSG);
            $this->MSG .= $MSG . "&nbsp;";
            imagedestroy($new_img);
            imagedestroy($this->sim);
        }
    }

    function addTXT($txt, $fontSize = 20, $tcolor = "250,0,0", $degree = 0, $position = 0, $font = "lib/simkai.ttf")
    { // 加水印文本
        if ($this->webFile != "" && $this->webFile != "./images/nopic.jpg") {
            $len = strlen($txt) / 2;
            $lentxt = $len * $fontSize;
            $this->sim();
            $this->size();
            $txt = iconv("gb2312", "utf-8", $txt);
            list ($c1, $c2, $c3) = explode(",", $tcolor);
            if (! file_exists($font))
                $font = "";
            $color = imagecolorallocate($this->sim, $c1, $c2, $c3);
            switch ($position) {
                case 11:
                    $x = 10;
                    $y = $fontSize + 10;
                    break;
                case 12:
                    $x = ($this->sw) / 2 - $lentxt / 2;
                    $y = $fontSize + 10;
                    break;
                case 13:
                    $x = $this->sw - $lentxt - 30;
                    $y = $fontSize + 10;
                    break;
                
                case 21:
                    $x = 10;
                    $y = ($this->sh) / 2 - $fontsize / 2;
                    break;
                case 22:
                    $x = ($this->sw) / 2 - $lentxt / 2;
                    $y = ($this->sh) / 2 - $fontsize / 2;
                    break;
                case 23:
                    $x = $this->sw - $lentxt - 30;
                    $y = ($this->sh) / 2 - $fontsize / 2;
                    break;
                
                case 31:
                    $x = 10;
                    $y = $this->sh - 10;
                    break;
                case 32:
                    $x = ($this->sw) / 2 - $lentxt / 2;
                    $y = $this->sh - 10;
                    break;
                case 33:
                    $x = $this->sw - $lentxt - 30;
                    $y = $this->sh - 10;
                    break;
                
                default:
                    $x = 10;
                    $y = $this->sh - 10;
            }
            imagettftext($this->sim, $fontSize, $degree, $x, $y, $color, $font, $txt);
            $MSG = "<font color=green>添加文本水印成功！</font>";
            $this->addtxtFile = str_replace("../", "./", $this->newFile("addTXT"));
            $MSG = $this->image($this->sim, $this->type, $this->newFile("addTXT"), $MSG);
            $this->MSG .= $MSG . "&nbsp;";
        }
    }

    function addPIC($logo, $position = 0)
    { // 加Logo水印
        if ($this->webFile != "" && $this->webFile != "./images/nopic.jpg") {
            $this->sim();
            $this->size();
            $slogo = imagecreatefromjpeg($logo);
            list ($w, $h) = getimagesize($logo);
            switch ($position) {
                case 0: // 水印图片位于右小角
                    $x = ($this->sw) - $w;
                    $y = ($this->sh) - $h;
                    break;
                case 1: // 水印图片位于中间
                    $x = ($this->sw) / 2 - $w / 2;
                    $y = ($this->sh) / 2 - $h / 2;
                    break;
                default:
                    $x = ($this->sw) - $w;
                    $y = ($this->sh) - $h;
            }
            imagecopy($this->sim, $slogo, $x, $y, 0, 0, $w, $h);
            $MSG = "<font color=green>添加水印图片成功！</font>";
            $this->addpicFile = str_replace("../", "./", $this->newFile("addPIC"));
            $MSG = $this->image($this->sim, $this->type, $this->newFile("addPIC"), $MSG);
            imagedestroy($this->sim);
            imagedestroy($slogo);
            $this->MSG .= $MSG;
        }
    }

    private function sim()
    { // 获取上传文件的资源
        $type = $this->type;
        switch ($type) {
            case "jpg":
                $this->sim = imagecreatefromjpeg($this->imgFile);
                break;
            case "jpeg":
                $this->sim = imagecreatefromjpeg($this->imgFile);
                break;
            case "gif":
                $this->sim = imagecreatefromgif($this->imgFile);
                break;
            case "png":
                $this->sim = imagecreatefrompng($this->imgFile);
        }
    }

    private function size()
    { // 获取上传文件的尺寸
        list ($w, $h) = getimagesize($this->imgFile);
        $this->sw = $w;
        $this->sh = $h;
    }

    private function image($im, $type, $newFile, $MSG)
    { // 生成的文件与原文件的格式相同
        $flag = 0; // 生成与上传文件格式相同的文件以及提示修改信息
        switch ($type) {
            case "jpg":
                if (! imagejpeg($im, $newFile))
                    $flag = 1;
                break;
            case "jpeg":
                if (! imagejpeg($im, $newFile))
                    $flag = 1;
                break;
            case "gif":
                if (! imagegif($im, $newFile))
                    $flag = 1;
                break;
            case "png":
                if (! imagepng($im, $newFile))
                    $flag = 1;
        }
        if ($flag == 0) {
            return "$MSG";
        } else {
            return "生成文件时出现错误";
        }
    }

    private function newFile($addName)
    { // 新文件名称具备不同的特征
        return $this->uploaddir . "/" . $addName . $this->name . "." . $this->type;
    }
} // class

?>