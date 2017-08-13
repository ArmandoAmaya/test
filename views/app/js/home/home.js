// Agregar un slider
function add_slider(data){

    /* LOADING */
    var l = Ladda.create( document.querySelector( '#upload_home' ) );
    l.start();
    /* CLEAR ALL ALERTS */
    toastr.clear();

    var _data = new FormData();
    var slider = document.getElementById('slider');
    var file = slider.files[0];

    _data.append('slider',file);

    $.ajax({
        type : "POST",
        url : "api/home",
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
// Elimina un slider 
function delete_slider(file, id){

    swal({
        title: "¿Está seguro?",
        text: "¡Usted borrará este elemento!",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-warning",
        confirmButtonText: '¡Sí, eliminar!',
        closeOnConfirm: false,
      },
      function() {

        $.ajax({
            type : "POST",
            url : "api/home/delete",
            data : 'id='+id+'&file='+file,
            success : function(json) {
                if(json.success == 1) {
                    swal("Borrado!", "Se ha borrado correctamente.", "success");
                }
                setTimeout(function(){
                    location.reload();
                },1000);
                
            },
            error : function(xhr, status) {
              console.log('Ha ocurrido un problema.');
            }
        });
        
        
    });
    
}

function edit_slider(e,id) {
    e.preventDefault();
    /* LOADING */
    var l = Ladda.create( document.querySelector( '#upload_home_'+id ) );
    l.start();
    /* CLEAR ALL ALERTS */
    toastr.clear();

    var _data = new FormData();
    var slider = document.getElementById('slider_'+id);
    var file = slider.files[0];

    _data.append('id', id);
    _data.append('slider',file);

    $.ajax({
        type : "POST",
        url : "api/home/edit",
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