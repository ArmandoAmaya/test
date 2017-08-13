/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function proyectos(e){

  /* LOADING */
  var l = Ladda.create( document.querySelector( '#proyectos' ) );
  l.start();
  /* CLEAR ALL ALERTS */
  toastr.clear();

  var _data = new FormData(),
      portada = document.getElementById('portada').files[0],
      logo = document.getElementById('logo_proyecto').files[0];

  _data.append('id_proyectos', $('#id_proyectos').val());
  _data.append('titulo', $('#id_titulo').val());
  _data.append('tmp_dir', $('#tmp_dir').val());
  _data.append('id_categoria', $('#id_categoria').val());
  _data.append('short_desc_es', $('#id_short_desc_es').val());
  _data.append('short_desc_en', $('#id_short_desc_en').val());
  _data.append('content_es', $('#id_content_es').html());
  _data.append('content_en', $('#id_content_en').html());
  _data.append('portada', portada);
  _data.append('logo', logo);

  e.defaultPrevented;
  $.ajax({
    type : "POST",
    url : "api/proyectos/editar",
    contentType:false,
    processData:false,
    data : _data,
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
$('#proyectos').click(function(e) {
  proyectos(e);
});
$('#proyectos_form').keypress(function(e) {
    if(e.which == 13) {
        proyectos(e);
    }
});