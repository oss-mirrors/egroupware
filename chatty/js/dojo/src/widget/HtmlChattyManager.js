/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/
   /**************************************************************************\
	/* Copyright (c) 2004-2005 The Dojo Foundation, Licensed under the 3-Clause BSD *
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier  oliviert@maphilo.com        *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
dojo.provide("dojo.widget.ChattyManager");
dojo.provide("dojo.widget.HtmlChattyManager");


//
// this widget provides a window-like floating pane
//
// TODO: instead of custom drag code, use HtmlDragMove.js in
// conjuction with DragHandle).  The only tricky part is the constraint 
// stuff (to keep the box within the container's boundaries)
//


dojo.require("dojo.html");
dojo.require("dojo.style");
dojo.require("dojo.dom");
dojo.require("dojo.fx.html");
dojo.require("dojo.chatty.IoManager");
dojo.require("dojo.widget.HtmlLayoutPane");
dojo.require("dojo.widget.HtmlChattyResizeHandle");
dojo.require("dojo.widget.HtmlChatty");
dojo.require("dojo.date");
dojo.require("dojo.widget.html.Tooltip");

dojo.widget.HtmlChattyManager = function(){
	dojo.widget.HtmlLayoutPane.call(this);
}

dojo.inherits(dojo.widget.HtmlChattyManager, dojo.widget.HtmlLayoutPane);

dojo.lang.extend(dojo.widget.HtmlChattyManager, {
	widgetType: "ChattyManager",

	isContainer: true,
	containerNode: null,
	domNode: null,
	clientPane: null,
	dragBar: null,
	dragOrigin: null,
	posOrigin: null,
	maxPosition: null,
	hasShadow: true,
	title: 'Untitled',
	savedHeight: null,
	savedOverflow: null,
	isMinimized: "max",
	sizeBeforeMin: null,
	constrainToContainer: 0,
	resizeHandle: null,
	chatBox: null,
	sendBox: null,
	sendButton: null,
	divContent: null,
	windowChild: null,
	divUsers: null,
	zindexForeGround: 1000,
	zindexBackGround: 500,
	templateCssPath: dojo.uri.dojoUri("src/widget/templates/HtmlChatty.css"),
	ioManager : null,
	listOfUsers: null,
	inSync: false,
	serverURL: null,
	helpOpen: false,
	chatty_info:{"windowsManager": null, "listOfUsers": null, "listOfChatWindows": null},

	fillInTemplate: function(){
		this.windowChild = new Array();
		if (this.templateCssPath) {
			dojo.style.insertCssFile(this.templateCssPath, null, true);
		}

		dojo.html.addClass(this.domNode, 'dojoFloatingPane');

		this.hide();
		var elm = document.createElement('div');
		dojo.dom.moveChildren(this.domNode, elm, 0);
		dojo.html.addClass(elm, 'dojoFloatingPaneClient');


		// add a drop shadow
		if ( this.hasShadow ) {
			this.shadow = document.createElement('div');
			dojo.html.addClass(this.shadow, "dojoDropShadow");
			dojo.style.setOpacity(this.shadow, 0.5);
			this.domNode.appendChild(this.shadow);
			dojo.html.disableSelection(this.shadow);
		}
		// this is our client area
		this.clientPane = this.createPane(elm, 'client');
		this.clientPane.ownerPane = this;

		this.divUsers = document.createElement("div");



		var elm = document.createElement('div');
		elm.appendChild(document.createTextNode(this.title));
		dojo.html.addClass(elm, 'dojoFloatingPaneDragbar');
		this.dragBar = this.createPane(elm, 'top');
		this.dragBar.ownerPane = this;


		var buttons = document.createElement('div');
		dojo.html.addClass(buttons, 'dojoMyFloatingPaneButtons');
		var btnHelp = document.createElement("img")
		btnHelp.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_help.gif";
		btnHelp.alt = "Help";
		btnHelp.title = btnHelp.alt;
		//btnHelp.id = "chattyBtnHelpId";
		dojo.event.connect(btnHelp, 'onclick', this, 'onBtnHelp');

		var btnMin = document.createElement("img")
		btnMin.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_minus.gif";
		btnMin.alt = "Minimize";
		btnMin.title = btnMin.alt;
		dojo.event.connect(btnMin, 'onclick', this, 'onBtnMin');
		var btnMax = document.createElement("img");
		btnMax.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_max.gif";
		btnMax.alt = "Maximize";
		btnMax.title = btnMax.alt;
		dojo.event.connect(btnMax, 'onclick', this, 'onBtnMax');
		
		buttons.appendChild(btnHelp);
		buttons.appendChild(btnMin);
		buttons.appendChild(btnMax);
		this.dragBar.domNode.appendChild(buttons);
		//var helpTooltip = dojo.widget.fromScript('tooltip', {connectId: btnHelp.id, caption:'Chatty Help'});		
		//var helpDiv = document.createElement('div');
		//helpDiv.innerHTML = "Chatty, chat system for eGrouwpware users <br/> based on Dojo toolkit: dojotoolkit.org <br/> Created by Olivier TITECA-BEAUPORT: oliviert@maphilo.com";
		//helpTooltip.domNode.appendChild(helpDiv);
		this.divContent = document.createElement('div');
		dojo.html.addClass(this.divContent, 'dojoMyFloatingPaneDivContent');		
		this.divContent.appendChild(this.divUsers);
		this.clientPane.domNode.appendChild(this.divContent);	
		
		var props = {targetElmId: this.widgetId, minSize:{x: 300, y:200}};

		resDiv = document.createElement('div');
		this.clientPane.domNode.appendChild(resDiv);		
		this.resizeHandle = dojo.widget.fromScript("ChattyResizeHandle",props,resDiv);
		dojo.html.disableSelection(this.dragBar.domNode);
		dojo.event.connect(this.dragBar.domNode, 'onmousedown', this, 'onMyDragStart');
		dojo.event.topic.registerPublisher("/settingFocus", this, "setWinFocus");
		dojo.event.connect(this, "postCreate", this, "init_chatty");
	},


	sync: function(){
		var data = new Array();
		for(winName in this.chatty_info.listOfChildWindows){
			if(this.chatty_info.listOfChildWindows[winName]){
				data[winName] = this.chatty_info.listOfChildWindows[winName]["theWidget"].getMySize();
			}
		}
		wm = this.getMySize();
		dataToPass = {"listofchildwindows": data, "wmanager": wm};
		this.ioManager.sync(dataToPass);
	},
	
		syncUnload: function(){
		var data = new Array();
		for(winName in this.chatty_info.listOfChildWindows){
			if(this.chatty_info.listOfChildWindows[winName]){
				data[winName] = this.chatty_info.listOfChildWindows[winName]["theWidget"].getMySize();
			}
		}
		wm = this.getMySize();
		dataToPass = {"listofchildwindows": data, "wmanager": wm};
		this.ioManager.syncUnload(dataToPass);
	},
	
	requestRestoreWindows: function(){
		this.ioManager.request("", "chatty.restoreWindows", "chatty_restoreWindows");
	},
	postCreate: function(args, fragment, parentComp){
		// move our 'children' into the client pane
		// we already moved the domnodes, but now we need to move the 'children'

		var kids = this.children.concat();
		this.children = [];

		for(var i=0; i<kids.length; i++){
			if (kids[i].ownerPane == this){
				this.children.push(kids[i]);
			}else{
				this.clientPane.children.push(kids[i]);
			}
		}
		this.resizeSoon();
	},

	init_chatty: function(){
		/* intialize chatty_info */
		this.chatty_info.listOfUsers = new Array();
		this.chatty_info.listOfChildWindows = new Array();
		this.chatty_info.windowsManager = new Array();
		/* creation of the IO Manager*/				
		this.ioManager = new dojo.chatty.IoManager();
		this.ioManager.setContext(this.serverURL, this, 8000);
		/* restore chat windows */
		this.requestRestoreWindows();		
		this.show();
		/* Setting the synchronisation chat system*/
		if(this.inSync){
			if(this.ioManager.timeSync > 0){
			var self = this;
			var closure = function(){ return function(){ self.sync(); } }();
			setInterval(closure, this.ioManager.timeSync);
			}
		}
		/*connect unload document to a sync call*/
		dojo.event.connect("before", window, "onunload", this, "sync");	
	},

	onResized: function(){
		if ( this.hasShadow ) {
			var width = dojo.style.getOuterWidth(this.domNode);
			var height = dojo.style.getOuterHeight(this.domNode);
			dojo.style.setOuterWidth(this.shadow, width);
			dojo.style.setOuterHeight(this.shadow, height);
		}
			this.domNode.style.zIndex = "1000";
			this.shadow.style.zIndex = "-1";

		dojo.widget.HtmlChatty.superclass.onResized.call(this);
	},

	createPane: function(node, align){

		var pane = dojo.widget.fromScript("LayoutPane", { layoutAlign: align }, node);

		this.addPane(pane);

		return pane;
	},

	onMyDragStart: function(e){
		this.dragOrigin = {'x': e.clientX, 'y': e.clientY};
		
		// this doesn't work if (as in the test file) the user hasn't set top
		// 	this.posOrigin = {'x': dojo.style.getNumericStyle(this.domNode, 'left'), 'y': dojo.style.getNumericStyle(this.domNode, 'top')};
		this.posOrigin = {'x': this.domNode.offsetLeft, 'y': this.domNode.offsetTop};

		if (this.constrainToContainer){
			// get parent client size...

			if (this.domNode.parentNode.nodeName.toLowerCase() == 'body'){
				var parentClient = {
					'w': dojo.html.getDocumentWidth(),
					'h': dojo.html.getDocumentHeight()
				};
			}else{
				var parentClient = {
					'w': dojo.style.getInnerWidth(this.domNode.parentNode),
					'h': dojo.style.getInnerHeight(this.domNode.parentNode)
				};
			}

			this.maxPosition = {
				'x': parentClient.w - dojo.style.getOuterWidth(this.domNode),
				'y': parentClient.h - dojo.style.getOuterHeight(this.domNode)
			};
		}

		dojo.event.connect(document, 'onmousemove', this, 'onMyDragMove');
		dojo.event.connect(document, 'onmouseup', this, 'onMyDragEnd');
	},

	onMyDragMove: function(e){
		var x = this.posOrigin.x + (e.clientX - this.dragOrigin.x);
		var y = this.posOrigin.y + (e.clientY - this.dragOrigin.y);

		if (this.constrainToContainer){
			if (x < 0){ x = 0; }
			if (y < 0){ y = 0; }
			if (x > this.maxPosition.x){ x = this.maxPosition.x; }
			if (y > this.maxPosition.y){ y = this.maxPosition.y; }
		}
		dojo.style.setOpacity(this.domNode, 0.5);
		this.domNode.style.left = x + 'px';
		this.domNode.style.top  = y + 'px';
	},

	onMyDragEnd: function(e){
		dojo.style.setOpacity(this.domNode, 1);
		dojo.event.disconnect(document, 'onmousemove', this, 'onMyDragMove');
		dojo.event.disconnect(document, 'onmouseup', this, 'onMyDragEnd');
	},
	
	onBtnMin: function(e){
		if(this.isMinimized == "max"){
			this.clientPane.hide();
			this.sizeBeforeMin = this.getMySize();
			dojo.fx.fadeOut(this.clientPane.domNode, 800);
			this.wipeOutToHeight(300, dojo.style.getOuterHeight(this.dragBar.domNode));
			this.isMinimized = "min";
		}
	},
	
	onBtnMax: function(e){
		if(this.isMinimized=="min"){
			this.savedHeight = this.sizeBeforeMin.size.h;
			dojo.fx.fadeIn(this.clientPane.domNode, 800);
			var myAnim = this.wipeInToHeight(300, this.savedHeight, null, true);
			myAnim.play(true);
			dojo.lang.setTimeout(this, this.endWipeIn, 400);
			this.isMinimized = "max";
			dojo.style.setOuterHeight(this.domNode, this.savedHeight);		
			this.clientPane.show();			
		}			
	},
	
	onBtnHelp: function(e){
		if(!this.helpOpen){
			this.helpDiv = document.createElement("div");
			dojo.dom.insertAtPosition(this.helpDiv, this.clientPane.domNode,"first");
			this.helpDiv.innerHTML = "Chatty, chat system for <a href='http://www.egroupware.org' target='blank'>eGroupware</a> users <br/> based on Dojo toolkit: <a href='http://dojotoolkit.org' target='blank'>dojotoolkit.org</a> <br/> Created by Olivier TITECA-BEAUPORT: <a href='mailto:oliviert@maphilo.com'>oliviert@maphilo.com</a> <br/><br/> keys ALT+S send your message<br/>";
			this.helpDiv.style.backgroundColor="yellow";
			this.helpDiv.style.color="blue";
			this.helpDiv.style.border="1px solid blue";
			dojo.event.connect(this.helpDiv, "onclick", this, "onBtnHelp");	
			this.helpOpen = true;	
		}
		else{
			dojo.dom.removeNode(this.helpDiv);
			this.helpOpen = false;	
		}
	},
	
	endWipeIn: function(){
	this.resizeSoon();
	this.domNode.style.overflow = this.savedOverflow;
	},
	
	
