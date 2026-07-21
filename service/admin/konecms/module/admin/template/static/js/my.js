
  $(function(){
     $('.page_btnbox span').click(function(){
         $(this).addClass('active').siblings().removeClass('active');
         var index= $(this).index();
         $('.page_cbox>div').eq(index).show().siblings().hide();
     });
  });
