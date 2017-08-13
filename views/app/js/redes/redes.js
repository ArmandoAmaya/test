
/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function redes(e){
  /* LOADING */
  var l = Ladda.create( document.querySelector( '#redes' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();

  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/redes",
    data : $('#redes_form').serialize(),
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
$('#redes').click(function(e) {
  redes(e);
});
$('#redes_form').keypress(function(e) {
    if(e.which == 13) {
        redes(e);
    }
});