wipeInToHeight: function(duration, height, callback, dontPlay) {
	node = this.domNode;
	node.style.display = "none";
	node.style.height = 0;
	if(this.savedOverflow == "visible") {
		node.style.overflow = "hidden";
	}
	var dispType = dojo.lang.inArray(node.tagName.toLowerCase(), ['tr', 'td', 'th']) ? "" : "block";
	node.style.display = dispType;

	var anim = new dojo.animation.Animation(
		new dojo.math.curves.Line([0], [height]),
		duration, 0);
	dojo.event.connect(anim, "onAnimate", function(e) {
		dojo.style.setOuterHeight(node, Math.round(e.x));
		//node.style.height = Math.round(e.x) + "px";
	});
	dojo.event.connect(anim, "onEnd", function(e) {
		if(callback) { callback(node, anim); }
	});
	if( !dontPlay ) { anim.play(true); }
	return anim;
},

	
wipeOutToHeight: function(duration, height, callback, dontPlay) {
	node = this.domNode;
	this.savedOverflow = dojo.html.getStyle(node, "overflow");
	var orgHeight = node.offsetHeight;
	this.savedHeight = orgHeight;
	node.style.overflow = "hidden";

	var anim = new dojo.animation.Animation(
		new dojo.math.curves.Line([orgHeight], [height]),
		duration, 0);
	dojo.event.connect(anim, "onAnimate", function(e) {
		node.style.height = Math.round(e.x) + "px";
	});
	dojo.event.connect(anim, "onEnd", function(e) {
		if(callback) { callback(node, anim); }
	});
	if( !dontPlay ) { anim.play(true); }
	return anim;
},

