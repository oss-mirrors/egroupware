<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier     oliviert@maphilo.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

$dojofile = $GLOBALS['egw_info']['server']['webserver_url']."/chatty/js/dojo/dojo.js";
$requestUrl = $GLOBALS['egw_info']['server']['webserver_url']."/chatty/index.php";
echo ('
<script type="text/javascript">
	var djConfig = {isDebug: false, parseWidgets: false};
	djConfig.debugAtAllCosts = false;
</script>
<script type="text/javascript" src="'.$dojofile.'"></script>' .
		'
<script language="JavaScript" type="text/javascript">
	dojo.require("dojo.widget.HtmlChattyManager");' .

	'if (djConfig.debugAtAllCosts){
		dojo.hostenv.writeIncludes();
	}
</script>
<script language="JavaScript" type="text/javascript"> 
' .
'function createChatWin(){' .
	'dojo.lang.setTimeout(this, function(){' .
	'var tmpdiv = document.createElement("div");' .
	'document.body.appendChild(tmpdiv);' .
	'var properties = {title:"Connected Users (Chat application)",constrainToContainer:0, inSync: true, serverURL: "'.$requestUrl.'"};' .
	'var FPmanager = dojo.widget.fromScript("ChattyManager", properties, tmpdiv, "last");' .
	'FPmanager.domNode.style.zIndex = "1000";}, 500);' .
'}' .
	'dojo.event.connect("after",window, "onload", this, "createChatWin")'.
'</script>' 
);

?>