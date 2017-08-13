function add_email(e) {
	e.preventDefault();

	let html_insert = '<div class="form-group col-xs-12 col-md-4 col-xl-3"><input class="form-control" type="tel" name="emails[]" id="emails" value="" placeholder="Correo@ejemplo.com" /></div>';

	$('#emails').append(html_insert);

	if ($('#emails').children().length == 2) {
		$('#emails_control').append('<a href="javascript:void(0)" class="ml-10 d-inline-block" id="delete_email" onclick="delete_email(event)"> <span class="fa fa-trash"></span> Eliminar</a>');
	}
}

function delete_email(e){
	e.preventDefault();

	if ($('#emails').children().length > 1) {
		$('#emails').children().last().remove();
	}
	if($('#emails').children().length == 1){
		$('#delete_email').remove();
	}
	
}