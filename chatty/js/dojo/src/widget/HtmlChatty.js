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

dojo.provide("dojo.widget.Chatty");
dojo.provide("dojo.widget.HtmlChatty");



dojo.require("dojo.html");
dojo.require("dojo.style");
dojo.require("dojo.dom");
dojo.require("dojo.fx.html");
dojo.require("dojo.widget.HtmlLayoutPane");
dojo.require("dojo.widget.HtmlChattyResizeHandle");

dojo.widget.HtmlChatty = function(){
	dojo.widget.HtmlLayoutPane.call(this);
}

dojo.inherits(dojo.widget.HtmlChatty, dojo.widget.HtmlLayoutPane);

dojo.lang.extend(dojo.widget.HtmlChatty, {
	widgetType: "Chatty",

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
	constrainToContainer: 0,
	resizeHandle: null,
	chatBox: null,
	sendBox: null,
	sendButton: null,
	divContent: null,
	zindexForeGround: 10000,
	windowManager: null,
	explode: true,
	winName: "",
	templateCssPath: dojo.uri.dojoUri("src/widget/templates/HtmlChatty.css"),	
	/*    properties to manage positionning and size       */
	/* if not set, the positionning is done in css file of the widget*/
	propX: null,
	propY: null,
	propW: null,
	propH: null,

	fillInTemplate: function(){

		if (this.templateCssPath) {
			dojo.style.insertCssFile(this.templateCssPath, null, true);
		}

		dojo.html.addClass(this.domNode, 'dojoFloatingPane');
		if(this.propX & this.propY){
			this.domNode.style.left = this.propX+"px";
			this.domNode.style.left = this.propY+"px";
		}

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

		var elm = document.createElement('div');
		elm.appendChild(document.createTextNode(this.title));
		dojo.html.addClass(elm, 'dojoFloatingPaneDragbar');
		this.dragBar = this.createPane(elm, 'top');
		this.dragBar.ownerPane = this;


		var buttons = document.createElement('div');
		dojo.html.addClass(buttons, 'dojoMyFloatingPaneButtons');
		this.btnMin = document.createElement("img")
		this.btnMin.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_minus.gif";
//		this.btnMax = document.createElement("img");
//		this.btnMax.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_max.gif";
		this.btnClose = document.createElement("img");
		this.btnClose.src = djConfig["baseScriptUri"]+"src/widget/templates/images/window_close.gif";
				
		buttons.appendChild(this.btnMin);
//		buttons.appendChild(this.btnMax);
		buttons.appendChild(this.btnClose);
		
		elm.appendChild(buttons);

		//dojo.event.connect(this.btnMin, 'onclick', this, 'onBtnMin');		
	//	dojo.event.connect(this.btnMax, 'onclick', this, 'onBtnMax');
		dojo.event.connect(this.btnClose, 'onclick', this, 'onBtnClose');

		this.divContent = document.createElement('div');
		dojo.html.addClass(this.divContent, 'dojoMyFloatingPaneDivContent');		
		
		this.chatBox = document.createElement('div');
		dojo.html.addClass(this.chatBox, 'dojoMyFloatingPaneChatBox');

		this.sendBox = document.createElement('textarea');

		this.sendButton = document.createElement('input');
		this.sendButton.type = "submit";
		this.sendButton.value = "Send";
		dojo.html.addClass(this.sendButton, 'dojoMyFloatingPaneSendButton');
		dojo.html.addClass(this.sendBox, 'dojoMyFloatingPaneSendBox');

		this.clientPane.domNode.appendChild(this.divContent);	

		this.divContent.appendChild(this.chatBox);
		this.divContent.appendChild(this.sendBox);
		this.divContent.appendChild(this.sendButton);

		var props = {targetElmId: this.widgetId, minSize:{x: 300, y:200}};

		resDiv = document.createElement('div');
		this.clientPane.domNode.appendChild(resDiv);		
		this.resizeHandle = dojo.widget.fromScript("ChattyResizeHandle",props,resDiv);

		dojo.html.disableSelection(this.dragBar.domNode);
		dojo.event.connect(this.dragBar.domNode, 'onmousedown', this, 'onMyDragStart');
		dojo.event.connect(this.sendButton, 'onclick', this, 'sendMsg');
		dojo.event.connect(this.resizeHandle, 'endSizing', this, 'scrollChatBox');
		dojo.event.connect(this.sendBox, 'onkeypress', this, 'evtKey');
		//this.layoutSoon();
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

	onResized: function(){
		if ( this.hasShadow ) {
			var width = dojo.style.getOuterWidth(this.domNode);
			var height = dojo.style.getOuterHeight(this.domNode);
			dojo.style.setOuterWidth(this.shadow, width);
			dojo.style.setOuterHeight(this.shadow, height);
		}
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

		this.domNode.style.left = x + 'px';
		this.domNode.style.top  = y + 'px';
	},

	onMyDragEnd: function(e){
		dojo.event.disconnect(document, 'onmousemove', this, 'onMyDragMove');
		dojo.event.disconnect(document, 'onmouseup', this, 'onMyDragEnd');
	},
	
	onBtnClose: function(e){
		var thiswidget = this;
		thiswidget.windowManager.onWinChildClose(thiswidget.winName);
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
			 this.wipeInToHeight(300, this.savedHeight);
			this.isMinimized = "max";
			dojo.style.setOuterHeight(this.domNode, this.savedHeight);		
		this.clientPane.show();			
		}			
	},
	
	endWipeIn: function(){
		//this.layoutSoon();
		this.resizeSoon();
		this.domNode.style.overflow = this.savedOverflow;
	},

	setZindex: function(widgetId){
		if (this.widgetId == widgetId){
			this.domNode.style.zIndex = this.windowManager.zindexForeGround;
		}
		else{
			this.domNode.style.zIndex = this.windowManager.zindexBackGround;		
		}
	},

	adviceGetFocus: function(e){
		this.windowManager.setWinFocus(this.widgetId);
	},

	setManager: function(windowManager){

		function setId(invocation){
			invocation.args = [this.widgetId];
			return invocation.proceed();
		}
		this.windowManager = dojo.widget.getWidgetById(windowManager);
		dojo.event.topic.subscribe("/settingFocus", this, "setZindex")
		dojo.event.kwConnect({
	    srcObj:     this.domNode, 
	    srcFunc:    "onclick", 
	    targetObj:  this, 
	    targetFunc: "adviceGetFocus",
	    once:       true
		});
	},
	
	endXplode: function(endNode, anim){
			this.clientPane.show();
			dojo.style.setOpacity(this.clientPane.domNode, 0.1);
			dojo.fx.fadeIn(this.clientPane.domNode, 1300);
			//this.layoutSoon();	
			this.resizeSoon();
			this.windowManager.setWinFocus(this.widgetId);
	},

	MyXplode: function(e){
		var end = this.windowManager.chatty_info.listOfUsers[this.winName]["domNode"];
		if (this.isMinimized == "max"){
			this.sizeBeforeMin = this.getMySize();
			dojo.fx.explode(this.domNode, end, 500);
			this.explode = false;
			this.domNode.style.display="none";
			this.isMinimized = "min";
			this.clientPane.hide();
		}
		else{
			dojo.fx.explode(end,this.domNode, 500);
			this.explode = true;
			this.isMinimized = "max";
			dojo.lang.setTimeout(this, this.endXplode, 600);
		}
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

getMySize: function(){
	if(this.isMinimized=="min"){
		chatWindow = this.sizeBeforeMin;
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
		if(wmsize.size){
			dojo.style.setOuterWidth(this.domNode, wmsize.size.w);
			dojo.style.setOuterHeight(this.domNode, wmsize.size.h);
		}
		if(wmsize["minmax"]=="max"){
			this.show();
		}
		else{
			this.hide();
		}
	}
	else{
		bodyposition = dojo.html.getViewportSize();
		setposition = {"position":{x: parseInt(bodyposition[0]/2), y: parseInt(bodyposition[1]/2)}};
		this.domNode.style.left = setposition.position.x+"px";
		this.domNode.style.top = setposition.position.y+"px";	
	}
	//this.layoutSoon();	
	this.resizeSoon();
}, 


adviceDisconnect: function(){
	this.chatBox.innerHTML="<br/>"+this.winName+" has quit";
	var thiswidget = this;
	thiswidget.windowManager.onWinChildClose(thiswidget.winName);
},
	

sendMsg: function(){
	var tosend = this.sendBox.value;
	msg = {'rcpt':this.winName, 'msg':tosend}; 
	this.windowManager.sendMsg(msg);
	this.sendBox.value ="";
	this.sendBox.focus();
},
updateChatBox: function(msg, sender,lheure){
	var date = new Date();
	var heure = dojo.date.toMilitaryTimeString(date);
	if(lheure) heure = lheure;	

	if(sender == this.winName){
		this.chatBox.innerHTML = this.chatBox.innerHTML+"<br/><span class='chattydatercv'>"+heure+"</span><span class='chattytxtrcv'>"+msg+"</span>";
	}
	else{
		this.chatBox.innerHTML = this.chatBox.innerHTML+"<br/><span class='chattydatesnd'>"+heure+"</span><span class='chattytxtsnd'>"+msg+"</span>";
	}
	this.scrollChatBox();
},

scrollChatBox: function(e){
	this.chatBox.scrollTop = this.chatBox.scrollHeight;
},
/*grab ALT+S to send message*/
evtKey: function(e){
	if(e.altKey && (e.charCode == 115)){
		this.sendMsg();
	};
}
	
	
});

dojo.widget.tags.addParseTreeHandler("dojo:Chatty");
