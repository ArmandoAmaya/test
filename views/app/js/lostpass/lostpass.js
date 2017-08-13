/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function lostpass(e){

  /* LOADING */
  var l = Ladda.create( document.querySelector( '#lostpass' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();

  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/lostpass",
    data : $('#lostpass_form').serialize(),
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
$('#lostpass').click(function(e) {
  lostpass(e);
});
$('#lostpass_form').keypress(function(e) {
    if(e.which == 13) {
        lostpass(e);
    }
});