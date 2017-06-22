/**
 * Ajax action to api rest
 * 
 * @param {*} e 
*/
function {{view}}(e){
  e.preventDefault();
  $.ajax({
    type : "{{method}}",
    url : "api/{{rest}}",
    data : $('#{{view}}_form').serialize(),
    success : function(json) {
      console.log(json.success);
      console.log(json.message);
      if(json.success == 1) {
        setTimeout(function(){
            location.reload();
        },1000);
      }
    },
    error : function(xhr, status) {
      console.log('Ha ocurrido un problema.');
    }
  });
}

/**
 * Events
 */
$('#{{view}}').click(function(e) {
  {{view}}(e);
});
$('#{{view}}_form').keypress(function(e) {
    if(e.which == 13) {
        {{view}}(e);
    }
});