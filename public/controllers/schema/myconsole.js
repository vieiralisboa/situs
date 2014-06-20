function myConsole(){
	$('<code class="header">').html(arguments[0]).appendTo('body');
	for (var i = 1; i< arguments.length; i += 1) {
		var argument = arguments[i];
		//console.log(argument);
		if (typeof argument != "string") {
			$('<code>').addClass("json").html(JSON.stringify(argument)).appendTo('body');
		}
		else {
			$('<code>').html(argument).appendTo('body');
		}
	}
}
