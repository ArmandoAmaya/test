$('#lostpass').click(function(e){
  e.preventDefault();
  $.ajax({
    type : "POST",
    url : "api/lostpass",
    data : $('#lostpass_form').serialize(),
    success : function(json) {
      console.log(json.success);
      console.log(json.message);
      if(json.success == 1) {
        setTimeout(function(){
            location.reload();
        },1000);
      }
    },
    error : function(xhr, status) {
      console.log('Ha ocurrido un problema.');
    }
  });
});