setWinFocus: function(widgetId){

},

/*   creation of new chat window *
 *								 
 */
MyNewWindow: function(e, props){
	id = e.target.innerHTML;
	if (!this.chatty_info.listOfChildWindows[id]){
	var properties = {title:"chat: "+id,constrainToContainer:0};
	var newWin = dojo.widget.fromScript("Chatty", properties);
	newWin.hide();
	dojo.html.body().appendChild(newWin.domNode);
	newWin.setManager(this.widgetId);
	newWin.winName = id;
	this.chatty_info.listOfChildWindows[id] = new Array();
	this.chatty_info.listOfChildWindows[id]["theWidget"]= newWin;
	bodyposition = dojo.html.getViewportSize();
	setposition = {"minmax": "max","position":{x: parseInt(bodyposition[0]/2), y: parseInt(bodyposition[1]/2)}};
	this.chatty_info.listOfChildWindows[id]["theWidget"].setMySize(setposition);
	this.setWinFocus(newWin.widgetId);
	//this.chatty_info.listOfChildWindows[id]["theWidget"].layoutSoon();
	dojo.event.connect(newWin.btnMin, "onclick", this.chatty_info.listOfChildWindows[id]["theWidget"], "MyXplode");
	dojo.event.connect(e.target, "onclick", this.chatty_info.listOfChildWindows[id]["theWidget"], "MyXplode");
	}
},


