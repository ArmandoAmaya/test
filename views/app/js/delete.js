function deleteModal(controller,id) {
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
    window.location = controller + '/eliminar/' + id; 
    swal("Borrado!", "Se ha borrado correctamente.", "success");
  });
}

