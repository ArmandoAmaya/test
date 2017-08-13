var FormDropzone = function () {

    return {
        //main function to initiate the module
        init: function () {

            Dropzone.options.myDropzone = {
                init: function() {
                    this.on("addedfile", function(file) {
                        // Create the remove button
                        var removeButton = Dropzone.createElement("<a href='javascript:;'' class='btn btn-danger mt-5'>Eliminar</a>");

                        // Capture the Dropzone instance as closure.
                        var _this = this;

                        // Listen to the click event
                        removeButton.addEventListener("click", function(e) {
                          // Make sure the button click doesn't submit the form:
                          e.preventDefault();
                          e.stopPropagation();

                          // Remove the file preview.
                          _this.removeFile(file);
                          $.ajax({
                            type : "POST",
                            url : "api/upload/delete",
                            data : 'name='+file.name+'&tmp_dir=' + $('#tmp_dir').val(),
                            success : function(json) {},
                            error : function() {
                              window.alert('#delete ERORR');
                            }
                          });

                        });

                        // Add the button to the file preview element.
                        file.previewElement.appendChild(removeButton);
                    });
                }
            }
        }
    };
}();

jQuery(document).ready(function() {
   FormDropzone.init();
});


function delete_file_in_dir(dir, n)  {
  swal({
    title: "¿Está seguro?",
    text: "¡Usted borrará este elemento!",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-warning",
    confirmButtonText: '¡Sí, eliminar!',
    closeOnConfirm: false,
    //closeOnCancel: false
  },
  function() {
     $.ajax({
      type : "POST",
      url : "api/delete/file",
      data : 'file='+dir,
      success : function(json) {
        if(json.success == 1) {
          swal("Borrado!", "Se ha borrado correctamente.", "success");
          $('#tr_delete_'+n).remove();
        }
      },
      error : function(xhr, status) {
        console.log('Ha ocurrido un problema.');
      }
    });
    
  });

}