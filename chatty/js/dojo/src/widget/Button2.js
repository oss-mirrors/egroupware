/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/
dojo.provide("dojo.widget.Button2");
dojo.provide("dojo.widget.DropDownButton2");
dojo.provide("dojo.widget.ComboButton2");
dojo.require("dojo.widget.Widget");

dojo.requireIf("html", "dojo.widget.html.Button2");

dojo.widget.tags.addParseTreeHandler("dojo:button2");
dojo.widget.tags.addParseTreeHandler("dojo:dropdownbutton2");
dojo.widget.tags.addParseTreeHandler("dojo:combobutton2");

dojo.widget.Button2 = function(){
	dojo.widget.Widget.call(this);

	this.widgetType = "Button2";
	this.isContainer = true;
}
dojo.inherits(dojo.widget.Button2, dojo.widget.Widget);

dojo.widget.DropDownButton2 = function(){
	dojo.widget.Widget.call(this);

	this.widgetType = "DropDownButton2";
	this.isContainer = true;
}
dojo.inherits(dojo.widget.DropDownButton2, dojo.widget.Widget);

dojo.widget.ComboButton2 = function(){
	dojo.widget.Widget.call(this);

	this.widgetType = "ComboButton2";
	this.isContainer = true;
}
dojo.inherits(dojo.widget.ComboButton2, dojo.widget.Widget);
