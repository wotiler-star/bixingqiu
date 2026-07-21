/**
 * Created by konecms on 2016/3/9.
 */
$(document).ready(function(){
	$height=$("body").height();
    $height_headerframe=$(".headerframe").height();
    $height_footerframe=$(".footerframe").height();
	$height_mainframe=$height-($height_headerframe+$height_footerframe);
	$(".mainframe").height($height_mainframe); 
	
	//$width_main=$(".mainframe").width();
	//console.log($width_main)
	//$width_left=$(".left").width();
	//console.log($width_left)
	//$width_mainframe_right=$width_main-$width_left;
	//console.log($width_mainframe_right)
	//$(".right").width($width_mainframe_right);
	//$(".right").css("height",$height_mainframe);
	
	
    $(window).resize(function(){
    $height=$("body").height();
    $height_headerframe=$(".headerframe").height();
    $height_footerframe=$(".footerframe").height();
    $height_mainframe=$height-($height_headerframe+$height_footerframe);
    $(".mainframe").height($height_mainframe);

    //宽度
    //$window_width=$(window).width();
    //$(".headerframe").width($window_width);
    //$(".mainframe").width($window_width);
    //$(".footerframe").width($window_width);
    //
    //$width_left=$(".left").width();
    //$width_right=$window_width-$width_left;
    //
    //$(".right").width($width_right);
    //$(".right").css("height",$height_mainframe);
    });
    $(".header-tab>ul>li").click(function(){
        $height=$("body").height();
        $height_headerframe=$(".headerframe").height();
        $height_footerframe=$(".footerframe").height();
        $height_mainframe=$height-($height_headerframe+$height_footerframe);
        $(".mainframe").height($height_mainframe);

        //宽度
        //$window_width=$(window).width();
        //
        //$width_left=$(".left").width();
        //$width_right=$window_width-$width_left;
        //
        //$(".right").width($width_right);
        //$(".right").css("height",$height_mainframe);
    })
    $(".foldsider").click(function(){
        $height=$("body").height();
        $height_headerframe=$(".headerframe").height();
        $height_footerframe=$(".footerframe").height();
        $height_mainframe=$height-($height_headerframe+$height_footerframe);
        $(".mainframe").height($height_mainframe);

        //宽度
        //$window_width=$(window).width();
        //$width_left=$(".left").width();
        //$width_right=$window_width-$width_left;
        //
        //$(".right").width($width_right);
        //$(".right").css("height",$height_mainframe);
    })
});