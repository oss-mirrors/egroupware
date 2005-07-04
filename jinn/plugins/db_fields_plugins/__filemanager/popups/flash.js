function flashcompattest()
{
    var flash6Installed = false;
	var flash7Installed = false;
	var flash8Installed = false;
	var flash9Installed = false;
	var actualVersion = 0;
 
	if(navigator.appVersion.indexOf("MSIE") != -1 && navigator.appVersion.toLowerCase().indexOf("win") != -1 && navigator.appVersion.indexOf("AOL") == -1)
    	{
        	document.write('<SCR' + 'IPT LANGUAGE=VBScript\> \n');
		document.write('on error resume next \n');
		document.write('flash6Installed = (IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash.6"))) \n');
		document.write('flash7Installed = (IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash.7"))) \n');
		document.write('flash8Installed = (IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash.8"))) \n');
		document.write('flash9Installed = (IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash.9"))) \n');
		document.write('<\/SCR' + 'IPT\> \n');
	}
	for (var i = 6; i <= 9; i++)
    	{
         		if (eval("flash"+i+"Installed") == true) actualVersion = i;
	}
	if (navigator.plugins)
    	{
        		if (navigator.plugins["Shockwave Flash 2.0"]|| navigator.plugins["Shockwave Flash"])
        		{
            		var isVersion2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
			var flashDescription = navigator.plugins["Shockwave Flash" + isVersion2].description;
            		actualVersion = parseInt(flashDescription.substring(16));
        		}
	}
	if(navigator.userAgent.indexOf("WebTV") != -1) actualVersion = 4;
	if (actualVersion >= 6)
    	{
		return true;
	}
    	else
    	{
        	return false;
    	}
}
function thisMovie(movieName)
{
	if (navigator.appName.indexOf ("Microsoft") !=-1) 
	{
    		return window[movieName]
  	} 
	else 
	{
    		return document[movieName]
  	}
}
function movieIsLoaded(movieName) 
{
	if (typeof(movieName) != "undefined") 
	{
    		return movieName.PercentLoaded() == 100;
  	} 
	else 
	{
    		return false;
  	}
}
function playmovie(movieName) 
{
	if (movieIsLoaded(thisMovie(movieName))) 
	{
    		thisMovie(movieName).Play();
  	}
}
function stopmovie(movieName) 
{
	thisMovie(movieName).StopPlay();
}
function writeFlash(file_width,file_height,src,name)
{
	document.write('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'+
                       		'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"'+
                       		'width="'+file_width+'"'+
                       		'height="'+file_height+'"'+                                            		 	
				'id="'+name+'">'+
			'<param name="movie"   	value="'+src+'">'+
                        '<param name="menu"    	value="false">'+
                        '<param name="quality" 	value="high">'+
                        '<param name="loop"	value="true">'+
                        '<param name="scale" 	value="exactfit">'+
			'<param name="play"	value="false">'+
                        '<embed src="'+src+'"'+
                                'menu="false"'+
                                'quality="high"'+
                                'loop="true"'+
                                'scale="exactfit"'+
				'play="false"'+
                                'width="'+file_width+'"'+
                                'height="'+file_height+'"'+
                                'name="'+name+'"'+
                                'type="application/x-shockwave-flash"'+
                                'pluginspage="http://www.macromedia.com/go/getflashplayer">'+
                                '</embed>'+
                                '</object>');
}
