﻿/*
 Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
(function(){function c(b){var c=this instanceof CKEDITOR.ui.dialog.checkbox;b.hasAttribute(this.id)&&(b=b.getAttribute(this.id),c?this.setValue(e[this.id]["true"]==b.toLowerCase()):this.setValue(b))}function d(b){var c=""===this.getValue(),a=this instanceof CKEDITOR.ui.dialog.checkbox,d=this.getValue();c?b.removeAttribute(this.att||this.id):a?b.setAttribute(this.id,e[this.id][d]):b.setAttribute(this.att||this.id,d)}var e={scrolling:{"true":"yes","false":"no"},frameborder:{"true":"1","false":"0"}};
CKEDITOR.dialog.add("iframe",function(b){var f=b.lang.iframe,a=b.lang.common,e=b.plugins.dialogadvtab;return{title:f.title,minWidth:350,minHeight:260,onShow:function(){this.fakeImage=this.iframeNode=null;var a=this.getSelectedElement();a&&(a.data("cke-real-element-type")&&"iframe"==a.data("cke-real-element-type"))&&(this.fakeImage=a,this.iframeNode=a=b.restoreRealElement(a),this.setupContent(a))},onOk:function(){var a;a=this.fakeImage?this.iframeNode:new CKEDITOR.dom.element("iframe");var c={},d=
{};this.commitContent(a,c,d);a=b.createFakeElement(a,"cke_iframe","iframe",!0);a.setAttributes(d);a.setStyles(c);this.fakeImage?(a.replace(this.fakeImage),b.getSelection().selectElement(a)):b.insertElement(a)},contents:[{id:"info",label:a.generalTab,accessKey:"I",elements:[{type:"vbox",padding:0,children:[{id:"src",type:"text",label:a.url,required:!0,validate:CKEDITOR.dialog.validate.notEmpty(f.noUrl),setup:c,commit:d}]},{type:"hbox",children:[{id:"width",type:"text",style:"width:100%",labelLayout:"vertical",
label:a.width,validate:CKEDITOR.dialog.validate.htmlLength(a.invalidHtmlLength.replace("%1",a.width)),setup:c,commit:d},{id:"height",type:"text",style:"width:100%",labelLayout:"vertical",label:a.height,validate:CKEDITOR.dialog.validate.htmlLength(a.invalidHtmlLength.replace("%1",a.height)),setup:c,commit:d},{id:"align",type:"select","default":"",items:[[a.notSet,""],[a.alignLeft,"left"],[a.alignRight,"right"],[a.alignTop,"top"],[a.alignMiddle,"middle"],[a.alignBottom,"bottom"]],style:"width:100%",
labelLayout:"vertical",label:a.align,setup:function(a,b){c.apply(this,arguments);if(b){var d=b.getAttribute("align");this.setValue(d&&d.toLowerCase()||"")}},commit:function(a,b,c){d.apply(this,arguments);this.getValue()&&(c.align=this.getValue())}}]},{type:"hbox",widths:["50%","50%"],children:[{id:"scrolling",type:"checkbox",label:f.scrolling,setup:c,commit:d},{id:"frameborder",type:"checkbox",label:f.border,setup:c,commit:d}]},{type:"hbox",widths:["50%","50%"],children:[{id:"name",type:"text",label:a.name,
setup:c,commit:d},{id:"title",type:"text",label:a.advisoryTitle,setup:c,commit:d}]},{id:"longdesc",type:"text",label:a.longDescr,setup:c,commit:d}]},e&&e.createAdvancedTab(b,{id:1,classes:1,styles:1})]}})})();