/*	called when closing a chat windows
*/
delChatWindow: function(name){
	if(this.chatty_info.listOfChildWindows[name]){
		thewidget = this.chatty_info.listOfChildWindows[name]["theWidget"];
		dojo.event.disconnect(this.chatty_info.listOfUsers[name]["domNode"].firstChild, "onclick", thewidget, "MyXplode");
		thewidget.destroy();
		dojo.dom.removeNode(this.chatty_info.listOfUsers[name]["domNode"]);
		this.chatty_info.listOfUsers[name]= null;
		this.chatty_info.listOfChildWindows[name]=null;
	}
},

onWinChildClose: function(name){
	dojo.fx.fadeOut(this.chatty_info.listOfChildWindows[name]["theWidget"].domNode, 400);
	dojo.lang.setTimeout(this, function(){this.delChatWindow(name);}, 1000);

},

updateUserList: function(userlist){
	tmpTab = new Array();
	for(name in userlist){
		tmpTab.push(userlist[name]);
	}
	tmpTab.sort();

	if(!this.chatty_info.listOfUsers){
		this.chatty_info.listOfUsers = new Array();
		}

	var tspan = new Array();
	/* create new connected users or link to the existing dom object */
	for(index in tmpTab){
		if(!this.chatty_info.listOfUsers[tmpTab[index]]){
			this.chatty_info.listOfUsers[tmpTab[index]]=new Array();
			this.chatty_info.listOfUsers[tmpTab[index]]["status"] = "connected";
			tspan[tmpTab[index]] = this._createUserSpan(tmpTab[index]);
			this.chatty_info.listOfUsers[tmpTab[index]]["domNode"] = tspan[tmpTab[index]];
		}
		else{
			tspan[tmpTab[index]] = this.chatty_info.listOfUsers[tmpTab[index]]["domNode"];
		}
	}

	/* destroy all disconnected users */
	for (index in this.chatty_info.listOfUsers){
		var isConnected = false;
		for(i=0; i<tmpTab.length; i++){
			if(tmpTab[i]==index){
				isConnected = true;	
			}
		}
		if (!isConnected){
			if(this.chatty_info.listOfChildWindows[index]){
				this.chatty_info.listOfChildWindows[index]["theWidget"].adviceDisconnect();
			}
		}
	}
	
	/* Add all links to connected users into a new div users */
	var userdiv = document.createElement("div");		
	for(index in tspan){	
		userdiv.appendChild(tspan[index]);
		dojo.event.kwConnect({
	    	srcObj:     tspan[index].firstChild,
	    	srcFunc:    'onclick', 
		    targetObj:  this,
		    targetFunc: 'MyNewWindow',
		    once:       true
		});
		}
		
		dojo.dom.replaceChildren(this.divUsers, userdiv);
},

