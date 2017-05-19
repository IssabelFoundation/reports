

$(document).ready((function(){

	$('select').click(function(){ 
		var val = $('select option:selected').val(); //obtenemos el value para el valor del item es .text()
		if(val=='duration'){
			//$('.time').attr('style','visibility:show;');
			$('#duration').show();
			$('#textfield').hide();
		}
		else{
			$('#duration').hide();
			$('#textfield').show();
		}
	});
}));
/*
onChange="validarSiNumero(this.value);"
function validarSiNumero(numero){
	if (!/^([0-9])*$/.test(numero))
		alert("El valor " + numero + " no es un número");
}*/

 function onlyNumbers(evt)
      {
        var keyPressed = (evt.which) ? evt.which : event.keyCode
        return !(keyPressed > 31 && (keyPressed < 48 || keyPressed > 57));
      }