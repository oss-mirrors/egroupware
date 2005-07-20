function openWindow(url,windowName,windowWidth,windowHeight) 
{
	windowID = window.open(url, windowName, "width=" + windowWidth + ",height=" + windowHeight + ",screenX=0,screenY=0,top=0,left=0,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no");
}
