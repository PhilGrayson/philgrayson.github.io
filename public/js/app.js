$(function() {
	// Toggle the base64 button group
	$('.btn-group.base64 a').click(function(event) {
		$(this).addClass('btn-primary');
		$(this).siblings().removeClass('btn-primary');

		// Force the keyup event to do the action the user just clicked
		$('#base64-input').trigger('keyup');
		// Stop the page from jumping
		event.preventDefault();
	});

	// Base64 encode/decode
	$('#base64-input').keyup(function() {
		var type   = $('.btn-group.base64 .btn-primary').text(),
		    input  = $('#base64-input'),
		    result = $('#base64-result');
		switch(type) {
			case 'Encode':
				result.val(Base64.encode(input.val()));
			break;
			case 'Decode':
				result.val(Base64.decode(input.val()));
			break;
		}
	});
	
	// String length
	$('#stringlength-input').keyup(function() {
		var length = $(this).val().length;
		$('#stringlength-result').html(length);
	});

	// PHP string concatenator
	$('#php-concat-submit').click(function() {
		var input = $('#php-concat-input').val(),
       result = $('#php-concat-result');
		result.empty();

		// Remove the concat characters and whitespace
		output = input.replace(/'\.\s+'/g, '');
		// Remove the variable assignment if it exists
		output = output.replace(/\$\w+\s+=\s+'/, '');
		// Remove the trailing apostrophe  and semi-colon
		output = output.replace(/';/, '');

		// Display result on the right of the input
		result.append($('<h2>').text('Result'));
		result.append($('<p>').addClass('code')
		                      .append(output));
	});

	$('#php-concat-reset').click(function() {
		$('#php-concat-input').val('');
		$('#php-concat-result').empty();
	});
});