syncHandler: function(data){
	var mydata = data.data;
	this.updateUserList(mydata.chatty_info.userlists.user);
	
	if(mydata.chatty_info.com.status == 'ok'){
		for(index in mydata.chatty_info.com.messages){
			var winName = mydata.chatty_info.com.messages[index].chatwindow;
			var text = mydata.chatty_info.com.messages[index].msg; 
			var sender = mydata.chatty_info.com.messages[index].sender; 
			if(this.chatty_info.listOfChildWindows[winName]){
				this.chatty_info.listOfChildWindows[winName]["theWidget"].updateChatBox(text, sender);
			}
			else{
					if(this.chatty_info.listOfUsers[winName]){
					obj = {"target": this.chatty_info.listOfUsers[winName]["domNode"].firstChild};
					this.MyNewWindow(obj);
					this.chatty_info.listOfChildWindows[winName]["theWidget"].updateChatBox(text, sender);
				}
			}
			
		}
	}
},

chatty_restoreWindows: function(data){
this.updateUserList(data.chatty_info.listofusers);
	var mydata = data.data;
	for (win in data.chatty_info.listofchatwindows){
		if(this.chatty_info.listOfUsers[win]){
			obj = {"target": this.chatty_info.listOfUsers[win]["domNode"].firstChild};
			this.MyNewWindow(obj);
			if(data.chatty_info.listofchatwindows[win].minmax =="min"){
				this.chatty_info.listOfChildWindows[win]["theWidget"].isMinimized = "min";
			}
			this.chatty_info.listOfChildWindows[win]["theWidget"].setMySize(data.chatty_info.listofchatwindows[win]);
		}
	}
	this.setMySize(data.chatty_info.wmanager);
	if(data.chatty_info.com){
		if(data.chatty_info.com.status=='ok'){
			for(i in data.chatty_info.com.messages){
				var winName = data.chatty_info.com.messages[i].chatwindow;
				var sender = data.chatty_info.com.messages[i].sender;			
				var msg = data.chatty_info.com.messages[i].msg;
				var lheure = data.chatty_info.com.messages[i].time;
				if(this.chatty_info.listOfChildWindows[winName]){
					this.chatty_info.listOfChildWindows[winName]["theWidget"].updateChatBox(msg,sender,lheure);
				}			
			}
		}
	}
},


