function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}

$(document).ready(function(){

	var prompt_length = $("#q-length").val();

	var remove_prompt = function(){
		$("#prompt-float").fadeOut();
	}; 

	var $response = $("#m");
	var prompt = $response.val();

	$response.focus().val('').val(prompt+' ');

	$("#prompt-float").click(remove_prompt);
	
	$response.keyup(function(){
		if ($(this).caret().start < prompt_length)
			remove_prompt();
	});
});