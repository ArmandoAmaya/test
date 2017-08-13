/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function admins(e){

  /* LOADING */
  var l = Ladda.create( document.querySelector( '#admins' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();

  var algo = new FormData();
  var perfil = document.getElementById('input-file-now-custom-1');
  var file = perfil.files[0];

  algo.append('email',$('#id_email').val());
  algo.append('id',$('#id_user').val());
  algo.append('name',$('#id_name').val());
  algo.append('apellido',$('#id_name').val());
  algo.append('pass',$('#id_pass').val());
  algo.append('pass_repeat',$('#id_pass_repeat').val());
  algo.append('phone',$('#id_phone').val());
  algo.append('perfil',file);
    
  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/admins/editar",
    contentType:false,
    processData:false,
    data : algo,
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
$('#admins').click(function(e) {
  admins(e);
});
$('#admins_form').keypress(function(e) {
    if(e.which == 13) {
        admins(e);
    }
});