_createUserSpan: function(username){
		var tspan = document.createElement("span");
		var newWinAnchor = document.createElement("a");
		newWinAnchor.href = "javascript:;"
		newWinAnchor.appendChild(document.createTextNode(username));
		tspan.id = username;
		tspan.appendChild(newWinAnchor);
		tspan.appendChild(document.createElement("br"));
		return tspan;
},
	

getMySize: function(){
	if(this.isMinimized=="min"){
		chatWindow = {'position': dojo.style.getAbsolutePosition(this.domNode),
					  'size':{'w':dojo.style.getOuterWidth(this.domNode),
	  			   		      'h':dojo.style.getOuterHeight(this.domNode)
							 },
				  'minmax' : this.isMinimized
					 };

		chatWindow.size = this.sizeBeforeMin.size;
		chatWindow.minmax = "min";
	}
	else
	{
	chatWindow = {'position': dojo.style.getAbsolutePosition(this.domNode),
					  'size':{'w':dojo.style.getOuterWidth(this.domNode),
	  			   		      'h':dojo.style.getOuterHeight(this.domNode)
							 },
				  'minmax' : this.isMinimized
					 };
	}						 
	return chatWindow;
},

setMySize: function(wmsize){
	if(wmsize){
		this.domNode.style.left = wmsize.position.x+"px";
		this.domNode.style.top = wmsize.position.y+"px";	
		this.show();
		dojo.style.setOuterWidth(this.domNode, wmsize.size.w);
		dojo.style.setOuterHeight(this.domNode, wmsize.size.h);
		if(wmsize["minmax"]=="max"){
			this.onBtnMax(null);
		}
		else{
			this.onBtnMin(null);
		}
	}
	else{
		bodyposition = dojo.html.getViewportSize();
		setposition = {"position":{x: parseInt(bodyposition[0] - (bodyposition[0]/3)), y: parseInt(bodyposition[1]/3)}};
		this.domNode.style.left = setposition.position.x+"px";
		this.domNode.style.top = setposition.position.y+"px";	
	}

	//this.layoutSoon();
	this.resizeSoon();

},
/* when a msg has been sent it's received and add to your chatBox*/
endSendMsg: function(data){
	var winName = data.chatty_info.chatwindow;
	var text = data.chatty_info.msg; 
	var sender = data.chatty_info.sender;
	if(this.chatty_info.listOfChildWindows[winName]){
		this.chatty_info.listOfChildWindows[winName]["theWidget"].updateChatBox(text, sender);
	}
},
/* Send a new chat message*/
sendMsg: function(msg){
	this.ioManager.request(msg, "chatty.sendmsg", "endSendMsg");
}

});

dojo.widget.tags.addParseTreeHandler("dojo:ChattyManager");
