/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function register(e){
  e.preventDefault();
  $.ajax({
    type : "POST",
    url : "api/register",
    data : $('#register_form').serialize(),
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
}

/**
 * Events
 */
$('#register').click(function(e) {
  register(e);
});
$('#register_form').keypress(function(e) {
    if(e.which == 13) {
        register(e);
    }
});
