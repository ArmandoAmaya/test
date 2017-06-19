$('#{{view}}').click(function(e){
  e.preventDefault();
  $.ajax({
    type : "{{method}}",
    url : "api/{{rest}}",
    data : $('#{{view}}_form').serialize(),
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
