/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function login(e){

  /* LOADING */
  var l = Ladda.create( document.querySelector( '#login' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();

  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/login",
    data : $('#login_form').serialize(),
    success : function(json) {
      if(json.success == 1) {
        toastr.success(json.message, 'Éxito');
        setTimeout(function(){
            location.reload();
        },1000);
      } else {
        toastr.error(json.message, 'Error');
      }
      l.stop();
    },
    error : function(xhr, status) {
      console.log('Ha ocurrido un problema.');
    }
  });
}

/**
 * Events
 */
$('#login').click(function(e) {
  login(e);
});
$('#login_form').keypress(function(e) {
    if(e.which == 13) {
        login(e);
    }
});
