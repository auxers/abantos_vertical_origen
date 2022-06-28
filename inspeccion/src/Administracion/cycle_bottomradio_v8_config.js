var xaraSwidgets_cycleBottomradio_v8Templates = {

	entry:		'<a href="{link}">'
			+	'<img src="{image}"  border="0"  />'
			+	'</a>',
			
	bulletimage:'{bulletimage}',			
			
	main:	 '<div id="{component_id}OuterDiv" class="{component_id}cycle_bottomradio">'
			+ 	'{entryhtml}'
			+ '</div>'	
			+ 	'<div class="{component_id}cycleBottomradio_navcontainer">'
			+ 		'<div class="{component_id}cycleBottomradio_nav"></div>'
			+ 	'</div>'
			
	
			
	        
};

// this is the constructor for a component
// it loops through each 'entry' in the array of data and compiles the entry template for it
// it then applies the resulting HTML to the main template before writing the whole lot to the div on the page
// it then initialises the actual jquery plugin for the div (that now contains the required HTML as a result of writing the template to it)
function xaraSwidgets_cycleBottomradio_v8Constructor(divID, data)
{
	
	
	var entryHTML = '';
	// loop through each entry in the array and compile the entry template for it
	for(var i=0; i<data.length; i++)
	{
		entryHTML += xaraSwidgets_compileTemplate(xaraSwidgets_cycleBottomradio_v8Templates.entry, data[i]);
		bulletimage = (data[0].bulletimage);
	}
	
	// now lets compile the 'main' template which acts as a wrapper for each entry
	
	var mainData = {
		component_id:divID,
		entryhtml:entryHTML
	};
	
	var mainTemplate = xaraSwidgets_compileTemplate(xaraSwidgets_cycleBottomradio_v8Templates.main, mainData);
	
	// now lets apply the resulting HTML for the whole component to the main DIV that was exported by XARA
	
	$('#' + divID).html(mainTemplate);
	
	
	
	
	// now we have the components DOM on the page - we can use the 'OuterDiv' as the jquery initiation point
	
	//	1. find the width and height of the parent div 
	
	// work out the required dimensions for width and height.
	var cb_height = Math.floor ( $('#' + divID).parent('div').height()* 0.893);
	var cb_width = $('#' + divID).parent('div').width()
	
	
	//	2a.  write the css for the main container
	$('head').append("<style>."+ divID +"cycle_bottomradio {background-color: #F8F8F8;  margin:0px;  z-index:0; width:" + cb_width +"px; height:" + cb_height +
	"px;} ."+ divID +"cycleBottomradio_slideshow { overflow:hidden; margin:0px;  z-index:0;  position:relative;   margin:0; padding:0; left:0%; background:transparent; width:" + cb_width +"px; height:" + cb_height +
	"px;} ."+ divID +"cycleBottomradio_slideshow img { padding: 0px; background-color: #F8F8F8; position: relative; margin:0; border:1px solid #CCC;  width:" + cb_width +
	"px;} </style>");
	
	//	2b.  add the class to the images
	$('#' + divID + 'OuterDiv').find('img').addClass(divID+"cycleBottomradio_slideshow");
	
	//	3.  write the css for navigation bullets
	$('head').append("<style>."+ divID +"cycleBottomradio_navcontainer {display:block; list-style: none; left:0px;  position: relative; padding-left: 0px; margin: 0; clear: both; border: 0px solid #CCCCCC;   background: #; z-index:2; height:22px;  width:" + cb_width +
	"px;} ."+ divID +"cycleBottomradio_nav a { display:block; background-image: url("+ bulletimage+"); float:left; height:22px; width: 22px; color:#555; background-position: 0 0px; background-repeat: no-repeat; text-decoration:none;text-indent: -9999px;" + 
	"} ."+ divID +"cycleBottomradio_nav a:hover { color:#fff;height:22px;width: 22px; background-image: url("+ bulletimage+"); background-position: 0 -22px;	background-repeat: no-repeat; display: block;"  +
	"}."+ divID +"cycleBottomradio_nav a.activeSlide{color:#fff; height:22px;width: 22px;background-image: url("+ bulletimage+");	background-position: 0 -22px;background-repeat: no-repeat; display: block;} </style>");

	
	$('#' + divID + 'OuterDiv').cycle({
						
						fx: 'all', // choose your transition type, ex: fade, scrollUp, shuffle, etc...
						
						pager:      '.'+divID+'cycleBottomradio_nav'
						
							
							
	});
}