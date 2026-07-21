$(document).ready(function(){ 
    var start=new Date();
        start=Date.parse(start)/1000;
        var counts=20;
        var $i=1;
        function timer(){
         var now=new Date();
         now=Date.parse(now)/1000;
         var x=parseInt(counts-(now-start),10);
	     $j=$i++;
	     $h=main.scrollHeight;
         $('#main').append('<p>'+$j+'hello'+$h+'</p>');
		 $("#main").scrollTop($h);
         if(x>0){
             window.setTimeout(timer ,1000);
        }
    }
	 timer();
});











