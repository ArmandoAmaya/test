/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function categorias(e){

  /* LOADING */
  var l = Ladda.create( document.querySelector( '#categorias' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();
    
  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/categorias/editar",
    data : $('#categorias_form').serialize(),
    success : function(json) {
      console.log(json);
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
$('#categorias').click(function(e) {
  categorias(e);
});
$('#categorias_form').keypress(function(e) {
    if(e.which == 13) {
        categorias(e);
    }
});