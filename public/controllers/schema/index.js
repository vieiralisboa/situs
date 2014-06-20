//JavaScript
if(typeof Frontgate != 'undefined') {
	
	// load styles
	Frontgate.stylesheet("/css/default.css");
	
	// load required scripts
	Frontgate.libs('underscore',
		'backbone',
		'/dev/situs.schema/js/schema.js');
}
else $('body').prepend($('<h2>')
	.css({
		padding: '5px 30px', 
		fontFamily: 'serif', 
		color: 'rgba(200,100,100,0.5)'})
	.text('Frontgate JavaScript Library not found.'));
