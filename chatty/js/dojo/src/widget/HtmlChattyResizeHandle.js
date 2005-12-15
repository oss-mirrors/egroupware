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

dojo.provide("dojo.widget.ChattyResizeHandle");
dojo.provide("dojo.widget.HtmlChattyResizeHandle");

dojo.require("dojo.widget.*");
dojo.require("dojo.html");
dojo.require("dojo.style");
dojo.require("dojo.dom");
dojo.require("dojo.event");

dojo.widget.HtmlChattyResizeHandle = function(){

	dojo.widget.HtmlWidget.call(this);
}

dojo.inherits(dojo.widget.HtmlChattyResizeHandle, dojo.widget.HtmlWidget);

dojo.lang.extend(dojo.widget.HtmlChattyResizeHandle, {
	widgetType: "ChattyResizeHandle",

	isSizing: false,
	startPoint: null,
	startSize: null,
	shadowResize: null,
	grabImg: null,
	minSize: null, 

	targetElmId: '',
	imgSrc: dojo.uri.dojoUri("src/widget/templates/grabCorner.gif"),

	templateCssPath: dojo.uri.dojoUri("src/widget/templates/HtmlResizeHandle.css"),
	templateString: '<div dojoAttachPoint="domNode"><img dojoAttachPoint="grabImg" /></div>',

	fillInTemplate: function(){

		dojo.style.insertCssFile(this.templateCssPath);

		dojo.html.addClass(this.domNode, 'dojoHtmlResizeHandle');
		dojo.html.addClass(this.grabImg, 'dojoHtmlResizeHandleImage');

		this.grabImg.src = this.imgSrc;
	},

	postCreate: function(){
		dojo.event.connect(this.domNode, "onmousedown", this, "beginSizing");
	},

	beginSizing: function(e){
		if (this.isSizing){ return false; }

		this.targetElm = dojo.widget.getWidgetById(this.targetElmId);
		if (!this.targetElm){ return; }

		var screenX = window.event ? window.event.clientX : e.pageX;
		var screenY = window.event ? window.event.clientY : e.pageY;

		this.isSizing = true;
		this.startPoint  = {'x':e.clientX, 'y':e.clientY};
		this.startSize  = {'w':dojo.style.getOuterWidth(this.targetElm.domNode), 'h':dojo.style.getOuterHeight(this.targetElm.domNode)};

		this.shadowResize = document.createElement("div");
		dojo.html.addClass(this.shadowResize, 'dojoHtmlResizeHandleShadowResize');

		with(this.shadowResize.style){
			top = dojo.style.getAbsoluteY(this.targetElm.domNode, false)+"px";
			left= dojo.style.getAbsoluteX(this.targetElm.domNode, false)+"px";
		}
		dojo.style.setOpacity(this.shadowResize, 0.2);
		dojo.html.body().appendChild(this.shadowResize);

		dojo.event.connect(document.documentElement, "onmousemove", this, "changeSizing");
		dojo.event.connect(document.documentElement, "onmouseup", this, "endSizing");

		e.preventDefault();
	},

	changeSizing: function(e){
		var dx = this.startPoint.x - e.clientX;
		var dy = this.startPoint.y - e.clientY;
		var minX = this.startSize.w - dx;
		var minY = this.startSize.h - dy;
		if ((minX >= this.minSize.x) & (minY >= this.minSize.y)){
			dojo.style.setOuterWidth(this.shadowResize, this.startSize.w - dx);
			dojo.style.setOuterHeight(this.shadowResize, this.startSize.h - dy);
			e.preventDefault();
		}
	},

	endSizing: function(e){
		dojo.html.body().removeChild(this.shadowResize);
		var dx = this.startPoint.x - e.clientX;
		var dy = this.startPoint.y - e.clientY;
		var finalX = this.startSize.w - dx;
		var finalY = this.startSize.h - dy;
		finalX = (finalX < this.minSize.x) ? this.minSize.x : finalX; 
		finalY = (finalY < this.minSize.y) ? this.minSize.y : finalY; 
		this.targetElm.resizeTo(finalX, finalY );
		dojo.event.disconnect(document.documentElement, "onmousemove", this, "changeSizing");
		dojo.event.disconnect(document.documentElement, "onmouseup", this, "endSizing");
		this.isSizing = false;
	}

});

dojo.widget.tags.addParseTreeHandler("dojo:ChattyResizeHandle");
