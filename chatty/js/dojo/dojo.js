/*
	Copyright (c) 2004-2005, The Dojo Foundation
	All Rights Reserved

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/
/*
	This is a compiled version of Dojo, built for deployment and not for development.
	To get an editable version, please visit:

		http://dojotoolkit.org

	for documentation and information on getting the source.
*/
var dj_global=this;
function dj_undef(_1,_2){
if(!_2){
_2=dj_global;
}
return (typeof _2[_1]=="undefined");
}
if(dj_undef("djConfig")){
var djConfig={};
}
var dojo;
if(dj_undef("dojo")){
dojo={};
}
dojo.version={major:0,minor:1,patch:0,flag:"+",revision:Number("$Rev: 2361 $".match(/[0-9]+/)[0]),toString:function(){
with(dojo.version){
return major+"."+minor+"."+patch+flag+" ("+revision+")";
}
}};
dojo.evalObjPath=function(_3,_4){
if(typeof _3!="string"){
return dj_global;
}
if(_3.indexOf(".")==-1){
if((dj_undef(_3,dj_global))&&(_4)){
dj_global[_3]={};
}
return dj_global[_3];
}
var _5=_3.split(/\./);
var _6=dj_global;
for(var i=0;i<_5.length;++i){
if(!_4){
_6=_6[_5[i]];
if((typeof _6=="undefined")||(!_6)){
return _6;
}
}else{
if(dj_undef(_5[i],_6)){
_6[_5[i]]={};
}
_6=_6[_5[i]];
}
}
return _6;
};
dojo.errorToString=function(_8){
return ((!dj_undef("message",_8))?_8.message:(dj_undef("description",_8)?_8:_8.description));
};
dojo.raise=function(_9,_a){
if(_a){
_9=_9+": "+dojo.errorToString(_a);
}
var he=dojo.hostenv;
if((!dj_undef("hostenv",dojo))&&(!dj_undef("println",dojo.hostenv))){
dojo.hostenv.println("FATAL: "+_9);
}
throw Error(_9);
};
dj_throw=dj_rethrow=function(m,e){
dojo.deprecated("dj_throw and dj_rethrow deprecated, use dojo.raise instead");
dojo.raise(m,e);
};
dojo.debug=function(){
if(!djConfig.isDebug){
return;
}
var _e=arguments;
if(dj_undef("println",dojo.hostenv)){
dojo.raise("dojo.debug not available (yet?)");
}
var _f=dj_global["jum"]&&!dj_global["jum"].isBrowser;
var s=[(_f?"":"DEBUG: ")];
for(var i=0;i<_e.length;++i){
if(!false&&_e[i] instanceof Error){
var msg="["+_e[i].name+": "+dojo.errorToString(_e[i])+(_e[i].fileName?", file: "+_e[i].fileName:"")+(_e[i].lineNumber?", line: "+_e[i].lineNumber:"")+"]";
}else{
if(typeof _e[i]=="undefined"){
var msg=_e[i];
}else{
if(_e[i]["toString"]){
var msg=_e[i];
}else{
if(dojo.render.html.ie){
var msg="[ActiveXObject]";
}else{
var msg="[unknown]";
}
}
}
}
s.push(msg);
}
if(_f){
jum.debug(s.join(" "));
}else{
dojo.hostenv.println(s.join(" "));
}
};
dojo.debugShallow=function(obj){
if(!djConfig.isDebug){
return;
}
dojo.debug("------------------------------------------------------------");
dojo.debug("Object: "+obj);
for(i in obj){
dojo.debug(i+": "+obj[i]);
}
dojo.debug("------------------------------------------------------------");
};
var dj_debug=dojo.debug;
function dj_eval(s){
return dj_global.eval?dj_global.eval(s):eval(s);
}
dj_unimplemented=dojo.unimplemented=function(_15,_16){
var _17="'"+_15+"' not implemented";
if((!dj_undef(_16))&&(_16)){
_17+=" "+_16;
}
dojo.raise(_17);
};
dj_deprecated=dojo.deprecated=function(_18,_19,_1a){
var _1b="DEPRECATED: "+_18;
if((!dj_undef(_19))&&(_19)){
_1b+=" "+_19;
}
if(!dj_undef(_1a)){
_1b+=" -- will be removed in version"+_1a;
}
dojo.debug(_1b);
};
dojo.inherits=function(_1c,_1d){
if(typeof _1d!="function"){
dojo.raise("superclass: "+_1d+" borken");
}
_1c.prototype=new _1d();
_1c.prototype.constructor=_1c;
_1c.superclass=_1d.prototype;
_1c["super"]=_1d.prototype;
};
dj_inherits=function(_1e,_1f){
dojo.deprecated("dj_inherits deprecated, use dojo.inherits instead");
dojo.inherits(_1e,_1f);
};
dojo.render=(function(){
function vscaffold(_20,_21){
var tmp={capable:false,support:{builtin:false,plugin:false},prefixes:_20};
for(var x in _21){
tmp[x]=false;
}
return tmp;
}
return {name:"",ver:dojo.version,os:{win:false,linux:false,osx:false},html:vscaffold(["html"],["ie","opera","khtml","safari","moz"]),svg:vscaffold(["svg"],["corel","adobe","batik"]),vml:vscaffold(["vml"],["ie"]),swf:vscaffold(["Swf","Flash","Mm"],["mm"]),swt:vscaffold(["Swt"],["ibm"])};
})();
dojo.hostenv=(function(){
var _24={isDebug:false,allowQueryConfig:false,baseScriptUri:"",baseRelativePath:"",libraryScriptUri:"",iePreventClobber:false,ieClobberMinimal:true,preventBackButtonFix:true,searchIds:[],parseWidgets:true};
if(typeof djConfig=="undefined"){
djConfig=_24;
}else{
for(var _25 in _24){
if(typeof djConfig[_25]=="undefined"){
djConfig[_25]=_24[_25];
}
}
}
var djc=djConfig;
function _def(obj,_28,def){
return (dj_undef(_28,obj)?def:obj[_28]);
}
return {name_:"(unset)",version_:"(unset)",pkgFileName:"__package__",loading_modules_:{},loaded_modules_:{},addedToLoadingCount:[],removedFromLoadingCount:[],inFlightCount:0,modulePrefixes_:{dojo:{name:"dojo",value:"src"}},setModulePrefix:function(_2a,_2b){
this.modulePrefixes_[_2a]={name:_2a,value:_2b};
},getModulePrefix:function(_2c){
var mp=this.modulePrefixes_;
if((mp[_2c])&&(mp[_2c]["name"])){
return mp[_2c].value;
}
return _2c;
},getTextStack:[],loadUriStack:[],loadedUris:[],post_load_:false,modulesLoadedListeners:[],getName:function(){
return this.name_;
},getVersion:function(){
return this.version_;
},getText:function(uri){
dojo.unimplemented("getText","uri="+uri);
},getLibraryScriptUri:function(){
dojo.unimplemented("getLibraryScriptUri","");
}};
})();
dojo.hostenv.getBaseScriptUri=function(){
if(djConfig.baseScriptUri.length){
return djConfig.baseScriptUri;
}
var uri=new String(djConfig.libraryScriptUri||djConfig.baseRelativePath);
if(!uri){
dojo.raise("Nothing returned by getLibraryScriptUri(): "+uri);
}
var _30=uri.lastIndexOf("/");
djConfig.baseScriptUri=djConfig.baseRelativePath;
return djConfig.baseScriptUri;
};
dojo.hostenv.setBaseScriptUri=function(uri){
djConfig.baseScriptUri=uri;
};
dojo.hostenv.loadPath=function(_32,_33,cb){
if((_32.charAt(0)=="/")||(_32.match(/^\w+:/))){
dojo.raise("relpath '"+_32+"'; must be relative");
}
var uri=this.getBaseScriptUri()+_32;
try{
return ((!_33)?this.loadUri(uri,cb):this.loadUriAndCheck(uri,_33,cb));
}
catch(e){
dojo.debug(e);
return false;
}
};
dojo.hostenv.loadUri=function(uri,cb){
if(dojo.hostenv.loadedUris[uri]){
return;
}
var _38=this.getText(uri,null,true);
if(_38==null){
return 0;
}
var _39=dj_eval(_38);
return 1;
};
dojo.hostenv.getDepsForEval=function(_3a){
if(!_3a){
_3a="";
}
var _3b=[];
var tmp;
var _3d=[/dojo.hostenv.loadModule\(.*?\)/mg,/dojo.hostenv.require\(.*?\)/mg,/dojo.require\(.*?\)/mg,/dojo.requireIf\([\w\W]*?\)/mg,/dojo.hostenv.conditionalLoadModule\([\w\W]*?\)/mg];
for(var i=0;i<_3d.length;i++){
tmp=_3a.match(_3d[i]);
if(tmp){
for(var x=0;x<tmp.length;x++){
_3b.push(tmp[x]);
}
}
}
return _3b;
};
dojo.hostenv.loadUriAndCheck=function(uri,_41,cb){
var ok=true;
try{
ok=this.loadUri(uri,cb);
}
catch(e){
dojo.debug("failed loading ",uri," with error: ",e);
}
return ((ok)&&(this.findModule(_41,false)))?true:false;
};
dojo.loaded=function(){
};
dojo.hostenv.loaded=function(){
this.post_load_=true;
var mll=this.modulesLoadedListeners;
for(var x=0;x<mll.length;x++){
mll[x]();
}
dojo.loaded();
};
dojo.addOnLoad=function(obj,_47){
if(arguments.length==1){
dojo.hostenv.modulesLoadedListeners.push(obj);
}else{
if(arguments.length>1){
dojo.hostenv.modulesLoadedListeners.push(function(){
obj[_47]();
});
}
}
};
dojo.hostenv.modulesLoaded=function(){
if(this.post_load_){
return;
}
if((this.loadUriStack.length==0)&&(this.getTextStack.length==0)){
if(this.inFlightCount>0){
dojo.debug("files still in flight!");
return;
}
if(typeof setTimeout=="object"){
setTimeout("dojo.hostenv.loaded();",0);
}else{
dojo.hostenv.loaded();
}
}
};
dojo.hostenv.moduleLoaded=function(_48){
var _49=dojo.evalObjPath((_48.split(".").slice(0,-1)).join("."));
this.loaded_modules_[(new String(_48)).toLowerCase()]=_49;
};
dojo.hostenv._global_omit_module_check=false;
dojo.hostenv.loadModule=function(_4a,_4b,_4c){
_4c=this._global_omit_module_check||_4c;
var _4d=this.findModule(_4a,false);
if(_4d){
return _4d;
}
if(dj_undef(_4a,this.loading_modules_)){
this.addedToLoadingCount.push(_4a);
}
this.loading_modules_[_4a]=1;
var _4e=_4a.replace(/\./g,"/")+".js";
var _4f=_4a.split(".");
var _50=_4a.split(".");
for(var i=_4f.length-1;i>0;i--){
var _52=_4f.slice(0,i).join(".");
var _53=this.getModulePrefix(_52);
if(_53!=_52){
_4f.splice(0,i,_53);
break;
}
}
var _54=_4f[_4f.length-1];
if(_54=="*"){
_4a=(_50.slice(0,-1)).join(".");
while(_4f.length){
_4f.pop();
_4f.push(this.pkgFileName);
_4e=_4f.join("/")+".js";
if(_4e.charAt(0)=="/"){
_4e=_4e.slice(1);
}
ok=this.loadPath(_4e,((!_4c)?_4a:null));
if(ok){
break;
}
_4f.pop();
}
}else{
_4e=_4f.join("/")+".js";
_4a=_50.join(".");
var ok=this.loadPath(_4e,((!_4c)?_4a:null));
if((!ok)&&(!_4b)){
_4f.pop();
while(_4f.length){
_4e=_4f.join("/")+".js";
ok=this.loadPath(_4e,((!_4c)?_4a:null));
if(ok){
break;
}
_4f.pop();
_4e=_4f.join("/")+"/"+this.pkgFileName+".js";
if(_4e.charAt(0)=="/"){
_4e=_4e.slice(1);
}
ok=this.loadPath(_4e,((!_4c)?_4a:null));
if(ok){
break;
}
}
}
if((!ok)&&(!_4c)){
dojo.raise("Could not load '"+_4a+"'; last tried '"+_4e+"'");
}
}
if(!_4c){
_4d=this.findModule(_4a,false);
if(!_4d){
dojo.raise("symbol '"+_4a+"' is not defined after loading '"+_4e+"'");
}
}
return _4d;
};
dojo.hostenv.startPackage=function(_56){
var _57=_56.split(/\./);
if(_57[_57.length-1]=="*"){
_57.pop();
}
return dojo.evalObjPath(_57.join("."),true);
};
dojo.hostenv.findModule=function(_58,_59){
if(this.loaded_modules_[(new String(_58)).toLowerCase()]){
return this.loaded_modules_[_58];
}
var _5a=dojo.evalObjPath(_58);
if((typeof _5a!=="undefined")&&(_5a)){
return _5a;
}
if(_59){
dojo.raise("no loaded module named '"+_58+"'");
}
return null;
};
if(typeof window=="undefined"){
dojo.raise("no window object");
}
(function(){
if(djConfig.allowQueryConfig){
var _5b=document.location.toString();
var _5c=_5b.split("?",2);
if(_5c.length>1){
var _5d=_5c[1];
var _5e=_5d.split("&");
for(var x in _5e){
var sp=_5e[x].split("=");
if((sp[0].length>9)&&(sp[0].substr(0,9)=="djConfig.")){
var opt=sp[0].substr(9);
try{
djConfig[opt]=eval(sp[1]);
}
catch(e){
djConfig[opt]=sp[1];
}
}
}
}
}
if(((djConfig["baseScriptUri"]=="")||(djConfig["baseRelativePath"]==""))&&(document&&document.getElementsByTagName)){
var _62=document.getElementsByTagName("script");
var _63=/(__package__|dojo)\.js(\?|$)/i;
for(var i=0;i<_62.length;i++){
var src=_62[i].getAttribute("src");
if(!src){
continue;
}
var m=src.match(_63);
if(m){
root=src.substring(0,m.index);
if(!this["djConfig"]){
djConfig={};
}
if(djConfig["baseScriptUri"]==""){
djConfig["baseScriptUri"]=root;
}
if(djConfig["baseRelativePath"]==""){
djConfig["baseRelativePath"]=root;
}
break;
}
}
}
var dr=dojo.render;
var drh=dojo.render.html;
var dua=drh.UA=navigator.userAgent;
var dav=drh.AV=navigator.appVersion;
var t=true;
var f=false;
drh.capable=t;
drh.support.builtin=t;
dr.ver=parseFloat(drh.AV);
dr.os.mac=dav.indexOf("Macintosh")>=0;
dr.os.win=dav.indexOf("Windows")>=0;
dr.os.linux=dav.indexOf("X11")>=0;
drh.opera=dua.indexOf("Opera")>=0;
drh.khtml=(dav.indexOf("Konqueror")>=0)||(dav.indexOf("Safari")>=0);
drh.safari=dav.indexOf("Safari")>=0;
var _6d=dua.indexOf("Gecko");
drh.mozilla=drh.moz=(_6d>=0)&&(!drh.khtml);
if(drh.mozilla){
drh.geckoVersion=dua.substring(_6d+6,_6d+14);
}
drh.ie=(document.all)&&(!drh.opera);
drh.ie50=drh.ie&&dav.indexOf("MSIE 5.0")>=0;
drh.ie55=drh.ie&&dav.indexOf("MSIE 5.5")>=0;
drh.ie60=drh.ie&&dav.indexOf("MSIE 6.0")>=0;
dr.vml.capable=drh.ie;
dr.svg.capable=f;
dr.svg.support.plugin=f;
dr.svg.support.builtin=f;
dr.svg.adobe=f;
if(document.implementation&&document.implementation.hasFeature&&document.implementation.hasFeature("org.w3c.dom.svg","1.0")){
dr.svg.capable=t;
dr.svg.support.builtin=t;
dr.svg.support.plugin=f;
dr.svg.adobe=f;
}else{
if(navigator.mimeTypes&&navigator.mimeTypes.length>0){
var _6e=navigator.mimeTypes["image/svg+xml"]||navigator.mimeTypes["image/svg"]||navigator.mimeTypes["image/svg-xml"];
if(_6e){
dr.svg.adobe=_6e&&_6e.enabledPlugin&&_6e.enabledPlugin.description&&(_6e.enabledPlugin.description.indexOf("Adobe")>-1);
if(dr.svg.adobe){
dr.svg.capable=t;
dr.svg.support.plugin=t;
}
}
}else{
if(drh.ie&&dr.os.win){
var _6e=f;
try{
var _6f=new ActiveXObject("Adobe.SVGCtl");
_6e=t;
}
catch(e){
}
if(_6e){
dr.svg.capable=t;
dr.svg.support.plugin=t;
dr.svg.adobe=t;
}
}else{
dr.svg.capable=f;
dr.svg.support.plugin=f;
dr.svg.adobe=f;
}
}
}
})();
dojo.hostenv.startPackage("dojo.hostenv");
dojo.hostenv.name_="browser";
dojo.hostenv.searchIds=[];
var DJ_XMLHTTP_PROGIDS=["Msxml2.XMLHTTP","Microsoft.XMLHTTP","Msxml2.XMLHTTP.4.0"];
dojo.hostenv.getXmlhttpObject=function(){
var _70=null;
var _71=null;
try{
_70=new XMLHttpRequest();
}
catch(e){
}
if(!_70){
for(var i=0;i<3;++i){
var _73=DJ_XMLHTTP_PROGIDS[i];
try{
_70=new ActiveXObject(_73);
}
catch(e){
_71=e;
}
if(_70){
DJ_XMLHTTP_PROGIDS=[_73];
break;
}
}
}
if(!_70){
return dojo.raise("XMLHTTP not available",_71);
}
return _70;
};
dojo.hostenv.getText=function(uri,_75,_76){
var _77=this.getXmlhttpObject();
if(_75){
_77.onreadystatechange=function(){
if((4==_77.readyState)&&(_77["status"])){
if(_77.status==200){
dojo.debug("LOADED URI: "+uri);
_75(_77.responseText);
}
}
};
}
_77.open("GET",uri,_75?true:false);
_77.send(null);
if(_75){
return null;
}
if(_77.status==200){
return _77.responseText;
}else{
return null;
}
};
dojo.hostenv.defaultDebugContainerId="dojoDebug";
dojo.hostenv._println_buffer=[];
dojo.hostenv._println_safe=false;
dojo.hostenv.println=function(_78){
if(!dojo.hostenv._println_safe){
dojo.hostenv._println_buffer.push(_78);
}else{
try{
var _79=document.getElementById(djConfig.debugContainerId?djConfig.debugContainerId:dojo.hostenv.defaultDebugContainerId);
if(!_79){
_79=document.getElementsByTagName("body")[0]||document.body;
}
var div=document.createElement("div");
div.appendChild(document.createTextNode(_78));
_79.appendChild(div);
}
catch(e){
try{
document.write("<div>"+_78+"</div>");
}
catch(e2){
window.status=_78;
}
}
}
};
dojo.addOnLoad(function(){
dojo.hostenv._println_safe=true;
while(dojo.hostenv._println_buffer.length>0){
dojo.hostenv.println(dojo.hostenv._println_buffer.shift());
}
});
function dj_addNodeEvtHdlr(_7b,_7c,fp,_7e){
var _7f=_7b["on"+_7c]||function(){
};
_7b["on"+_7c]=function(){
fp.apply(_7b,arguments);
_7f.apply(_7b,arguments);
};
return true;
}
dj_addNodeEvtHdlr(window,"load",function(){
if(dojo.render.html.ie){
dojo.hostenv.makeWidgets();
}
dojo.hostenv.modulesLoaded();
});
dojo.hostenv.makeWidgets=function(){
var _80=[];
if(djConfig.searchIds&&djConfig.searchIds.length>0){
_80=_80.concat(djConfig.searchIds);
}
if(dojo.hostenv.searchIds&&dojo.hostenv.searchIds.length>0){
_80=_80.concat(dojo.hostenv.searchIds);
}
if((djConfig.parseWidgets)||(_80.length>0)){
if(dojo.evalObjPath("dojo.widget.Parse")){
try{
var _81=new dojo.xml.Parse();
if(_80.length>0){
for(var x=0;x<_80.length;x++){
var _83=document.getElementById(_80[x]);
if(!_83){
continue;
}
var _84=_81.parseElement(_83,null,true);
dojo.widget.getParser().createComponents(_84);
}
}else{
if(djConfig.parseWidgets){
var _84=_81.parseElement(document.getElementsByTagName("body")[0]||document.body,null,true);
dojo.widget.getParser().createComponents(_84);
}
}
}
catch(e){
dojo.debug("auto-build-widgets error:",e);
}
}
}
};
dojo.hostenv.modulesLoadedListeners.push(function(){
if(!dojo.render.html.ie){
dojo.hostenv.makeWidgets();
}
});
try{
if(!window["djConfig"]||!window.djConfig["preventBackButtonFix"]){
document.write("<iframe style='border: 0px; width: 1px; height: 1px; position: absolute; bottom: 0px; right: 0px; visibility: visible;' name='djhistory' id='djhistory' src='"+(dojo.hostenv.getBaseScriptUri()+"iframe_history.html")+"'></iframe>");
}
if(dojo.render.html.ie){
document.write("<style>v:*{ behavior:url(#default#VML); }</style>");
document.write("<xml:namespace ns=\"urn:schemas-microsoft-com:vml\" prefix=\"v\"/>");
}
}
catch(e){
}
dojo.hostenv.writeIncludes=function(){
};
dojo.hostenv.byId=dojo.byId=function(id,doc){
if(typeof id=="string"||id instanceof String){
if(!doc){
doc=document;
}
return doc.getElementById(id);
}
return id;
};
dojo.hostenv.byIdArray=dojo.byIdArray=function(){
var ids=[];
for(var i=0;i<arguments.length;i++){
if((arguments[i] instanceof Array)||(typeof arguments[i]=="array")){
for(var j=0;j<arguments[i].length;j++){
ids=ids.concat(dojo.hostenv.byIdArray(arguments[i][j]));
}
}else{
ids.push(dojo.hostenv.byId(arguments[i]));
}
}
return ids;
};
dojo.hostenv.conditionalLoadModule=function(_8a){
var _8b=_8a["common"]||[];
var _8c=(_8a[dojo.hostenv.name_])?_8b.concat(_8a[dojo.hostenv.name_]||[]):_8b.concat(_8a["default"]||[]);
for(var x=0;x<_8c.length;x++){
var _8e=_8c[x];
if(_8e.constructor==Array){
dojo.hostenv.loadModule.apply(dojo.hostenv,_8e);
}else{
dojo.hostenv.loadModule(_8e);
}
}
};
dojo.hostenv.require=dojo.hostenv.loadModule;
dojo.require=function(){
dojo.hostenv.loadModule.apply(dojo.hostenv,arguments);
};
dojo.requireIf=function(){
if((arguments[0]===true)||(arguments[0]=="common")||(dojo.render[arguments[0]].capable)){
var _8f=[];
for(var i=1;i<arguments.length;i++){
_8f.push(arguments[i]);
}
dojo.require.apply(dojo,_8f);
}
};
dojo.conditionalRequire=dojo.requireIf;
dojo.kwCompoundRequire=function(){
dojo.hostenv.conditionalLoadModule.apply(dojo.hostenv,arguments);
};
dojo.hostenv.provide=dojo.hostenv.startPackage;
dojo.provide=function(){
return dojo.hostenv.startPackage.apply(dojo.hostenv,arguments);
};
dojo.setModulePrefix=function(_91,_92){
return dojo.hostenv.setModulePrefix(_91,_92);
};
dojo.profile={start:function(){
},end:function(){
},dump:function(){
}};
dojo.provide("dojo.lang");
dojo.provide("dojo.AdapterRegistry");
dojo.provide("dojo.lang.Lang");
dojo.lang.mixin=function(obj,_94){
var _95=[];
for(var x in _94){
if(typeof _95[x]=="undefined"||_95[x]!=_94[x]){
obj[x]=_94[x];
}
}
return obj;
};
dojo.lang.extend=function(_97,_98){
this.mixin(_97.prototype,_98);
};
dojo.lang.extendPrototype=function(obj,_9a){
this.extend(obj.constructor,_9a);
};
dojo.lang.anonCtr=0;
dojo.lang.anon={};
dojo.lang.nameAnonFunc=function(_9b,_9c){
var nso=(_9c||dojo.lang.anon);
if((dj_global["djConfig"])&&(djConfig["slowAnonFuncLookups"]==true)){
for(var x in nso){
if(nso[x]===_9b){
return x;
}
}
}
var ret="__"+dojo.lang.anonCtr++;
while(typeof nso[ret]!="undefined"){
ret="__"+dojo.lang.anonCtr++;
}
nso[ret]=_9b;
return ret;
};
dojo.lang.hitch=function(_a0,_a1){
if(dojo.lang.isString(_a1)){
var fcn=_a0[_a1];
}else{
var fcn=_a1;
}
return function(){
return fcn.apply(_a0,arguments);
};
};
dojo.lang.setTimeout=function(_a3,_a4){
var _a5=window,argsStart=2;
if(!dojo.lang.isFunction(_a3)){
_a5=_a3;
_a3=_a4;
_a4=arguments[2];
argsStart++;
}
if(dojo.lang.isString(_a3)){
_a3=_a5[_a3];
}
var _a6=[];
for(var i=argsStart;i<arguments.length;i++){
_a6.push(arguments[i]);
}
return setTimeout(function(){
_a3.apply(_a5,_a6);
},_a4);
};
dojo.lang.isObject=function(wh){
return typeof wh=="object"||dojo.lang.isArray(wh)||dojo.lang.isFunction(wh);
};
dojo.lang.isArray=function(wh){
return (wh instanceof Array||typeof wh=="array");
};
dojo.lang.isArrayLike=function(wh){
if(dojo.lang.isString(wh)){
return false;
}
if(dojo.lang.isArray(wh)){
return true;
}
if(dojo.lang.isNumber(wh.length)&&isFinite(wh)){
return true;
}
return false;
};
dojo.lang.isFunction=function(wh){
return (wh instanceof Function||typeof wh=="function");
};
dojo.lang.isString=function(wh){
return (wh instanceof String||typeof wh=="string");
};
dojo.lang.isAlien=function(wh){
return !dojo.lang.isFunction()&&/\{\s*\[native code\]\s*\}/.test(String(wh));
};
dojo.lang.isBoolean=function(wh){
return (wh instanceof Boolean||typeof wh=="boolean");
};
dojo.lang.isNumber=function(wh){
return (wh instanceof Number||typeof wh=="number");
};
dojo.lang.isUndefined=function(wh){
return ((wh==undefined)&&(typeof wh=="undefined"));
};
dojo.lang.whatAmI=function(wh){
try{
if(dojo.lang.isArray(wh)){
return "array";
}
if(dojo.lang.isFunction(wh)){
return "function";
}
if(dojo.lang.isString(wh)){
return "string";
}
if(dojo.lang.isNumber(wh)){
return "number";
}
if(dojo.lang.isBoolean(wh)){
return "boolean";
}
if(dojo.lang.isAlien(wh)){
return "alien";
}
if(dojo.lang.isUndefined(wh)){
return "undefined";
}
for(var _b2 in dojo.lang.whatAmI.custom){
if(dojo.lang.whatAmI.custom[_b2](wh)){
return _b2;
}
}
if(dojo.lang.isObject(wh)){
return "object";
}
}
catch(E){
}
return "unknown";
};
dojo.lang.whatAmI.custom={};
dojo.lang.find=function(arr,val,_b5){
if(!dojo.lang.isArray(arr)&&dojo.lang.isArray(val)){
var a=arr;
arr=val;
val=a;
}
var _b7=dojo.lang.isString(arr);
if(_b7){
arr=arr.split("");
}
if(_b5){
for(var i=0;i<arr.length;++i){
if(arr[i]===val){
return i;
}
}
}else{
for(var i=0;i<arr.length;++i){
if(arr[i]==val){
return i;
}
}
}
return -1;
};
dojo.lang.indexOf=dojo.lang.find;
dojo.lang.findLast=function(arr,val,_bb){
if(!dojo.lang.isArray(arr)&&dojo.lang.isArray(val)){
var a=arr;
arr=val;
val=a;
}
var _bd=dojo.lang.isString(arr);
if(_bd){
arr=arr.split("");
}
if(_bb){
for(var i=arr.length-1;i>=0;i--){
if(arr[i]===val){
return i;
}
}
}else{
for(var i=arr.length-1;i>=0;i--){
if(arr[i]==val){
return i;
}
}
}
return -1;
};
dojo.lang.lastIndexOf=dojo.lang.findLast;
dojo.lang.inArray=function(arr,val){
return dojo.lang.find(arr,val)>-1;
};
dojo.lang.getNameInObj=function(ns,_c2){
if(!ns){
ns=dj_global;
}
for(var x in ns){
if(ns[x]===_c2){
return new String(x);
}
}
return null;
};
dojo.lang.has=function(obj,_c5){
return (typeof obj[_c5]!=="undefined");
};
dojo.lang.isEmpty=function(obj){
if(dojo.lang.isObject(obj)){
var tmp={};
var _c8=0;
for(var x in obj){
if(obj[x]&&(!tmp[x])){
_c8++;
break;
}
}
return (_c8==0);
}else{
if(dojo.lang.isArray(obj)||dojo.lang.isString(obj)){
return obj.length==0;
}
}
};
dojo.lang.forEach=function(arr,_cb,_cc){
var _cd=dojo.lang.isString(arr);
if(_cd){
arr=arr.split("");
}
var il=arr.length;
for(var i=0;i<((_cc)?il:arr.length);i++){
if(_cb(arr[i],i,arr)=="break"){
break;
}
}
};
dojo.lang.map=function(arr,obj,_d2){
var _d3=dojo.lang.isString(arr);
if(_d3){
arr=arr.split("");
}
if(dojo.lang.isFunction(obj)&&(!_d2)){
_d2=obj;
obj=dj_global;
}else{
if(dojo.lang.isFunction(obj)&&_d2){
var _d4=obj;
obj=_d2;
_d2=_d4;
}
}
if(Array.map){
var _d5=Array.map(arr,_d2,obj);
}else{
var _d5=[];
for(var i=0;i<arr.length;++i){
_d5.push(_d2.call(obj,arr[i]));
}
}
if(_d3){
return _d5.join("");
}else{
return _d5;
}
};
dojo.lang.tryThese=function(){
for(var x=0;x<arguments.length;x++){
try{
if(typeof arguments[x]=="function"){
var ret=(arguments[x]());
if(ret){
return ret;
}
}
}
catch(e){
dojo.debug(e);
}
}
};
dojo.lang.delayThese=function(_d9,cb,_db,_dc){
if(!_d9.length){
if(typeof _dc=="function"){
_dc();
}
return;
}
if((typeof _db=="undefined")&&(typeof cb=="number")){
_db=cb;
cb=function(){
};
}else{
if(!cb){
cb=function(){
};
if(!_db){
_db=0;
}
}
}
setTimeout(function(){
(_d9.shift())();
cb();
dojo.lang.delayThese(_d9,cb,_db,_dc);
},_db);
};
dojo.lang.shallowCopy=function(obj){
var ret={},key;
for(key in obj){
if(dojo.lang.isUndefined(ret[key])){
ret[key]=obj[key];
}
}
return ret;
};
dojo.lang.every=function(arr,_e0,_e1){
var _e2=dojo.lang.isString(arr);
if(_e2){
arr=arr.split("");
}
if(Array.every){
return Array.every(arr,_e0,_e1);
}else{
if(!_e1){
if(arguments.length>=3){
dojo.raise("thisObject doesn't exist!");
}
_e1=dj_global;
}
for(var i=0;i<arr.length;i++){
if(!_e0.call(_e1,arr[i],i,arr)){
return false;
}
}
return true;
}
};
dojo.lang.some=function(arr,_e5,_e6){
var _e7=dojo.lang.isString(arr);
if(_e7){
arr=arr.split("");
}
if(Array.some){
return Array.some(arr,_e5,_e6);
}else{
if(!_e6){
if(arguments.length>=3){
dojo.raise("thisObject doesn't exist!");
}
_e6=dj_global;
}
for(var i=0;i<arr.length;i++){
if(_e5.call(_e6,arr[i],i,arr)){
return true;
}
}
return false;
}
};
dojo.lang.filter=function(arr,_ea,_eb){
var _ec=dojo.lang.isString(arr);
if(_ec){
arr=arr.split("");
}
if(Array.filter){
var _ed=Array.filter(arr,_ea,_eb);
}else{
if(!_eb){
if(arguments.length>=3){
dojo.raise("thisObject doesn't exist!");
}
_eb=dj_global;
}
var _ed=[];
for(var i=0;i<arr.length;i++){
if(_ea.call(_eb,arr[i],i,arr)){
_ed.push(arr[i]);
}
}
}
if(_ec){
return _ed.join("");
}else{
return _ed;
}
};
dojo.AdapterRegistry=function(){
this.pairs=[];
};
dojo.lang.extend(dojo.AdapterRegistry,{register:function(_ef,_f0,_f1,_f2){
if(_f2){
this.pairs.unshift([_ef,_f0,_f1]);
}else{
this.pairs.push([_ef,_f0,_f1]);
}
},match:function(){
for(var i=0;i<this.pairs.length;i++){
var _f4=this.pairs[i];
if(_f4[1].apply(this,arguments)){
return _f4[2].apply(this,arguments);
}
}
dojo.raise("No match found");
},unregister:function(_f5){
for(var i=0;i<this.pairs.length;i++){
var _f7=this.pairs[i];
if(_f7[0]==_f5){
this.pairs.splice(i,1);
return true;
}
}
return false;
}});
dojo.lang.reprRegistry=new dojo.AdapterRegistry();
dojo.lang.registerRepr=function(_f8,_f9,_fa,_fb){
dojo.lang.reprRegistry.register(_f8,_f9,_fa,_fb);
};
dojo.lang.repr=function(obj){
if(typeof (obj)=="undefined"){
return "undefined";
}else{
if(obj===null){
return "null";
}
}
try{
if(typeof (obj["__repr__"])=="function"){
return obj["__repr__"]();
}else{
if((typeof (obj["repr"])=="function")&&(obj.repr!=arguments.callee)){
return obj["repr"]();
}
}
return dojo.lang.reprRegistry.match(obj);
}
catch(e){
if(typeof (obj.NAME)=="string"&&(obj.toString==Function.prototype.toString||obj.toString==Object.prototype.toString)){
return o.NAME;
}
}
if(typeof (obj)=="function"){
obj=(obj+"").replace(/^\s+/,"");
var idx=obj.indexOf("{");
if(idx!=-1){
obj=obj.substr(0,idx)+"{...}";
}
}
return obj+"";
};
dojo.lang.reprArrayLike=function(arr){
try{
var na=dojo.lang.map(arr,dojo.lang.repr);
return "["+na.join(", ")+"]";
}
catch(e){
}
};
dojo.lang.reprString=function(str){
return ("\""+str.replace(/(["\\])/g,"\\$1")+"\"").replace(/[\f]/g,"\\f").replace(/[\b]/g,"\\b").replace(/[\n]/g,"\\n").replace(/[\t]/g,"\\t").replace(/[\r]/g,"\\r");
};
dojo.lang.reprNumber=function(num){
return num+"";
};
(function(){
var m=dojo.lang;
m.registerRepr("arrayLike",m.isArrayLike,m.reprArrayLike);
m.registerRepr("string",m.isString,m.reprString);
m.registerRepr("numbers",m.isNumber,m.reprNumber);
m.registerRepr("boolean",m.isBoolean,m.reprNumber);
})();
dojo.lang.unnest=function(){
var out=[];
for(var i=0;i<arguments.length;i++){
if(dojo.lang.isArrayLike(arguments[i])){
var add=dojo.lang.unnest.apply(this,arguments[i]);
out=out.concat(add);
}else{
out.push(arguments[i]);
}
}
return out;
};
dojo.require("dojo.lang");
dojo.provide("dojo.event");
dojo.event=new function(){
this.canTimeout=dojo.lang.isFunction(dj_global["setTimeout"])||dojo.lang.isAlien(dj_global["setTimeout"]);
this.nameAnonFunc=dojo.lang.nameAnonFunc;
this.createFunctionPair=function(obj,cb){
var ret=[];
if(typeof obj=="function"){
ret[1]=dojo.event.nameAnonFunc(obj,dj_global);
ret[0]=dj_global;
return ret;
}else{
if((typeof obj=="object")&&(typeof cb=="string")){
return [obj,cb];
}else{
if((typeof obj=="object")&&(typeof cb=="function")){
ret[1]=dojo.event.nameAnonFunc(cb,obj);
ret[0]=obj;
return ret;
}
}
}
return null;
};
this.matchSignature=function(args,_10a){
var end=Math.min(args.length,_10a.length);
for(var x=0;x<end;x++){
if(compareTypes){
if((typeof args[x]).toLowerCase()!=(typeof _10a[x])){
return false;
}
}else{
if((typeof args[x]).toLowerCase()!=_10a[x].toLowerCase()){
return false;
}
}
}
return true;
};
this.matchSignatureSets=function(args){
for(var x=1;x<arguments.length;x++){
if(this.matchSignature(args,arguments[x])){
return true;
}
}
return false;
};
function interpolateArgs(args){
var ao={srcObj:dj_global,srcFunc:null,adviceObj:dj_global,adviceFunc:null,aroundObj:null,aroundFunc:null,adviceType:(args.length>2)?args[0]:"after",precedence:"last",once:false,delay:null,rate:0,adviceMsg:false};
switch(args.length){
case 0:
return;
case 1:
return;
case 2:
ao.srcFunc=args[0];
ao.adviceFunc=args[1];
break;
case 3:
if((typeof args[0]=="object")&&(typeof args[1]=="string")&&(typeof args[2]=="string")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
}else{
if((typeof args[1]=="string")&&(typeof args[2]=="string")){
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
}else{
if((typeof args[0]=="object")&&(typeof args[1]=="string")&&(typeof args[2]=="function")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
var _111=dojo.event.nameAnonFunc(args[2],ao.adviceObj);
ao.adviceObj[_111]=args[2];
ao.adviceFunc=_111;
}else{
if((typeof args[0]=="function")&&(typeof args[1]=="object")&&(typeof args[2]=="string")){
ao.adviceType="after";
ao.srcObj=dj_global;
var _111=dojo.event.nameAnonFunc(args[0],ao.srcObj);
ao.srcObj[_111]=args[0];
ao.srcFunc=_111;
ao.adviceObj=args[1];
ao.adviceFunc=args[2];
}
}
}
}
break;
case 4:
if((typeof args[0]=="object")&&(typeof args[2]=="object")){
ao.adviceType="after";
ao.srcObj=args[0];
ao.srcFunc=args[1];
ao.adviceObj=args[2];
ao.adviceFunc=args[3];
}else{
if((typeof args[1]).toLowerCase()=="object"){
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=dj_global;
ao.adviceFunc=args[3];
}else{
if((typeof args[2]).toLowerCase()=="object"){
ao.srcObj=dj_global;
ao.srcFunc=args[1];
ao.adviceObj=args[2];
ao.adviceFunc=args[3];
}else{
ao.srcObj=ao.adviceObj=ao.aroundObj=dj_global;
ao.srcFunc=args[1];
ao.adviceFunc=args[2];
ao.aroundFunc=args[3];
}
}
}
break;
case 6:
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=args[3];
ao.adviceFunc=args[4];
ao.aroundFunc=args[5];
ao.aroundObj=dj_global;
break;
default:
ao.srcObj=args[1];
ao.srcFunc=args[2];
ao.adviceObj=args[3];
ao.adviceFunc=args[4];
ao.aroundObj=args[5];
ao.aroundFunc=args[6];
ao.once=args[7];
ao.delay=args[8];
ao.rate=args[9];
ao.adviceMsg=args[10];
break;
}
if((typeof ao.srcFunc).toLowerCase()!="string"){
ao.srcFunc=dojo.lang.getNameInObj(ao.srcObj,ao.srcFunc);
}
if((typeof ao.adviceFunc).toLowerCase()!="string"){
ao.adviceFunc=dojo.lang.getNameInObj(ao.adviceObj,ao.adviceFunc);
}
if((ao.aroundObj)&&((typeof ao.aroundFunc).toLowerCase()!="string")){
ao.aroundFunc=dojo.lang.getNameInObj(ao.aroundObj,ao.aroundFunc);
}
if(!ao.srcObj){
dojo.raise("bad srcObj for srcFunc: "+ao.srcFunc);
}
if(!ao.adviceObj){
dojo.raise("bad adviceObj for adviceFunc: "+ao.adviceFunc);
}
return ao;
}
this.connect=function(){
var ao=interpolateArgs(arguments);
var mjp=dojo.event.MethodJoinPoint.getForMethod(ao.srcObj,ao.srcFunc);
if(ao.adviceFunc){
var mjp2=dojo.event.MethodJoinPoint.getForMethod(ao.adviceObj,ao.adviceFunc);
}
mjp.kwAddAdvice(ao);
return mjp;
};
this.connectBefore=function(){
var args=["before"];
for(var i=0;i<arguments.length;i++){
args.push(arguments[i]);
}
return this.connect.apply(this,args);
};
this.connectAround=function(){
var args=["around"];
for(var i=0;i<arguments.length;i++){
args.push(arguments[i]);
}
return this.connect.apply(this,args);
};
this.kwConnectImpl_=function(_119,_11a){
var fn=(_11a)?"disconnect":"connect";
if(typeof _119["srcFunc"]=="function"){
_119.srcObj=_119["srcObj"]||dj_global;
var _11c=dojo.event.nameAnonFunc(_119.srcFunc,_119.srcObj);
_119.srcFunc=_11c;
}
if(typeof _119["adviceFunc"]=="function"){
_119.adviceObj=_119["adviceObj"]||dj_global;
var _11c=dojo.event.nameAnonFunc(_119.adviceFunc,_119.adviceObj);
_119.adviceFunc=_11c;
}
return dojo.event[fn]((_119["type"]||_119["adviceType"]||"after"),_119["srcObj"]||dj_global,_119["srcFunc"],_119["adviceObj"]||_119["targetObj"]||dj_global,_119["adviceFunc"]||_119["targetFunc"],_119["aroundObj"],_119["aroundFunc"],_119["once"],_119["delay"],_119["rate"],_119["adviceMsg"]||false);
};
this.kwConnect=function(_11d){
return this.kwConnectImpl_(_11d,false);
};
this.disconnect=function(){
var ao=interpolateArgs(arguments);
if(!ao.adviceFunc){
return;
}
var mjp=dojo.event.MethodJoinPoint.getForMethod(ao.srcObj,ao.srcFunc);
return mjp.removeAdvice(ao.adviceObj,ao.adviceFunc,ao.adviceType,ao.once);
};
this.kwDisconnect=function(_120){
return this.kwConnectImpl_(_120,true);
};
};
dojo.event.MethodInvocation=function(_121,obj,args){
this.jp_=_121;
this.object=obj;
this.args=[];
for(var x=0;x<args.length;x++){
this.args[x]=args[x];
}
this.around_index=-1;
};
dojo.event.MethodInvocation.prototype.proceed=function(){
this.around_index++;
if(this.around_index>=this.jp_.around.length){
return this.jp_.object[this.jp_.methodname].apply(this.jp_.object,this.args);
}else{
var ti=this.jp_.around[this.around_index];
var mobj=ti[0]||dj_global;
var meth=ti[1];
return mobj[meth].call(mobj,this);
}
};
dojo.event.MethodJoinPoint=function(obj,_129){
this.object=obj||dj_global;
this.methodname=_129;
this.methodfunc=this.object[_129];
this.before=[];
this.after=[];
this.around=[];
};
dojo.event.MethodJoinPoint.getForMethod=function(obj,_12b){
if(!obj){
obj=dj_global;
}
if(!obj[_12b]){
obj[_12b]=function(){
};
}else{
if((!dojo.lang.isFunction(obj[_12b]))&&(!dojo.lang.isAlien(obj[_12b]))){
return null;
}
}
var _12c=_12b+"$joinpoint";
var _12d=_12b+"$joinpoint$method";
var _12e=obj[_12c];
if(!_12e){
var _12f=false;
if(dojo.event["browser"]){
if((obj["attachEvent"])||(obj["nodeType"])||(obj["addEventListener"])){
_12f=true;
dojo.event.browser.addClobberNodeAttrs(obj,[_12c,_12d,_12b]);
}
}
obj[_12d]=obj[_12b];
_12e=obj[_12c]=new dojo.event.MethodJoinPoint(obj,_12d);
obj[_12b]=function(){
var args=[];
if((_12f)&&(!arguments.length)&&(window.event)){
args.push(dojo.event.browser.fixEvent(window.event));
}else{
for(var x=0;x<arguments.length;x++){
if((x==0)&&(_12f)&&(dojo.event.browser.isEvent(arguments[x]))){
args.push(dojo.event.browser.fixEvent(arguments[x]));
}else{
args.push(arguments[x]);
}
}
}
return _12e.run.apply(_12e,args);
};
}
return _12e;
};
dojo.event.MethodJoinPoint.prototype.unintercept=function(){
this.object[this.methodname]=this.methodfunc;
};
dojo.event.MethodJoinPoint.prototype.run=function(){
var obj=this.object||dj_global;
var args=arguments;
var _134=[];
for(var x=0;x<args.length;x++){
_134[x]=args[x];
}
var _136=function(marr){
if(!marr){
dojo.debug("Null argument to unrollAdvice()");
return;
}
var _138=marr[0]||dj_global;
var _139=marr[1];
if(!_138[_139]){
throw new Error("function \""+_139+"\" does not exist on \""+_138+"\"");
}
var _13a=marr[2]||dj_global;
var _13b=marr[3];
var msg=marr[6];
var _13d;
var to={args:[],jp_:this,object:obj,proceed:function(){
return _138[_139].apply(_138,to.args);
}};
to.args=_134;
var _13f=parseInt(marr[4]);
var _140=((!isNaN(_13f))&&(marr[4]!==null)&&(typeof marr[4]!="undefined"));
if(marr[5]){
var rate=parseInt(marr[5]);
var cur=new Date();
var _143=false;
if((marr["last"])&&((cur-marr.last)<=rate)){
if(dojo.event.canTimeout){
if(marr["delayTimer"]){
clearTimeout(marr.delayTimer);
}
var tod=parseInt(rate*2);
var mcpy=dojo.lang.shallowCopy(marr);
marr.delayTimer=setTimeout(function(){
mcpy[5]=0;
_136(mcpy);
},tod);
}
return;
}else{
marr.last=cur;
}
}
if(_13b){
_13a[_13b].call(_13a,to);
}else{
if((_140)&&((dojo.render.html)||(dojo.render.svg))){
dj_global["setTimeout"](function(){
if(msg){
_138[_139].call(_138,to);
}else{
_138[_139].apply(_138,args);
}
},_13f);
}else{
if(msg){
_138[_139].call(_138,to);
}else{
_138[_139].apply(_138,args);
}
}
}
};
if(this.before.length>0){
dojo.lang.forEach(this.before,_136,true);
}
var _146;
if(this.around.length>0){
var mi=new dojo.event.MethodInvocation(this,obj,args);
_146=mi.proceed();
}else{
if(this.methodfunc){
_146=this.object[this.methodname].apply(this.object,args);
}
}
if(this.after.length>0){
dojo.lang.forEach(this.after,_136,true);
}
return (this.methodfunc)?_146:null;
};
dojo.event.MethodJoinPoint.prototype.getArr=function(kind){
var arr=this.after;
if((typeof kind=="string")&&(kind.indexOf("before")!=-1)){
arr=this.before;
}else{
if(kind=="around"){
arr=this.around;
}
}
return arr;
};
dojo.event.MethodJoinPoint.prototype.kwAddAdvice=function(args){
this.addAdvice(args["adviceObj"],args["adviceFunc"],args["aroundObj"],args["aroundFunc"],args["adviceType"],args["precedence"],args["once"],args["delay"],args["rate"],args["adviceMsg"]);
};
dojo.event.MethodJoinPoint.prototype.addAdvice=function(_14b,_14c,_14d,_14e,_14f,_150,once,_152,rate,_154){
var arr=this.getArr(_14f);
if(!arr){
dojo.raise("bad this: "+this);
}
var ao=[_14b,_14c,_14d,_14e,_152,rate,_154];
if(once){
if(this.hasAdvice(_14b,_14c,_14f,arr)>=0){
return;
}
}
if(_150=="first"){
arr.unshift(ao);
}else{
arr.push(ao);
}
};
dojo.event.MethodJoinPoint.prototype.hasAdvice=function(_157,_158,_159,arr){
if(!arr){
arr=this.getArr(_159);
}
var ind=-1;
for(var x=0;x<arr.length;x++){
if((arr[x][0]==_157)&&(arr[x][1]==_158)){
ind=x;
}
}
return ind;
};
dojo.event.MethodJoinPoint.prototype.removeAdvice=function(_15d,_15e,_15f,once){
var arr=this.getArr(_15f);
var ind=this.hasAdvice(_15d,_15e,_15f,arr);
if(ind==-1){
return false;
}
while(ind!=-1){
arr.splice(ind,1);
if(once){
break;
}
ind=this.hasAdvice(_15d,_15e,_15f,arr);
}
return true;
};
dojo.require("dojo.event");
dojo.provide("dojo.event.topic");
dojo.event.topic=new function(){
this.topics={};
this.getTopic=function(_163){
if(!this.topics[_163]){
this.topics[_163]=new this.TopicImpl(_163);
}
return this.topics[_163];
};
this.registerPublisher=function(_164,obj,_166){
var _164=this.getTopic(_164);
_164.registerPublisher(obj,_166);
};
this.subscribe=function(_167,obj,_169){
var _167=this.getTopic(_167);
_167.subscribe(obj,_169);
};
this.unsubscribe=function(_16a,obj,_16c){
var _16a=this.getTopic(_16a);
_16a.unsubscribe(obj,_16c);
};
this.publish=function(_16d,_16e){
var _16d=this.getTopic(_16d);
var args=[];
if((arguments.length==2)&&(_16e.length)&&(typeof _16e!="string")){
args=_16e;
}else{
var args=[];
for(var x=1;x<arguments.length;x++){
args.push(arguments[x]);
}
}
_16d.sendMessage.apply(_16d,args);
};
};
dojo.event.topic.TopicImpl=function(_171){
this.topicName=_171;
var self=this;
self.subscribe=function(_173,_174){
dojo.event.connect("before",self,"sendMessage",_173,_174);
};
self.unsubscribe=function(_175,_176){
dojo.event.disconnect("before",self,"sendMessage",_175,_176);
};
self.registerPublisher=function(_177,_178){
dojo.event.connect(_177,_178,self,"sendMessage");
};
self.sendMessage=function(_179){
};
};
dojo.provide("dojo.event.browser");
dojo.require("dojo.event");
dojo_ie_clobber=new function(){
this.clobberArr=["data","onload","onmousedown","onmouseup","onmouseover","onmouseout","onmousemove","onclick","ondblclick","onfocus","onblur","onkeypress","onkeydown","onkeyup","onsubmit","onreset","onselect","onchange","onselectstart","ondragstart","oncontextmenu"];
this.exclusions=[];
this.clobberList={};
this.clobberNodes=[];
this.addClobberAttr=function(type){
if(dojo.render.html.ie){
if(this.clobberList[type]!="set"){
this.clobberArr.push(type);
this.clobberList[type]="set";
}
}
};
this.addExclusionID=function(id){
this.exclusions.push(id);
};
if(dojo.render.html.ie){
for(var x=0;x<this.clobberArr.length;x++){
this.clobberList[this.clobberArr[x]]="set";
}
}
function nukeProp(node,prop){
try{
node[prop]=null;
}
catch(e){
}
try{
delete node[prop];
}
catch(e){
}
try{
node.removeAttribute(prop);
}
catch(e){
}
}
this.clobber=function(_17f){
for(var x=0;x<this.exclusions.length;x++){
try{
var tn=document.getElementById(this.exclusions[x]);
tn.parentNode.removeChild(tn);
}
catch(e){
}
}
var na;
var tna;
if(_17f){
tna=_17f.getElementsByTagName("*");
na=[_17f];
for(var x=0;x<tna.length;x++){
if(!djConfig.ieClobberMinimal){
na.push(tna[x]);
}else{
if(tna[x]["__doClobber__"]){
na.push(tna[x]);
}
}
}
}else{
try{
window.onload=null;
}
catch(e){
}
na=(this.clobberNodes.length)?this.clobberNodes:document.all;
}
tna=null;
var _184={};
for(var i=na.length-1;i>=0;i=i-1){
var el=na[i];
if(djConfig.ieClobberMinimal){
if(el["__clobberAttrs__"]){
for(var j=0;j<el.__clobberAttrs__.length;j++){
nukeProp(el,el.__clobberAttrs__[j]);
}
nukeProp(el,"__clobberAttrs__");
nukeProp(el,"__doClobber__");
}
}else{
for(var p=this.clobberArr.length-1;p>=0;p=p-1){
var ta=this.clobberArr[p];
nukeProp(el,ta);
}
}
}
na=null;
};
};
if((dojo.render.html.ie)&&((!dojo.hostenv.ie_prevent_clobber_)||(djConfig.ieClobberMinimal))){
window.onunload=function(){
dojo_ie_clobber.clobber();
try{
if((dojo["widget"])&&(dojo.widget["manager"])){
dojo.widget.manager.destroyAll();
}
}
catch(e){
}
try{
window.onload=null;
}
catch(e){
}
try{
window.onunload=null;
}
catch(e){
}
dojo_ie_clobber.clobberNodes=[];
};
}
dojo.event.browser=new function(){
var _18a=0;
this.clean=function(node){
if(dojo.render.html.ie){
dojo_ie_clobber.clobber(node);
}
};
this.addClobberAttr=function(type){
dojo_ie_clobber.addClobberAttr(type);
};
this.addClobberAttrs=function(){
for(var x=0;x<arguments.length;x++){
this.addClobberAttr(arguments[x]);
}
};
this.addClobberNode=function(node){
if(djConfig.ieClobberMinimal){
if(!node["__doClobber__"]){
node.__doClobber__=true;
dojo_ie_clobber.clobberNodes.push(node);
node.__clobberAttrs__=[];
}
}
};
this.addClobberNodeAttrs=function(node,_190){
this.addClobberNode(node);
if(djConfig.ieClobberMinimal){
for(var x=0;x<_190.length;x++){
node.__clobberAttrs__.push(_190[x]);
}
}else{
this.addClobberAttrs.apply(this,_190);
}
};
this.removeListener=function(node,_193,fp,_195){
if(!_195){
var _195=false;
}
_193=_193.toLowerCase();
if(_193.substr(0,2)=="on"){
_193=_193.substr(2);
}
if(node.removeEventListener){
node.removeEventListener(_193,fp,_195);
}
};
this.addListener=function(node,_197,fp,_199,_19a){
if(!node){
return;
}
if(!_199){
var _199=false;
}
_197=_197.toLowerCase();
if(_197.substr(0,2)!="on"){
_197="on"+_197;
}
if(!_19a){
var _19b=function(evt){
if(!evt){
evt=window.event;
}
var ret=fp(dojo.event.browser.fixEvent(evt));
if(_199){
dojo.event.browser.stopEvent(evt);
}
return ret;
};
}else{
_19b=fp;
}
if(node.addEventListener){
node.addEventListener(_197.substr(2),_19b,_199);
return _19b;
}else{
if(typeof node[_197]=="function"){
var _19e=node[_197];
node[_197]=function(e){
_19e(e);
return _19b(e);
};
}else{
node[_197]=_19b;
}
if(dojo.render.html.ie){
this.addClobberNodeAttrs(node,[_197]);
}
return _19b;
}
};
this.isEvent=function(obj){
return (typeof Event!="undefined")&&(obj.eventPhase);
};
this.currentEvent=null;
this.callListener=function(_1a1,_1a2){
if(typeof _1a1!="function"){
dojo.raise("listener not a function: "+_1a1);
}
dojo.event.browser.currentEvent.currentTarget=_1a2;
return _1a1.call(_1a2,dojo.event.browser.currentEvent);
};
this.stopPropagation=function(){
dojo.event.browser.currentEvent.cancelBubble=true;
};
this.preventDefault=function(){
dojo.event.browser.currentEvent.returnValue=false;
};
this.keys={KEY_BACKSPACE:8,KEY_TAB:9,KEY_ENTER:13,KEY_SHIFT:16,KEY_CTRL:17,KEY_ALT:18,KEY_PAUSE:19,KEY_CAPS_LOCK:20,KEY_ESCAPE:27,KEY_SPACE:32,KEY_PAGE_UP:33,KEY_PAGE_DOWN:34,KEY_END:35,KEY_HOME:36,KEY_LEFT_ARROW:37,KEY_UP_ARROW:38,KEY_RIGHT_ARROW:39,KEY_DOWN_ARROW:40,KEY_INSERT:45,KEY_DELETE:46,KEY_LEFT_WINDOW:91,KEY_RIGHT_WINDOW:92,KEY_SELECT:93,KEY_F1:112,KEY_F2:113,KEY_F3:114,KEY_F4:115,KEY_F5:116,KEY_F6:117,KEY_F7:118,KEY_F8:119,KEY_F9:120,KEY_F10:121,KEY_F11:122,KEY_F12:123,KEY_NUM_LOCK:144,KEY_SCROLL_LOCK:145};
this.revKeys=[];
for(var key in this.keys){
this.revKeys[this.keys[key]]=key;
}
this.fixEvent=function(evt){
if((!evt)&&(window["event"])){
var evt=window.event;
}
if((evt["type"])&&(evt["type"].indexOf("key")==0)){
evt.keys=this.revKeys;
for(var key in this.keys){
evt[key]=this.keys[key];
}
if((dojo.render.html.ie)&&(evt["type"]=="keypress")){
evt.charCode=evt.keyCode;
}
}
if(dojo.render.html.ie){
if(!evt.target){
evt.target=evt.srcElement;
}
if(!evt.currentTarget){
evt.currentTarget=evt.srcElement;
}
if(!evt.layerX){
evt.layerX=evt.offsetX;
}
if(!evt.layerY){
evt.layerY=evt.offsetY;
}
if(evt.fromElement){
evt.relatedTarget=evt.fromElement;
}
if(evt.toElement){
evt.relatedTarget=evt.toElement;
}
this.currentEvent=evt;
evt.callListener=this.callListener;
evt.stopPropagation=this.stopPropagation;
evt.preventDefault=this.preventDefault;
}
return evt;
};
this.stopEvent=function(ev){
if(window.event){
ev.returnValue=false;
ev.cancelBubble=true;
}else{
ev.preventDefault();
ev.stopPropagation();
}
};
};
dojo.hostenv.conditionalLoadModule({common:["dojo.event","dojo.event.topic"],browser:["dojo.event.browser"]});
dojo.hostenv.moduleLoaded("dojo.event.*");
dojo.provide("dojo.string");
dojo.require("dojo.lang");
dojo.string.trim=function(str,wh){
if(!dojo.lang.isString(str)){
return str;
}
if(!str.length){
return str;
}
if(wh>0){
return str.replace(/^\s+/,"");
}else{
if(wh<0){
return str.replace(/\s+$/,"");
}else{
return str.replace(/^\s+|\s+$/g,"");
}
}
};
dojo.string.trimStart=function(str){
return dojo.string.trim(str,1);
};
dojo.string.trimEnd=function(str){
return dojo.string.trim(str,-1);
};
dojo.string.paramString=function(str,_1ac,_1ad){
for(var name in _1ac){
var re=new RegExp("\\%\\{"+name+"\\}","g");
str=str.replace(re,_1ac[name]);
}
if(_1ad){
str=str.replace(/%\{([^\}\s]+)\}/g,"");
}
return str;
};
dojo.string.capitalize=function(str){
if(!dojo.lang.isString(str)){
return "";
}
if(arguments.length==0){
str=this;
}
var _1b1=str.split(" ");
var _1b2="";
var len=_1b1.length;
for(var i=0;i<len;i++){
var word=_1b1[i];
word=word.charAt(0).toUpperCase()+word.substring(1,word.length);
_1b2+=word;
if(i<len-1){
_1b2+=" ";
}
}
return new String(_1b2);
};
dojo.string.isBlank=function(str){
if(!dojo.lang.isString(str)){
return true;
}
return (dojo.string.trim(str).length==0);
};
dojo.string.encodeAscii=function(str){
if(!dojo.lang.isString(str)){
return str;
}
var ret="";
var _1b9=escape(str);
var _1ba,re=/%u([0-9A-F]{4})/i;
while((_1ba=_1b9.match(re))){
var num=Number("0x"+_1ba[1]);
var _1bc=escape("&#"+num+";");
ret+=_1b9.substring(0,_1ba.index)+_1bc;
_1b9=_1b9.substring(_1ba.index+_1ba[0].length);
}
ret+=_1b9.replace(/\+/g,"%2B");
return ret;
};
dojo.string.summary=function(str,len){
if(!len||str.length<=len){
return str;
}else{
return str.substring(0,len).replace(/\.+$/,"")+"...";
}
};
dojo.string.escape=function(type,str){
switch(type.toLowerCase()){
case "xml":
case "html":
case "xhtml":
return dojo.string.escapeXml(str);
case "sql":
return dojo.string.escapeSql(str);
case "regexp":
case "regex":
return dojo.string.escapeRegExp(str);
case "javascript":
case "jscript":
case "js":
return dojo.string.escapeJavaScript(str);
case "ascii":
return dojo.string.encodeAscii(str);
default:
return str;
}
};
dojo.string.escapeXml=function(str){
return str.replace(/&/gm,"&amp;").replace(/</gm,"&lt;").replace(/>/gm,"&gt;").replace(/"/gm,"&quot;").replace(/'/gm,"&#39;");
};
dojo.string.escapeSql=function(str){
return str.replace(/'/gm,"''");
};
dojo.string.escapeRegExp=function(str){
return str.replace(/\\/gm,"\\\\").replace(/([\f\b\n\t\r])/gm,"\\$1");
};
dojo.string.escapeJavaScript=function(str){
return str.replace(/(["'\f\b\n\t\r])/gm,"\\$1");
};
dojo.string.repeat=function(str,_1c6,_1c7){
var out="";
for(var i=0;i<_1c6;i++){
out+=str;
if(_1c7&&i<_1c6-1){
out+=_1c7;
}
}
return out;
};
dojo.string.endsWith=function(str,end,_1cc){
if(_1cc){
str=str.toLowerCase();
end=end.toLowerCase();
}
return str.lastIndexOf(end)==str.length-end.length;
};
dojo.string.endsWithAny=function(str){
for(var i=1;i<arguments.length;i++){
if(dojo.string.endsWith(str,arguments[i])){
return true;
}
}
return false;
};
dojo.string.startsWith=function(str,_1d0,_1d1){
if(_1d1){
str=str.toLowerCase();
_1d0=_1d0.toLowerCase();
}
return str.indexOf(_1d0)==0;
};
dojo.string.startsWithAny=function(str){
for(var i=1;i<arguments.length;i++){
if(dojo.string.startsWith(str,arguments[i])){
return true;
}
}
return false;
};
dojo.string.has=function(str){
for(var i=1;i<arguments.length;i++){
if(str.indexOf(arguments[i]>-1)){
return true;
}
}
return false;
};
dojo.string.pad=function(str,len,c,dir){
var out=String(str);
if(!c){
c="0";
}
if(!dir){
dir=1;
}
while(out.length<len){
if(dir>0){
out=c+out;
}else{
out+=c;
}
}
return out;
};
dojo.string.padLeft=function(str,len,c){
return dojo.string.pad(str,len,c,1);
};
dojo.string.padRight=function(str,len,c){
return dojo.string.pad(str,len,c,-1);
};
dojo.string.addToPrototype=function(){
for(var _1e1 in dojo.string){
if(dojo.lang.isFunction(dojo.string[_1e1])){
var func=(function(){
var meth=_1e1;
switch(meth){
case "addToPrototype":
return null;
break;
case "escape":
return function(type){
return dojo.string.escape(type,this);
};
break;
default:
return function(){
var args=[this];
for(var i=0;i<arguments.length;i++){
args.push(arguments[i]);
}
dojo.debug(args);
return dojo.string[meth].apply(dojo.string,args);
};
}
})();
if(func){
String.prototype[_1e1]=func;
}
}
}
};
dojo.provide("dojo.io.IO");
dojo.require("dojo.string");
dojo.io.transports=[];
dojo.io.hdlrFuncNames=["load","error"];
dojo.io.Request=function(url,_1e8,_1e9,_1ea){
if((arguments.length==1)&&(arguments[0].constructor==Object)){
this.fromKwArgs(arguments[0]);
}else{
this.url=url;
if(_1e8){
this.mimetype=_1e8;
}
if(_1e9){
this.transport=_1e9;
}
if(arguments.length>=4){
this.changeUrl=_1ea;
}
}
};
dojo.lang.extend(dojo.io.Request,{url:"",mimetype:"text/plain",method:"GET",content:undefined,transport:undefined,changeUrl:undefined,formNode:undefined,sync:false,bindSuccess:false,useCache:false,preventCache:false,load:function(type,data,evt){
},error:function(type,_1ef){
},handle:function(){
},abort:function(){
},fromKwArgs:function(_1f0){
if(_1f0["url"]){
_1f0.url=_1f0.url.toString();
}
if(!_1f0["method"]&&_1f0["formNode"]&&_1f0["formNode"].method){
_1f0.method=_1f0["formNode"].method;
}
if(!_1f0["handle"]&&_1f0["handler"]){
_1f0.handle=_1f0.handler;
}
if(!_1f0["load"]&&_1f0["loaded"]){
_1f0.load=_1f0.loaded;
}
if(!_1f0["changeUrl"]&&_1f0["changeURL"]){
_1f0.changeUrl=_1f0.changeURL;
}
if(!_1f0["encoding"]){
if(!dojo.lang.isUndefined(djConfig["bindEncoding"])){
_1f0.encoding=djConfig.bindEncoding;
}else{
_1f0.encoding="";
}
}
var _1f1=dojo.lang.isFunction;
for(var x=0;x<dojo.io.hdlrFuncNames.length;x++){
var fn=dojo.io.hdlrFuncNames[x];
if(_1f1(_1f0[fn])){
continue;
}
if(_1f1(_1f0["handle"])){
_1f0[fn]=_1f0.handle;
}
}
dojo.lang.mixin(this,_1f0);
}});
dojo.io.Error=function(msg,type,num){
this.message=msg;
this.type=type||"unknown";
this.number=num||0;
};
dojo.io.transports.addTransport=function(name){
this.push(name);
this[name]=dojo.io[name];
};
dojo.io.bind=function(_1f8){
if(!(_1f8 instanceof dojo.io.Request)){
try{
_1f8=new dojo.io.Request(_1f8);
}
catch(e){
dojo.debug(e);
}
}
var _1f9="";
if(_1f8["transport"]){
_1f9=_1f8["transport"];
if(!this[_1f9]){
return _1f8;
}
}else{
for(var x=0;x<dojo.io.transports.length;x++){
var tmp=dojo.io.transports[x];
if((this[tmp])&&(this[tmp].canHandle(_1f8))){
_1f9=tmp;
}
}
if(_1f9==""){
return _1f8;
}
}
this[_1f9].bind(_1f8);
_1f8.bindSuccess=true;
return _1f8;
};
dojo.io.queueBind=function(_1fc){
if(!(_1fc instanceof dojo.io.Request)){
try{
_1fc=new dojo.io.Request(_1fc);
}
catch(e){
dojo.debug(e);
}
}
var _1fd=_1fc.load;
_1fc.load=function(){
dojo.io._queueBindInFlight=false;
var ret=_1fd.apply(this,arguments);
dojo.io._dispatchNextQueueBind();
return ret;
};
var _1ff=_1fc.error;
_1fc.error=function(){
dojo.io._queueBindInFlight=false;
var ret=_1ff.apply(this,arguments);
dojo.io._dispatchNextQueueBind();
return ret;
};
dojo.io._bindQueue.push(_1fc);
dojo.io._dispatchNextQueueBind();
return _1fc;
};
dojo.io._dispatchNextQueueBind=function(){
if(!dojo.io._queueBindInFlight){
dojo.io._queueBindInFlight=true;
dojo.io.bind(dojo.io._bindQueue.shift());
}
};
dojo.io._bindQueue=[];
dojo.io._queueBindInFlight=false;
dojo.io.argsFromMap=function(map,_202){
var _203=new Object();
var _204="";
var enc=/utf/i.test(_202||"")?encodeURIComponent:dojo.string.encodeAscii;
for(var x in map){
if(!_203[x]){
_204+=enc(x)+"="+enc(map[x])+"&";
}
}
return _204;
};
dojo.provide("dojo.dom");
dojo.require("dojo.lang");
dojo.dom.ELEMENT_NODE=1;
dojo.dom.ATTRIBUTE_NODE=2;
dojo.dom.TEXT_NODE=3;
dojo.dom.CDATA_SECTION_NODE=4;
dojo.dom.ENTITY_REFERENCE_NODE=5;
dojo.dom.ENTITY_NODE=6;
dojo.dom.PROCESSING_INSTRUCTION_NODE=7;
dojo.dom.COMMENT_NODE=8;
dojo.dom.DOCUMENT_NODE=9;
dojo.dom.DOCUMENT_TYPE_NODE=10;
dojo.dom.DOCUMENT_FRAGMENT_NODE=11;
dojo.dom.NOTATION_NODE=12;
dojo.dom.dojoml="http://www.dojotoolkit.org/2004/dojoml";
dojo.dom.xmlns={svg:"http://www.w3.org/2000/svg",smil:"http://www.w3.org/2001/SMIL20/",mml:"http://www.w3.org/1998/Math/MathML",cml:"http://www.xml-cml.org",xlink:"http://www.w3.org/1999/xlink",xhtml:"http://www.w3.org/1999/xhtml",xul:"http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul",xbl:"http://www.mozilla.org/xbl",fo:"http://www.w3.org/1999/XSL/Format",xsl:"http://www.w3.org/1999/XSL/Transform",xslt:"http://www.w3.org/1999/XSL/Transform",xi:"http://www.w3.org/2001/XInclude",xforms:"http://www.w3.org/2002/01/xforms",saxon:"http://icl.com/saxon",xalan:"http://xml.apache.org/xslt",xsd:"http://www.w3.org/2001/XMLSchema",dt:"http://www.w3.org/2001/XMLSchema-datatypes",xsi:"http://www.w3.org/2001/XMLSchema-instance",rdf:"http://www.w3.org/1999/02/22-rdf-syntax-ns#",rdfs:"http://www.w3.org/2000/01/rdf-schema#",dc:"http://purl.org/dc/elements/1.1/",dcq:"http://purl.org/dc/qualifiers/1.0","soap-env":"http://schemas.xmlsoap.org/soap/envelope/",wsdl:"http://schemas.xmlsoap.org/wsdl/",AdobeExtensions:"http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/"};
dojo.dom.isNode=dojo.lang.isDomNode=function(wh){
if(typeof Element!="undefined"){
return wh instanceof Element;
}else{
return wh&&!isNaN(wh.nodeType);
}
};
dojo.lang.whatAmI.custom["node"]=dojo.dom.isNode;
dojo.dom.getTagName=function(node){
var _209=node.tagName;
if(_209.substr(0,5).toLowerCase()!="dojo:"){
if(_209.substr(0,4).toLowerCase()=="dojo"){
return "dojo:"+_209.substring(4).toLowerCase();
}
var djt=node.getAttribute("dojoType")||node.getAttribute("dojotype");
if(djt){
return "dojo:"+djt.toLowerCase();
}
if((node.getAttributeNS)&&(node.getAttributeNS(this.dojoml,"type"))){
return "dojo:"+node.getAttributeNS(this.dojoml,"type").toLowerCase();
}
try{
djt=node.getAttribute("dojo:type");
}
catch(e){
}
if(djt){
return "dojo:"+djt.toLowerCase();
}
if((!dj_global["djConfig"])||(!djConfig["ignoreClassNames"])){
var _20b=node.className||node.getAttribute("class");
if((_20b)&&(_20b.indexOf("dojo-")!=-1)){
var _20c=_20b.split(" ");
for(var x=0;x<_20c.length;x++){
if((_20c[x].length>5)&&(_20c[x].indexOf("dojo-")>=0)){
return "dojo:"+_20c[x].substr(5).toLowerCase();
}
}
}
}
}
return _209.toLowerCase();
};
dojo.dom.getUniqueId=function(){
do{
var id="dj_unique_"+(++arguments.callee._idIncrement);
}while(document.getElementById(id));
return id;
};
dojo.dom.getUniqueId._idIncrement=0;
dojo.dom.firstElement=dojo.dom.getFirstChildElement=function(_20f,_210){
var node=_20f.firstChild;
while(node&&node.nodeType!=dojo.dom.ELEMENT_NODE){
node=node.nextSibling;
}
if(_210&&node&&node.tagName&&node.tagName.toLowerCase()!=_210.toLowerCase()){
node=dojo.dom.nextElement(node,_210);
}
return node;
};
dojo.dom.lastElement=dojo.dom.getLastChildElement=function(_212,_213){
var node=_212.lastChild;
while(node&&node.nodeType!=dojo.dom.ELEMENT_NODE){
node=node.previousSibling;
}
if(_213&&node&&node.tagName&&node.tagName.toLowerCase()!=_213.toLowerCase()){
node=dojo.dom.prevElement(node,_213);
}
return node;
};
dojo.dom.nextElement=dojo.dom.getNextSiblingElement=function(node,_216){
if(!node){
return null;
}
if(_216){
_216=_216.toLowerCase();
}
do{
node=node.nextSibling;
}while(node&&node.nodeType!=dojo.dom.ELEMENT_NODE&&(!_216||_216!=node.tagName));
return node;
};
dojo.dom.prevElement=dojo.dom.getPreviousSiblingElement=function(node,_218){
if(!node){
return null;
}
if(_218){
_218=_218.toLowerCase();
}
do{
node=node.previousSibling;
}while(node&&node.nodeType!=dojo.dom.ELEMENT_NODE&&(!_218||_218!=node.tagName));
return node;
};
dojo.dom.moveChildren=function(_219,_21a,trim){
var _21c=0;
if(trim){
while(_219.hasChildNodes()&&_219.firstChild.nodeType==dojo.dom.TEXT_NODE){
_219.removeChild(_219.firstChild);
}
while(_219.hasChildNodes()&&_219.lastChild.nodeType==dojo.dom.TEXT_NODE){
_219.removeChild(_219.lastChild);
}
}
while(_219.hasChildNodes()){
_21a.appendChild(_219.firstChild);
_21c++;
}
return _21c;
};
dojo.dom.copyChildren=function(_21d,_21e,trim){
var _220=_21d.cloneNode(true);
return this.moveChildren(_220,_21e,trim);
};
dojo.dom.removeChildren=function(node){
var _222=node.childNodes.length;
while(node.hasChildNodes()){
node.removeChild(node.firstChild);
}
return _222;
};
dojo.dom.replaceChildren=function(node,_224){
dojo.dom.removeChildren(node);
node.appendChild(_224);
};
dojo.dom.removeNode=function(node){
if(node&&node.parentNode){
return node.parentNode.removeChild(node);
}
};
dojo.dom.getAncestors=function(node,_227,_228){
var _229=[];
var _22a=dojo.lang.isFunction(_227);
while(node){
if(!_22a||_227(node)){
_229.push(node);
}
if(_228&&_229.length>0){
return _229[0];
}
node=node.parentNode;
}
if(_228){
return null;
}
return _229;
};
dojo.dom.getAncestorsByTag=function(node,tag,_22d){
tag=tag.toLowerCase();
return dojo.dom.getAncestors(node,function(el){
return ((el.tagName)&&(el.tagName.toLowerCase()==tag));
},_22d);
};
dojo.dom.getFirstAncestorByTag=function(node,tag){
return dojo.dom.getAncestorsByTag(node,tag,true);
};
dojo.dom.isDescendantOf=function(node,_232,_233){
if(_233&&node){
node=node.parentNode;
}
while(node){
if(node==_232){
return true;
}
node=node.parentNode;
}
return false;
};
dojo.dom.innerXML=function(node){
if(node.innerXML){
return node.innerXML;
}else{
if(typeof XMLSerializer!="undefined"){
return (new XMLSerializer()).serializeToString(node);
}
}
};
dojo.dom.createDocumentFromText=function(str,_236){
if(!_236){
_236="text/xml";
}
if(typeof DOMParser!="undefined"){
var _237=new DOMParser();
return _237.parseFromString(str,_236);
}else{
if(typeof ActiveXObject!="undefined"){
var _238=new ActiveXObject("Microsoft.XMLDOM");
if(_238){
_238.async=false;
_238.loadXML(str);
return _238;
}else{
dojo.debug("toXml didn't work?");
}
}else{
if(document.createElement){
var tmp=document.createElement("xml");
tmp.innerHTML=str;
if(document.implementation&&document.implementation.createDocument){
var _23a=document.implementation.createDocument("foo","",null);
for(var i=0;i<tmp.childNodes.length;i++){
_23a.importNode(tmp.childNodes.item(i),true);
}
return _23a;
}
return tmp.document&&tmp.document.firstChild?tmp.document.firstChild:tmp;
}
}
}
return null;
};
dojo.dom.insertBefore=function(node,ref,_23e){
if(_23e!=true&&(node===ref||node.nextSibling===ref)){
return false;
}
var _23f=ref.parentNode;
_23f.insertBefore(node,ref);
return true;
};
dojo.dom.insertAfter=function(node,ref,_242){
var pn=ref.parentNode;
if(ref==pn.lastChild){
if((_242!=true)&&(node===ref)){
return false;
}
pn.appendChild(node);
}else{
return this.insertBefore(node,ref.nextSibling,_242);
}
return true;
};
dojo.dom.insertAtPosition=function(node,ref,_246){
if((!node)||(!ref)||(!_246)){
return false;
}
switch(_246.toLowerCase()){
case "before":
return dojo.dom.insertBefore(node,ref);
case "after":
return dojo.dom.insertAfter(node,ref);
case "first":
if(ref.firstChild){
return dojo.dom.insertBefore(node,ref.firstChild);
}else{
ref.appendChild(node);
return true;
}
break;
default:
ref.appendChild(node);
return true;
}
};
dojo.dom.insertAtIndex=function(node,_248,_249){
var _24a=_248.childNodes;
if(!_24a.length){
_248.appendChild(node);
return true;
}
var _24b=null;
for(var i=0;i<_24a.length;i++){
var _24d=_24a.item(i)["getAttribute"]?parseInt(_24a.item(i).getAttribute("dojoinsertionindex")):-1;
if(_24d<_249){
_24b=_24a.item(i);
}
}
if(_24b){
return dojo.dom.insertAfter(node,_24b);
}else{
return dojo.dom.insertBefore(node,_24a.item(0));
}
};
dojo.dom.textContent=function(node,text){
if(text){
dojo.dom.replaceChildren(node,document.createTextNode(text));
return text;
}else{
var _250="";
if(node==null){
return _250;
}
for(var i=0;i<node.childNodes.length;i++){
switch(node.childNodes[i].nodeType){
case 1:
case 5:
_250+=dojo.dom.textContent(node.childNodes[i]);
break;
case 3:
case 2:
case 4:
_250+=node.childNodes[i].nodeValue;
break;
default:
break;
}
}
return _250;
}
};
dojo.dom.collectionToArray=function(_252){
var _253=new Array(_252.length);
for(var i=0;i<_252.length;i++){
_253[i]=_252[i];
}
return _253;
};
dojo.provide("dojo.io.BrowserIO");
dojo.require("dojo.io");
dojo.require("dojo.lang");
dojo.require("dojo.dom");
try{
if((!djConfig.preventBackButtonFix)&&(!dojo.hostenv.post_load_)){
document.write("<iframe style='border: 0px; width: 1px; height: 1px; position: absolute; bottom: 0px; right: 0px; visibility: visible;' name='djhistory' id='djhistory' src='"+(dojo.hostenv.getBaseScriptUri()+"iframe_history.html")+"'></iframe>");
}
}
catch(e){
}
dojo.io.checkChildrenForFile=function(node){
var _256=false;
var _257=node.getElementsByTagName("input");
dojo.lang.forEach(_257,function(_258){
if(_256){
return;
}
if(_258.getAttribute("type")=="file"){
_256=true;
}
});
return _256;
};
dojo.io.formHasFile=function(_259){
return dojo.io.checkChildrenForFile(_259);
};
dojo.io.encodeForm=function(_25a,_25b){
if((!_25a)||(!_25a.tagName)||(!_25a.tagName.toLowerCase()=="form")){
dojo.raise("Attempted to encode a non-form element.");
}
var enc=/utf/i.test(_25b||"")?encodeURIComponent:dojo.string.encodeAscii;
var _25d=[];
for(var i=0;i<_25a.elements.length;i++){
var elm=_25a.elements[i];
if(elm.disabled||elm.tagName.toLowerCase()=="fieldset"||!elm.name){
continue;
}
var name=enc(elm.name);
var type=elm.type.toLowerCase();
if(type=="select-multiple"){
for(var j=0;j<elm.options.length;j++){
if(elm.options[j].selected){
_25d.push(name+"="+enc(elm.options[j].value));
}
}
}else{
if(dojo.lang.inArray(type,["radio","checkbox"])){
if(elm.checked){
_25d.push(name+"="+enc(elm.value));
}
}else{
if(!dojo.lang.inArray(type,["file","submit","reset","button"])){
_25d.push(name+"="+enc(elm.value));
}
}
}
}
var _263=_25a.getElementsByTagName("input");
for(var i=0;i<_263.length;i++){
var _264=_263[i];
if(_264.type.toLowerCase()=="image"&&_264.form==_25a){
var name=enc(_264.name);
_25d.push(name+"="+enc(_264.value));
_25d.push(name+".x=0");
_25d.push(name+".y=0");
}
}
return _25d.join("&")+"&";
};
dojo.io.setIFrameSrc=function(_265,src,_267){
try{
var r=dojo.render.html;
if(!_267){
if(r.safari){
_265.location=src;
}else{
frames[_265.name].location=src;
}
}else{
var idoc;
if(r.ie){
idoc=_265.contentWindow.document;
}else{
if(r.moz){
idoc=_265.contentWindow;
}
}
idoc.location.replace(src);
}
}
catch(e){
dojo.debug(e);
dojo.debug("setIFrameSrc: "+e);
}
};
dojo.io.XMLHTTPTransport=new function(){
var _26a=this;
this.initialHref=window.location.href;
this.initialHash=window.location.hash;
this.moveForward=false;
var _26b={};
this.useCache=false;
this.preventCache=false;
this.historyStack=[];
this.forwardStack=[];
this.historyIframe=null;
this.bookmarkAnchor=null;
this.locationTimer=null;
function getCacheKey(url,_26d,_26e){
return url+"|"+_26d+"|"+_26e.toLowerCase();
}
function addToCache(url,_270,_271,http){
_26b[getCacheKey(url,_270,_271)]=http;
}
function getFromCache(url,_274,_275){
return _26b[getCacheKey(url,_274,_275)];
}
this.clearCache=function(){
_26b={};
};
function doLoad(_276,http,url,_279,_27a){
if((http.status==200)||(location.protocol=="file:"&&http.status==0)){
var ret;
if(_276.method.toLowerCase()=="head"){
var _27c=http.getAllResponseHeaders();
ret={};
ret.toString=function(){
return _27c;
};
var _27d=_27c.split(/[\r\n]+/g);
for(var i=0;i<_27d.length;i++){
var pair=_27d[i].match(/^([^:]+)\s*:\s*(.+)$/i);
if(pair){
ret[pair[1]]=pair[2];
}
}
}else{
if(_276.mimetype=="text/javascript"){
try{
ret=dj_eval(http.responseText);
}
catch(e){
dojo.debug(e);
ret=null;
}
}else{
if(_276.mimetype=="text/json"){
try{
ret=dj_eval("("+http.responseText+")");
}
catch(e){
dojo.debug(e);
ret=false;
}
}else{
if((_276.mimetype=="application/xml")||(_276.mimetype=="text/xml")){
ret=http.responseXML;
if(!ret||typeof ret=="string"){
ret=dojo.dom.createDocumentFromText(http.responseText);
}
}else{
ret=http.responseText;
}
}
}
}
if(_27a){
addToCache(url,_279,_276.method,http);
}
_276[(typeof _276.load=="function")?"load":"handle"]("load",ret,http);
}else{
var _280=new dojo.io.Error("XMLHttpTransport Error: "+http.status+" "+http.statusText);
_276[(typeof _276.error=="function")?"error":"handle"]("error",_280,http);
}
}
function setHeaders(http,_282){
if(_282["headers"]){
for(var _283 in _282["headers"]){
if(_283.toLowerCase()=="content-type"&&!_282["contentType"]){
_282["contentType"]=_282["headers"][_283];
}else{
http.setRequestHeader(_283,_282["headers"][_283]);
}
}
}
}
this.addToHistory=function(args){
var _285=args["back"]||args["backButton"]||args["handle"];
var hash=null;
if(!this.historyIframe){
this.historyIframe=window.frames["djhistory"];
}
if(!this.bookmarkAnchor){
this.bookmarkAnchor=document.createElement("a");
(document.body||document.getElementsByTagName("body")[0]).appendChild(this.bookmarkAnchor);
this.bookmarkAnchor.style.display="none";
}
if((!args["changeUrl"])||(dojo.render.html.ie)){
var url=dojo.hostenv.getBaseScriptUri()+"iframe_history.html?"+(new Date()).getTime();
this.moveForward=true;
dojo.io.setIFrameSrc(this.historyIframe,url,false);
}
if(args["changeUrl"]){
hash="#"+((args["changeUrl"]!==true)?args["changeUrl"]:(new Date()).getTime());
setTimeout("window.location.href = '"+hash+"';",1);
this.bookmarkAnchor.href=hash;
if(dojo.render.html.ie){
var _288=_285;
var lh=null;
var hsl=this.historyStack.length-1;
if(hsl>=0){
while(!this.historyStack[hsl]["urlHash"]){
hsl--;
}
lh=this.historyStack[hsl]["urlHash"];
}
if(lh){
_285=function(){
if(window.location.hash!=""){
setTimeout("window.location.href = '"+lh+"';",1);
}
_288();
};
}
this.forwardStack=[];
var _28b=args["forward"]||args["forwardButton"];
var tfw=function(){
if(window.location.hash!=""){
window.location.href=hash;
}
if(_28b){
_28b();
}
};
if(args["forward"]){
args.forward=tfw;
}else{
if(args["forwardButton"]){
args.forwardButton=tfw;
}
}
}else{
if(dojo.render.html.moz){
if(!this.locationTimer){
this.locationTimer=setInterval("dojo.io.XMLHTTPTransport.checkLocation();",200);
}
}
}
}
this.historyStack.push({"url":url,"callback":_285,"kwArgs":args,"urlHash":hash});
};
this.checkLocation=function(){
var hsl=this.historyStack.length;
if((window.location.hash==this.initialHash)||(window.location.href==this.initialHref)&&(hsl==1)){
this.handleBackButton();
return;
}
if(this.forwardStack.length>0){
if(this.forwardStack[this.forwardStack.length-1].urlHash==window.location.hash){
this.handleForwardButton();
return;
}
}
if((hsl>=2)&&(this.historyStack[hsl-2])){
if(this.historyStack[hsl-2].urlHash==window.location.hash){
this.handleBackButton();
return;
}
}
};
this.iframeLoaded=function(evt,_28f){
var isp=_28f.href.split("?");
if(isp.length<2){
if(this.historyStack.length==1){
this.handleBackButton();
}
return;
}
var _291=isp[1];
if(this.moveForward){
this.moveForward=false;
return;
}
var last=this.historyStack.pop();
if(!last){
if(this.forwardStack.length>0){
var next=this.forwardStack[this.forwardStack.length-1];
if(_291==next.url.split("?")[1]){
this.handleForwardButton();
}
}
return;
}
this.historyStack.push(last);
if(this.historyStack.length>=2){
if(isp[1]==this.historyStack[this.historyStack.length-2].url.split("?")[1]){
this.handleBackButton();
}
}else{
this.handleBackButton();
}
};
this.handleBackButton=function(){
var last=this.historyStack.pop();
if(!last){
return;
}
if(last["callback"]){
last.callback();
}else{
if(last.kwArgs["backButton"]){
last.kwArgs["backButton"]();
}else{
if(last.kwArgs["back"]){
last.kwArgs["back"]();
}else{
if(last.kwArgs["handle"]){
last.kwArgs.handle("back");
}
}
}
}
this.forwardStack.push(last);
};
this.handleForwardButton=function(){
var last=this.forwardStack.pop();
if(!last){
return;
}
if(last.kwArgs["forward"]){
last.kwArgs.forward();
}else{
if(last.kwArgs["forwardButton"]){
last.kwArgs.forwardButton();
}else{
if(last.kwArgs["handle"]){
last.kwArgs.handle("forward");
}
}
}
this.historyStack.push(last);
};
this.inFlight=[];
this.inFlightTimer=null;
this.startWatchingInFlight=function(){
if(!this.inFlightTimer){
this.inFlightTimer=setInterval("dojo.io.XMLHTTPTransport.watchInFlight();",10);
}
};
this.watchInFlight=function(){
for(var x=this.inFlight.length-1;x>=0;x--){
var tif=this.inFlight[x];
if(!tif){
this.inFlight.splice(x,1);
continue;
}
if(4==tif.http.readyState){
this.inFlight.splice(x,1);
doLoad(tif.req,tif.http,tif.url,tif.query,tif.useCache);
if(this.inFlight.length==0){
clearInterval(this.inFlightTimer);
this.inFlightTimer=null;
}
}
}
};
var _298=dojo.hostenv.getXmlhttpObject()?true:false;
this.canHandle=function(_299){
return _298&&dojo.lang.inArray((_299["mimetype"]||"".toLowerCase()),["text/plain","text/html","application/xml","text/xml","text/javascript","text/json"])&&dojo.lang.inArray(_299["method"].toLowerCase(),["post","get","head"])&&!(_299["formNode"]&&dojo.io.formHasFile(_299["formNode"]));
};
this.multipartBoundary="45309FFF-BD65-4d50-99C9-36986896A96F";
this.bind=function(_29a){
if(!_29a["url"]){
if(!_29a["formNode"]&&(_29a["backButton"]||_29a["back"]||_29a["changeUrl"]||_29a["watchForURL"])&&(!djConfig.preventBackButtonFix)){
this.addToHistory(_29a);
return true;
}
}
var url=_29a.url;
var _29c="";
if(_29a["formNode"]){
var ta=_29a.formNode.getAttribute("action");
if((ta)&&(!_29a["url"])){
url=ta;
}
var tp=_29a.formNode.getAttribute("method");
if((tp)&&(!_29a["method"])){
_29a.method=tp;
}
_29c+=dojo.io.encodeForm(_29a.formNode,_29a.encoding);
}
if(url.indexOf("#")>-1){
dojo.debug("Warning: dojo.io.bind: stripping hash values from url:",url);
url=url.split("#")[0];
}
if(_29a["file"]){
_29a.method="post";
}
if(!_29a["method"]){
_29a.method="get";
}
if(_29a.method.toLowerCase()=="get"){
_29a.multipart=false;
}else{
if(_29a["file"]){
_29a.multipart=true;
}else{
if(!_29a["multipart"]){
_29a.multipart=false;
}
}
}
if(_29a["backButton"]||_29a["back"]||_29a["changeUrl"]){
this.addToHistory(_29a);
}
do{
if(_29a.postContent){
_29c=_29a.postContent;
break;
}
if(_29a["content"]){
_29c+=dojo.io.argsFromMap(_29a.content,_29a.encoding);
}
if(_29a.method.toLowerCase()=="get"||!_29a.multipart){
break;
}
var t=[];
if(_29c.length){
var q=_29c.split("&");
for(var i=0;i<q.length;++i){
if(q[i].length){
var p=q[i].split("=");
t.push("--"+this.multipartBoundary,"Content-Disposition: form-data; name=\""+p[0]+"\"","",p[1]);
}
}
}
if(_29a.file){
if(dojo.lang.isArray(_29a.file)){
for(var i=0;i<_29a.file.length;++i){
var o=_29a.file[i];
t.push("--"+this.multipartBoundary,"Content-Disposition: form-data; name=\""+o.name+"\"; filename=\""+("fileName" in o?o.fileName:o.name)+"\"","Content-Type: "+("contentType" in o?o.contentType:"application/octet-stream"),"",o.content);
}
}else{
var o=_29a.file;
t.push("--"+this.multipartBoundary,"Content-Disposition: form-data; name=\""+o.name+"\"; filename=\""+("fileName" in o?o.fileName:o.name)+"\"","Content-Type: "+("contentType" in o?o.contentType:"application/octet-stream"),"",o.content);
}
}
if(t.length){
t.push("--"+this.multipartBoundary+"--","");
_29c=t.join("\r\n");
}
}while(false);
var _2a4=_29a["sync"]?false:true;
var _2a5=_29a["preventCache"]||(this.preventCache==true&&_29a["preventCache"]!=false);
var _2a6=_29a["useCache"]==true||(this.useCache==true&&_29a["useCache"]!=false);
if(!_2a5&&_2a6){
var _2a7=getFromCache(url,_29c,_29a.method);
if(_2a7){
doLoad(_29a,_2a7,url,_29c,false);
return;
}
}
var http=dojo.hostenv.getXmlhttpObject();
var _2a9=false;
if(_2a4){
this.inFlight.push({"req":_29a,"http":http,"url":url,"query":_29c,"useCache":_2a6});
this.startWatchingInFlight();
}
if(_29a.method.toLowerCase()=="post"){
http.open("POST",url,_2a4);
setHeaders(http,_29a);
http.setRequestHeader("Content-Type",_29a.multipart?("multipart/form-data; boundary="+this.multipartBoundary):(_29a.contentType||"application/x-www-form-urlencoded"));
http.send(_29c);
}else{
var _2aa=url;
if(_29c!=""){
_2aa+=(_2aa.indexOf("?")>-1?"&":"?")+_29c;
}
if(_2a5){
_2aa+=(dojo.string.endsWithAny(_2aa,"?","&")?"":(_2aa.indexOf("?")>-1?"&":"?"))+"dojo.preventCache="+new Date().valueOf();
}
http.open(_29a.method.toUpperCase(),_2aa,_2a4);
setHeaders(http,_29a);
http.send(null);
}
if(!_2a4){
doLoad(_29a,http,url,_29c,_2a6);
}
_29a.abort=function(){
return http.abort();
};
return;
};
dojo.io.transports.addTransport("XMLHTTPTransport");
};
dojo.provide("dojo.io.cookie");
dojo.io.cookie.setCookie=function(name,_2ac,days,path,_2af,_2b0){
var _2b1=-1;
if(typeof days=="number"&&days>=0){
var d=new Date();
d.setTime(d.getTime()+(days*24*60*60*1000));
_2b1=d.toGMTString();
}
_2ac=escape(_2ac);
document.cookie=name+"="+_2ac+";"+(_2b1!=-1?" expires="+_2b1+";":"")+(path?"path="+path:"")+(_2af?"; domain="+_2af:"")+(_2b0?"; secure":"");
};
dojo.io.cookie.set=dojo.io.cookie.setCookie;
dojo.io.cookie.getCookie=function(name){
var idx=document.cookie.indexOf(name+"=");
if(idx==-1){
return null;
}
value=document.cookie.substring(idx+name.length+1);
var end=value.indexOf(";");
if(end==-1){
end=value.length;
}
value=value.substring(0,end);
value=unescape(value);
return value;
};
dojo.io.cookie.get=dojo.io.cookie.getCookie;
dojo.io.cookie.deleteCookie=function(name){
dojo.io.cookie.setCookie(name,"-",0);
};
dojo.io.cookie.setObjectCookie=function(name,obj,days,path,_2bb,_2bc,_2bd){
if(arguments.length==5){
_2bd=_2bb;
}
var _2be=[],cookie,value="";
if(!_2bd){
cookie=dojo.io.cookie.getObjectCookie(name);
}
if(days>=0){
if(!cookie){
cookie={};
}
for(var prop in obj){
if(prop==null){
delete cookie[prop];
}else{
if(typeof obj[prop]=="string"||typeof obj[prop]=="number"){
cookie[prop]=obj[prop];
}
}
}
prop=null;
for(var prop in cookie){
_2be.push(escape(prop)+"="+escape(cookie[prop]));
}
value=_2be.join("&");
}
dojo.io.cookie.setCookie(name,value,days,path);
};
dojo.io.cookie.getObjectCookie=function(name){
var _2c1=null,cookie=dojo.io.cookie.getCookie(name);
if(cookie){
_2c1={};
var _2c2=cookie.split("&");
for(var i=0;i<_2c2.length;i++){
var pair=_2c2[i].split("=");
var _2c5=pair[1];
if(isNaN(_2c5)){
_2c5=unescape(pair[1]);
}
_2c1[unescape(pair[0])]=_2c5;
}
}
return _2c1;
};
dojo.io.cookie.isSupported=function(){
if(typeof navigator.cookieEnabled!="boolean"){
dojo.io.cookie.setCookie("__TestingYourBrowserForCookieSupport__","CookiesAllowed",90,null);
var _2c6=dojo.io.cookie.getCookie("__TestingYourBrowserForCookieSupport__");
navigator.cookieEnabled=(_2c6=="CookiesAllowed");
if(navigator.cookieEnabled){
this.deleteCookie("__TestingYourBrowserForCookieSupport__");
}
}
return navigator.cookieEnabled;
};
if(!dojo.io.cookies){
dojo.io.cookies=dojo.io.cookie;
}
dojo.hostenv.conditionalLoadModule({common:["dojo.io",false,false],rhino:["dojo.io.RhinoIO",false,false],browser:[["dojo.io.BrowserIO",false,false],["dojo.io.cookie",false,false]]});
dojo.hostenv.moduleLoaded("dojo.io.*");
dojo.provide("dojo.xml.Parse");
dojo.require("dojo.dom");
dojo.xml.Parse=function(){
this.parseFragment=function(_2c7){
var _2c8={};
var _2c9=dojo.dom.getTagName(_2c7);
_2c8[_2c9]=new Array(_2c7.tagName);
var _2ca=this.parseAttributes(_2c7);
for(var attr in _2ca){
if(!_2c8[attr]){
_2c8[attr]=[];
}
_2c8[attr][_2c8[attr].length]=_2ca[attr];
}
var _2cc=_2c7.childNodes;
for(var _2cd in _2cc){
switch(_2cc[_2cd].nodeType){
case dojo.dom.ELEMENT_NODE:
_2c8[_2c9].push(this.parseElement(_2cc[_2cd]));
break;
case dojo.dom.TEXT_NODE:
if(_2cc.length==1){
if(!_2c8[_2c7.tagName]){
_2c8[_2c9]=[];
}
_2c8[_2c9].push({value:_2cc[0].nodeValue});
}
break;
}
}
return _2c8;
};
this.parseElement=function(node,_2cf,_2d0,_2d1){
var _2d2={};
var _2d3=dojo.dom.getTagName(node);
_2d2[_2d3]=[];
if((!_2d0)||(_2d3.substr(0,4).toLowerCase()=="dojo")){
var _2d4=this.parseAttributes(node);
for(var attr in _2d4){
if((!_2d2[_2d3][attr])||(typeof _2d2[_2d3][attr]!="array")){
_2d2[_2d3][attr]=[];
}
_2d2[_2d3][attr].push(_2d4[attr]);
}
_2d2[_2d3].nodeRef=node;
_2d2.tagName=_2d3;
_2d2.index=_2d1||0;
}
var _2d6=0;
for(var i=0;i<node.childNodes.length;i++){
var tcn=node.childNodes.item(i);
switch(tcn.nodeType){
case dojo.dom.ELEMENT_NODE:
_2d6++;
var ctn=dojo.dom.getTagName(tcn);
if(!_2d2[ctn]){
_2d2[ctn]=[];
}
_2d2[ctn].push(this.parseElement(tcn,true,_2d0,_2d6));
if((tcn.childNodes.length==1)&&(tcn.childNodes.item(0).nodeType==dojo.dom.TEXT_NODE)){
_2d2[ctn][_2d2[ctn].length-1].value=tcn.childNodes.item(0).nodeValue;
}
break;
case dojo.dom.TEXT_NODE:
if(node.childNodes.length==1){
_2d2[_2d3].push({value:node.childNodes.item(0).nodeValue});
}
break;
default:
break;
}
}
return _2d2;
};
this.parseAttributes=function(node){
var _2db={};
var atts=node.attributes;
for(var i=0;i<atts.length;i++){
var _2de=atts.item(i);
if((dojo.render.html.capable)&&(dojo.render.html.ie)){
if(!_2de){
continue;
}
if((typeof _2de=="object")&&(typeof _2de.nodeValue=="undefined")||(_2de.nodeValue==null)||(_2de.nodeValue=="")){
continue;
}
}
_2db[_2de.nodeName]={value:_2de.nodeValue};
}
return _2db;
};
};
dojo.provide("dojo.math");
dojo.math.degToRad=function(x){
return (x*Math.PI)/180;
};
dojo.math.radToDeg=function(x){
return (x*180)/Math.PI;
};
dojo.math.factorial=function(n){
if(n<1){
return 0;
}
var _2e2=1;
for(var i=1;i<=n;i++){
_2e2*=i;
}
return _2e2;
};
dojo.math.permutations=function(n,k){
if(n==0||k==0){
return 1;
}
return (dojo.math.factorial(n)/dojo.math.factorial(n-k));
};
dojo.math.combinations=function(n,r){
if(n==0||r==0){
return 1;
}
return (dojo.math.factorial(n)/(dojo.math.factorial(n-r)*dojo.math.factorial(r)));
};
dojo.math.bernstein=function(t,n,i){
return (dojo.math.combinations(n,i)*Math.pow(t,i)*Math.pow(1-t,n-i));
};
dojo.math.gaussianRandom=function(){
var k=2;
do{
var i=2*Math.random()-1;
var j=2*Math.random()-1;
k=i*i+j*j;
}while(k>=1);
k=Math.sqrt((-2*Math.log(k))/k);
return i*k;
};
dojo.math.mean=function(){
var _2ee=dojo.lang.isArray(arguments[0])?arguments[0]:arguments;
var mean=0;
for(var i=0;i<_2ee.length;i++){
mean+=_2ee[i];
}
return mean/_2ee.length;
};
dojo.math.round=function(_2f1,_2f2){
if(!_2f2){
var _2f3=1;
}else{
var _2f3=Math.pow(10,_2f2);
}
return Math.round(_2f1*_2f3)/_2f3;
};
dojo.math.sd=function(){
var _2f4=dojo.lang.isArray(arguments[0])?arguments[0]:arguments;
return Math.sqrt(dojo.math.variance(_2f4));
};
dojo.math.variance=function(){
var _2f5=dojo.lang.isArray(arguments[0])?arguments[0]:arguments;
var mean=0,squares=0;
for(var i=0;i<_2f5.length;i++){
mean+=_2f5[i];
squares+=Math.pow(_2f5[i],2);
}
return (squares/_2f5.length)-Math.pow(mean/_2f5.length,2);
};
dojo.provide("dojo.graphics.color");
dojo.require("dojo.lang");
dojo.require("dojo.string");
dojo.require("dojo.math");
dojo.graphics.color.Color=function(r,g,b,a){
if(dojo.lang.isArray(r)){
this.r=r[0];
this.g=r[1];
this.b=r[2];
this.a=r[3]||1;
}else{
if(dojo.lang.isString(r)){
var rgb=dojo.graphics.color.extractRGB(r);
this.r=rgb[0];
this.g=rgb[1];
this.b=rgb[2];
this.a=g||1;
}else{
if(r instanceof dojo.graphics.color.Color){
this.r=r.r;
this.b=r.b;
this.g=r.g;
this.a=r.a;
}else{
this.r=r;
this.g=g;
this.b=b;
this.a=a;
}
}
}
};
dojo.graphics.color.Color.prototype.toRgb=function(_2fd){
if(_2fd){
return this.toRgba();
}else{
return [this.r,this.g,this.b];
}
};
dojo.graphics.color.Color.prototype.toRgba=function(){
return [this.r,this.g,this.b,this.a];
};
dojo.graphics.color.Color.prototype.toHex=function(){
return dojo.graphics.color.rgb2hex(this.toRgb());
};
dojo.graphics.color.Color.prototype.toCss=function(){
return "rgb("+this.toRgb().join()+")";
};
dojo.graphics.color.Color.prototype.toString=function(){
return this.toHex();
};
dojo.graphics.color.Color.prototype.toHsv=function(){
return dojo.graphics.color.rgb2hsv(this.toRgb());
};
dojo.graphics.color.Color.prototype.toHsl=function(){
return dojo.graphics.color.rgb2hsl(this.toRgb());
};
dojo.graphics.color.Color.prototype.blend=function(_2fe,_2ff){
return dojo.graphics.color.blend(this.toRgb(),new Color(_2fe).toRgb(),_2ff);
};
dojo.graphics.color.named={white:[255,255,255],black:[0,0,0],red:[255,0,0],green:[0,255,0],blue:[0,0,255],navy:[0,0,128],gray:[128,128,128],silver:[192,192,192]};
dojo.graphics.color.blend=function(a,b,_302){
if(typeof a=="string"){
return dojo.graphics.color.blendHex(a,b,_302);
}
if(!_302){
_302=0;
}else{
if(_302>1){
_302=1;
}else{
if(_302<-1){
_302=-1;
}
}
}
var c=new Array(3);
for(var i=0;i<3;i++){
var half=Math.abs(a[i]-b[i])/2;
c[i]=Math.floor(Math.min(a[i],b[i])+half+(half*_302));
}
return c;
};
dojo.graphics.color.blendHex=function(a,b,_308){
return dojo.graphics.color.rgb2hex(dojo.graphics.color.blend(dojo.graphics.color.hex2rgb(a),dojo.graphics.color.hex2rgb(b),_308));
};
dojo.graphics.color.extractRGB=function(_309){
var hex="0123456789abcdef";
_309=_309.toLowerCase();
if(_309.indexOf("rgb")==0){
var _30b=_309.match(/rgba*\((\d+), *(\d+), *(\d+)/i);
var ret=_30b.splice(1,3);
return ret;
}else{
var _30d=dojo.graphics.color.hex2rgb(_309);
if(_30d){
return _30d;
}else{
return dojo.graphics.color.named[_309]||[255,255,255];
}
}
};
dojo.graphics.color.hex2rgb=function(hex){
var _30f="0123456789ABCDEF";
var rgb=new Array(3);
if(hex.indexOf("#")==0){
hex=hex.substring(1);
}
hex=hex.toUpperCase();
if(hex.replace(new RegExp("["+_30f+"]","g"),"")!=""){
return null;
}
if(hex.length==3){
rgb[0]=hex.charAt(0)+hex.charAt(0);
rgb[1]=hex.charAt(1)+hex.charAt(1);
rgb[2]=hex.charAt(2)+hex.charAt(2);
}else{
rgb[0]=hex.substring(0,2);
rgb[1]=hex.substring(2,4);
rgb[2]=hex.substring(4);
}
for(var i=0;i<rgb.length;i++){
rgb[i]=_30f.indexOf(rgb[i].charAt(0))*16+_30f.indexOf(rgb[i].charAt(1));
}
return rgb;
};
dojo.graphics.color.rgb2hex=function(r,g,b){
if(dojo.lang.isArray(r)){
g=r[1]||0;
b=r[2]||0;
r=r[0]||0;
}
return ["#",dojo.string.pad(r.toString(16),2),dojo.string.pad(g.toString(16),2),dojo.string.pad(b.toString(16),2)].join("");
};
dojo.graphics.color.rgb2hsv=function(r,g,b){
if(dojo.lang.isArray(r)){
b=r[2]||0;
g=r[1]||0;
r=r[0]||0;
}
var h=null;
var s=null;
var v=null;
var min=Math.min(r,g,b);
v=Math.max(r,g,b);
var _31c=v-min;
s=(v==0)?0:_31c/v;
if(s==0){
h=0;
}else{
if(r==v){
h=60*(g-b)/_31c;
}else{
if(g==v){
h=120+60*(b-r)/_31c;
}else{
if(b==v){
h=240+60*(r-g)/_31c;
}
}
}
if(h<0){
h+=360;
}
}
h=(h==0)?360:Math.ceil((h/360)*255);
s=Math.ceil(s*255);
return [h,s,v];
};
dojo.graphics.color.hsv2rgb=function(h,s,v){
if(dojo.lang.isArray(h)){
v=h[2]||0;
s=h[1]||0;
h=h[0]||0;
}
h=(h/255)*360;
if(h==360){
h=0;
}
s=s/255;
v=v/255;
var r=null;
var g=null;
var b=null;
if(s==0){
r=v;
g=v;
b=v;
}else{
var _323=h/60;
var i=Math.floor(_323);
var f=_323-i;
var p=v*(1-s);
var q=v*(1-(s*f));
var t=v*(1-(s*(1-f)));
switch(i){
case 0:
r=v;
g=t;
b=p;
break;
case 1:
r=q;
g=v;
b=p;
break;
case 2:
r=p;
g=v;
b=t;
break;
case 3:
r=p;
g=q;
b=v;
break;
case 4:
r=t;
g=p;
b=v;
break;
case 5:
r=v;
g=p;
b=q;
break;
}
}
r=Math.ceil(r*255);
g=Math.ceil(g*255);
b=Math.ceil(b*255);
return [r,g,b];
};
dojo.graphics.color.rgb2hsl=function(r,g,b){
if(dojo.lang.isArray(r)){
b=r[2]||0;
g=r[1]||0;
r=r[0]||0;
}
r/=255;
g/=255;
b/=255;
var h=null;
var s=null;
var l=null;
var min=Math.min(r,g,b);
var max=Math.max(r,g,b);
var _331=max-min;
l=(min+max)/2;
s=0;
if((l>0)&&(l<1)){
s=_331/((l<0.5)?(2*l):(2-2*l));
}
h=0;
if(_331>0){
if((max==r)&&(max!=g)){
h+=(g-b)/_331;
}
if((max==g)&&(max!=b)){
h+=(2+(b-r)/_331);
}
if((max==b)&&(max!=r)){
h+=(4+(r-g)/_331);
}
h*=60;
}
h=(h==0)?360:Math.ceil((h/360)*255);
s=Math.ceil(s*255);
l=Math.ceil(l*255);
return [h,s,l];
};
dojo.graphics.color.hsl2rgb=function(h,s,l){
if(dojo.lang.isArray(h)){
l=h[2]||0;
s=h[1]||0;
h=h[0]||0;
}
h=(h/255)*360;
if(h==360){
h=0;
}
s=s/255;
l=l/255;
while(h<0){
h+=360;
}
while(h>360){
h-=360;
}
if(h<120){
r=(120-h)/60;
g=h/60;
b=0;
}else{
if(h<240){
r=0;
g=(240-h)/60;
b=(h-120)/60;
}else{
r=(h-240)/60;
g=0;
b=(360-h)/60;
}
}
r=Math.min(r,1);
g=Math.min(g,1);
b=Math.min(b,1);
r=2*s*r+(1-s);
g=2*s*g+(1-s);
b=2*s*b+(1-s);
if(l<0.5){
r=l*r;
g=l*g;
b=l*b;
}else{
r=(1-l)*r+2*l-1;
g=(1-l)*g+2*l-1;
b=(1-l)*b+2*l-1;
}
r=Math.ceil(r*255);
g=Math.ceil(g*255);
b=Math.ceil(b*255);
return [r,g,b];
};
dojo.provide("dojo.uri.Uri");
dojo.uri=new function(){
this.joinPath=function(){
var arr=[];
for(var i=0;i<arguments.length;i++){
arr.push(arguments[i]);
}
return arr.join("/").replace(/\/{2,}/g,"/").replace(/((https*|ftps*):)/i,"$1/");
};
this.dojoUri=function(uri){
return new dojo.uri.Uri(dojo.hostenv.getBaseScriptUri(),uri);
};
this.Uri=function(){
var uri=arguments[0];
for(var i=1;i<arguments.length;i++){
if(!arguments[i]){
continue;
}
var _33a=new dojo.uri.Uri(arguments[i].toString());
var _33b=new dojo.uri.Uri(uri.toString());
if(_33a.path==""&&_33a.scheme==null&&_33a.authority==null&&_33a.query==null){
if(_33a.fragment!=null){
_33b.fragment=_33a.fragment;
}
_33a=_33b;
}else{
if(_33a.scheme==null){
_33a.scheme=_33b.scheme;
if(_33a.authority==null){
_33a.authority=_33b.authority;
if(_33a.path.charAt(0)!="/"){
var path=_33b.path.substring(0,_33b.path.lastIndexOf("/")+1)+_33a.path;
var segs=path.split("/");
for(var j=0;j<segs.length;j++){
if(segs[j]=="."){
if(j==segs.length-1){
segs[j]="";
}else{
segs.splice(j,1);
j--;
}
}else{
if(j>0&&!(j==1&&segs[0]=="")&&segs[j]==".."&&segs[j-1]!=".."){
if(j==segs.length-1){
segs.splice(j,1);
segs[j-1]="";
}else{
segs.splice(j-1,2);
j-=2;
}
}
}
}
_33a.path=segs.join("/");
}
}
}
}
uri="";
if(_33a.scheme!=null){
uri+=_33a.scheme+":";
}
if(_33a.authority!=null){
uri+="//"+_33a.authority;
}
uri+=_33a.path;
if(_33a.query!=null){
uri+="?"+_33a.query;
}
if(_33a.fragment!=null){
uri+="#"+_33a.fragment;
}
}
this.uri=uri.toString();
var _33f="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\\?([^#]*))?(#(.*))?$";
var r=this.uri.match(new RegExp(_33f));
this.scheme=r[2]||(r[1]?"":null);
this.authority=r[4]||(r[3]?"":null);
this.path=r[5];
this.query=r[7]||(r[6]?"":null);
this.fragment=r[9]||(r[8]?"":null);
if(this.authority!=null){
_33f="^((([^:]+:)?([^@]+))@)?([^:]*)(:([0-9]+))?$";
r=this.authority.match(new RegExp(_33f));
this.user=r[3]||null;
this.password=r[4]||null;
this.host=r[5];
this.port=r[7]||null;
}
this.toString=function(){
return this.uri;
};
};
};
dojo.provide("dojo.style");
dojo.require("dojo.dom");
dojo.require("dojo.uri.Uri");
dojo.require("dojo.graphics.color");
dojo.style.boxSizing={marginBox:"margin-box",borderBox:"border-box",paddingBox:"padding-box",contentBox:"content-box"};
dojo.style.getBoxSizing=function(node){
if(dojo.render.html.ie||dojo.render.html.opera){
var cm=document["compatMode"];
if(cm=="BackCompat"||cm=="QuirksMode"){
return dojo.style.boxSizing.borderBox;
}else{
return dojo.style.boxSizing.contentBox;
}
}else{
if(arguments.length==0){
node=document.documentElement;
}
var _343=dojo.style.getStyle(node,"-moz-box-sizing");
if(!_343){
_343=dojo.style.getStyle(node,"box-sizing");
}
return (_343?_343:dojo.style.boxSizing.contentBox);
}
};
dojo.style.isBorderBox=function(node){
return (dojo.style.getBoxSizing(node)==dojo.style.boxSizing.borderBox);
};
dojo.style.getUnitValue=function(_345,_346,_347){
var _348={value:0,units:"px"};
var s=dojo.style.getComputedStyle(_345,_346);
if(s==""||(s=="auto"&&_347)){
return _348;
}
if(dojo.lang.isUndefined(s)){
_348.value=NaN;
}else{
var _34a=s.match(/([\d.]+)([a-z%]*)/i);
if(!_34a){
_348.value=NaN;
}else{
_348.value=Number(_34a[1]);
_348.units=_34a[2].toLowerCase();
}
}
return _348;
};
dojo.style.getPixelValue=function(_34b,_34c,_34d){
var _34e=dojo.style.getUnitValue(_34b,_34c,_34d);
if(isNaN(_34e.value)||(_34e.value&&_34e.units!="px")){
return NaN;
}
return _34e.value;
};
dojo.style.getNumericStyle=dojo.style.getPixelValue;
dojo.style.isPositionAbsolute=function(node){
return (dojo.style.getComputedStyle(node,"position")=="absolute");
};
dojo.style.getMarginWidth=function(node){
var _351=dojo.style.isPositionAbsolute(node);
var left=dojo.style.getPixelValue(node,"margin-left",_351);
var _353=dojo.style.getPixelValue(node,"margin-right",_351);
return left+_353;
};
dojo.style.getBorderWidth=function(node){
var left=(dojo.style.getStyle(node,"border-left-style")=="none"?0:dojo.style.getPixelValue(node,"border-left-width"));
var _356=(dojo.style.getStyle(node,"border-right-style")=="none"?0:dojo.style.getPixelValue(node,"border-right-width"));
return left+_356;
};
dojo.style.getPaddingWidth=function(node){
var left=dojo.style.getPixelValue(node,"padding-left",true);
var _359=dojo.style.getPixelValue(node,"padding-right",true);
return left+_359;
};
dojo.style.getContentWidth=function(node){
return node.offsetWidth-dojo.style.getPaddingWidth(node)-dojo.style.getBorderWidth(node);
};
dojo.style.getInnerWidth=function(node){
return node.offsetWidth;
};
dojo.style.getOuterWidth=function(node){
return dojo.style.getInnerWidth(node)+dojo.style.getMarginWidth(node);
};
dojo.style.setOuterWidth=function(node,_35e){
if(!dojo.style.isBorderBox(node)){
_35e-=dojo.style.getPaddingWidth(node)+dojo.style.getBorderWidth(node);
}
_35e-=dojo.style.getMarginWidth(node);
if(!isNaN(_35e)&&_35e>0){
node.style.width=_35e+"px";
return true;
}else{
return false;
}
};
dojo.style.getContentBoxWidth=dojo.style.getContentWidth;
dojo.style.getBorderBoxWidth=dojo.style.getInnerWidth;
dojo.style.getMarginBoxWidth=dojo.style.getOuterWidth;
dojo.style.setMarginBoxWidth=dojo.style.setOuterWidth;
dojo.style.getMarginHeight=function(node){
var _360=dojo.style.isPositionAbsolute(node);
var top=dojo.style.getPixelValue(node,"margin-top",_360);
var _362=dojo.style.getPixelValue(node,"margin-bottom",_360);
return top+_362;
};
dojo.style.getBorderHeight=function(node){
var top=(dojo.style.getStyle(node,"border-top-style")=="none"?0:dojo.style.getPixelValue(node,"border-top-width"));
var _365=(dojo.style.getStyle(node,"border-bottom-style")=="none"?0:dojo.style.getPixelValue(node,"border-bottom-width"));
return top+_365;
};
dojo.style.getPaddingHeight=function(node){
var top=dojo.style.getPixelValue(node,"padding-top",true);
var _368=dojo.style.getPixelValue(node,"padding-bottom",true);
return top+_368;
};
dojo.style.getContentHeight=function(node){
return node.offsetHeight-dojo.style.getPaddingHeight(node)-dojo.style.getBorderHeight(node);
};
dojo.style.getInnerHeight=function(node){
return node.offsetHeight;
};
dojo.style.getOuterHeight=function(node){
return dojo.style.getInnerHeight(node)+dojo.style.getMarginHeight(node);
};
dojo.style.setOuterHeight=function(node,_36d){
if(!dojo.style.isBorderBox(node)){
_36d-=dojo.style.getPaddingHeight(node)+dojo.style.getBorderHeight(node);
}
_36d-=dojo.style.getMarginHeight(node);
if(!isNaN(_36d)&&_36d>0){
node.style.height=_36d+"px";
return true;
}else{
return false;
}
};
dojo.style.setContentWidth=function(node,_36f){
if(dojo.style.isBorderBox(node)){
_36f+=dojo.style.getPaddingWidth(node)+dojo.style.getBorderWidth(node);
}
if(!isNaN(_36f)&&_36f>0){
node.style.width=_36f+"px";
return true;
}else{
return false;
}
};
dojo.style.setContentHeight=function(node,_371){
if(dojo.style.isBorderBox(node)){
_371+=dojo.style.getPaddingHeight(node)+dojo.style.getBorderHeight(node);
}
if(!isNaN(_371)&&_371>0){
node.style.height=_371+"px";
return true;
}else{
return false;
}
};
dojo.style.getContentBoxHeight=dojo.style.getContentHeight;
dojo.style.getBorderBoxHeight=dojo.style.getInnerHeight;
dojo.style.getMarginBoxHeight=dojo.style.getOuterHeight;
dojo.style.setMarginBoxHeight=dojo.style.setOuterHeight;
dojo.style.getTotalOffset=function(node,type,_374){
var _375=(type=="top")?"offsetTop":"offsetLeft";
var _376=(type=="top")?"scrollTop":"scrollLeft";
var alt=(type=="top")?"y":"x";
var ret=0;
if(node["offsetParent"]){
if(_374&&node.parentNode!=document.body){
ret-=dojo.style.sumAncestorProperties(node,_376);
}
do{
ret+=node[_375];
node=node.offsetParent;
}while(node!=document.getElementsByTagName("body")[0].parentNode&&node!=null);
}else{
if(node[alt]){
ret+=node[alt];
}
}
return ret;
};
dojo.style.sumAncestorProperties=function(node,prop){
if(!node){
return 0;
}
var _37b=0;
while(node){
var val=node[prop];
if(val){
_37b+=val-0;
}
node=node.parentNode;
}
return _37b;
};
dojo.style.totalOffsetLeft=function(node,_37e){
return dojo.style.getTotalOffset(node,"left",_37e);
};
dojo.style.getAbsoluteX=dojo.style.totalOffsetLeft;
dojo.style.totalOffsetTop=function(node,_380){
return dojo.style.getTotalOffset(node,"top",_380);
};
dojo.style.getAbsoluteY=dojo.style.totalOffsetTop;
dojo.style.getAbsolutePosition=function(node,_382){
var _383=[dojo.style.getAbsoluteX(node,_382),dojo.style.getAbsoluteY(node,_382)];
_383.x=_383[0];
_383.y=_383[1];
return _383;
};
dojo.style.styleSheet=null;
dojo.style.insertCssRule=function(_384,_385,_386){
if(!dojo.style.styleSheet){
if(document.createStyleSheet){
dojo.style.styleSheet=document.createStyleSheet();
}else{
if(document.styleSheets[0]){
dojo.style.styleSheet=document.styleSheets[0];
}else{
return null;
}
}
}
if(arguments.length<3){
if(dojo.style.styleSheet.cssRules){
_386=dojo.style.styleSheet.cssRules.length;
}else{
if(dojo.style.styleSheet.rules){
_386=dojo.style.styleSheet.rules.length;
}else{
return null;
}
}
}
if(dojo.style.styleSheet.insertRule){
var rule=_384+" { "+_385+" }";
return dojo.style.styleSheet.insertRule(rule,_386);
}else{
if(dojo.style.styleSheet.addRule){
return dojo.style.styleSheet.addRule(_384,_385,_386);
}else{
return null;
}
}
};
dojo.style.removeCssRule=function(_388){
if(!dojo.style.styleSheet){
dojo.debug("no stylesheet defined for removing rules");
return false;
}
if(dojo.render.html.ie){
if(!_388){
_388=dojo.style.styleSheet.rules.length;
dojo.style.styleSheet.removeRule(_388);
}
}else{
if(document.styleSheets[0]){
if(!_388){
_388=dojo.style.styleSheet.cssRules.length;
}
dojo.style.styleSheet.deleteRule(_388);
}
}
return true;
};
dojo.style.insertCssFile=function(URI,doc,_38b){
if(!URI){
return;
}
if(!doc){
doc=document;
}
if(doc.baseURI){
URI=new dojo.uri.Uri(doc.baseURI,URI);
}
if(_38b&&doc.styleSheets){
var loc=location.href.split("#")[0].substring(0,location.href.indexOf(location.pathname));
for(var i=0;i<doc.styleSheets.length;i++){
if(doc.styleSheets[i].href&&URI.toString()==new dojo.uri.Uri(doc.styleSheets[i].href.toString())){
return;
}
}
}
var file=doc.createElement("link");
file.setAttribute("type","text/css");
file.setAttribute("rel","stylesheet");
file.setAttribute("href",URI);
var head=doc.getElementsByTagName("head")[0];
if(head){
head.appendChild(file);
}
};
dojo.style.getBackgroundColor=function(node){
var _391;
do{
_391=dojo.style.getStyle(node,"background-color");
if(_391.toLowerCase()=="rgba(0, 0, 0, 0)"){
_391="transparent";
}
if(node==document.getElementsByTagName("body")[0]){
node=null;
break;
}
node=node.parentNode;
}while(node&&dojo.lang.inArray(_391,["transparent",""]));
if(_391=="transparent"){
_391=[255,255,255,0];
}else{
_391=dojo.graphics.color.extractRGB(_391);
}
return _391;
};
dojo.style.getComputedStyle=function(_392,_393,_394){
var _395=_394;
if(_392.style.getPropertyValue){
_395=_392.style.getPropertyValue(_393);
}
if(!_395){
if(document.defaultView){
_395=document.defaultView.getComputedStyle(_392,"").getPropertyValue(_393);
}else{
if(_392.currentStyle){
_395=_392.currentStyle[dojo.style.toCamelCase(_393)];
}
}
}
return _395;
};
dojo.style.getStyle=function(_396,_397){
var _398=dojo.style.toCamelCase(_397);
var _399=_396.style[_398];
return (_399?_399:dojo.style.getComputedStyle(_396,_397,_399));
};
dojo.style.toCamelCase=function(_39a){
var arr=_39a.split("-"),cc=arr[0];
for(var i=1;i<arr.length;i++){
cc+=arr[i].charAt(0).toUpperCase()+arr[i].substring(1);
}
return cc;
};
dojo.style.toSelectorCase=function(_39d){
return _39d.replace(/([A-Z])/g,"-$1").toLowerCase();
};
dojo.style.setOpacity=function setOpacity(node,_39f,_3a0){
node=dojo.byId(node);
var h=dojo.render.html;
if(!_3a0){
if(_39f>=1){
if(h.ie){
dojo.style.clearOpacity(node);
return;
}else{
_39f=0.999999;
}
}else{
if(_39f<0){
_39f=0;
}
}
}
if(h.ie){
if(node.nodeName.toLowerCase()=="tr"){
var tds=node.getElementsByTagName("td");
for(var x=0;x<tds.length;x++){
tds[x].style.filter="Alpha(Opacity="+_39f*100+")";
}
}
node.style.filter="Alpha(Opacity="+_39f*100+")";
}else{
if(h.moz){
node.style.opacity=_39f;
node.style.MozOpacity=_39f;
}else{
if(h.safari){
node.style.opacity=_39f;
node.style.KhtmlOpacity=_39f;
}else{
node.style.opacity=_39f;
}
}
}
};
dojo.style.getOpacity=function getOpacity(node){
if(dojo.render.html.ie){
var opac=(node.filters&&node.filters.alpha&&typeof node.filters.alpha.opacity=="number"?node.filters.alpha.opacity:100)/100;
}else{
var opac=node.style.opacity||node.style.MozOpacity||node.style.KhtmlOpacity||1;
}
return opac>=0.999999?1:Number(opac);
};
dojo.style.clearOpacity=function clearOpacity(node){
var h=dojo.render.html;
if(h.ie){
if(node.filters&&node.filters.alpha){
node.style.filter="";
}
}else{
if(h.moz){
node.style.opacity=1;
node.style.MozOpacity=1;
}else{
if(h.safari){
node.style.opacity=1;
node.style.KhtmlOpacity=1;
}else{
node.style.opacity=1;
}
}
}
};
dojo.provide("dojo.xml.domUtil");
dojo.require("dojo.graphics.color");
dojo.require("dojo.dom");
dojo.require("dojo.style");
dj_deprecated("dojo.xml.domUtil is deprecated, use dojo.dom instead");
dojo.xml.domUtil=new function(){
this.nodeTypes={ELEMENT_NODE:1,ATTRIBUTE_NODE:2,TEXT_NODE:3,CDATA_SECTION_NODE:4,ENTITY_REFERENCE_NODE:5,ENTITY_NODE:6,PROCESSING_INSTRUCTION_NODE:7,COMMENT_NODE:8,DOCUMENT_NODE:9,DOCUMENT_TYPE_NODE:10,DOCUMENT_FRAGMENT_NODE:11,NOTATION_NODE:12};
this.dojoml="http://www.dojotoolkit.org/2004/dojoml";
this.idIncrement=0;
this.getTagName=function(){
return dojo.dom.getTagName.apply(dojo.dom,arguments);
};
this.getUniqueId=function(){
return dojo.dom.getUniqueId.apply(dojo.dom,arguments);
};
this.getFirstChildTag=function(){
return dojo.dom.getFirstChildElement.apply(dojo.dom,arguments);
};
this.getLastChildTag=function(){
return dojo.dom.getLastChildElement.apply(dojo.dom,arguments);
};
this.getNextSiblingTag=function(){
return dojo.dom.getNextSiblingElement.apply(dojo.dom,arguments);
};
this.getPreviousSiblingTag=function(){
return dojo.dom.getPreviousSiblingElement.apply(dojo.dom,arguments);
};
this.forEachChildTag=function(node,_3a9){
var _3aa=this.getFirstChildTag(node);
while(_3aa){
if(_3a9(_3aa)=="break"){
break;
}
_3aa=this.getNextSiblingTag(_3aa);
}
};
this.moveChildren=function(){
return dojo.dom.moveChildren.apply(dojo.dom,arguments);
};
this.copyChildren=function(){
return dojo.dom.copyChildren.apply(dojo.dom,arguments);
};
this.clearChildren=function(){
return dojo.dom.removeChildren.apply(dojo.dom,arguments);
};
this.replaceChildren=function(){
return dojo.dom.replaceChildren.apply(dojo.dom,arguments);
};
this.getStyle=function(){
return dojo.style.getStyle.apply(dojo.style,arguments);
};
this.toCamelCase=function(){
return dojo.style.toCamelCase.apply(dojo.style,arguments);
};
this.toSelectorCase=function(){
return dojo.style.toSelectorCase.apply(dojo.style,arguments);
};
this.getAncestors=function(){
return dojo.dom.getAncestors.apply(dojo.dom,arguments);
};
this.isChildOf=function(){
return dojo.dom.isDescendantOf.apply(dojo.dom,arguments);
};
this.createDocumentFromText=function(){
return dojo.dom.createDocumentFromText.apply(dojo.dom,arguments);
};
if(dojo.render.html.capable||dojo.render.svg.capable){
this.createNodesFromText=function(txt,wrap){
return dojo.dom.createNodesFromText.apply(dojo.dom,arguments);
};
}
this.extractRGB=function(_3ad){
return dojo.graphics.color.extractRGB(_3ad);
};
this.hex2rgb=function(hex){
return dojo.graphics.color.hex2rgb(hex);
};
this.rgb2hex=function(r,g,b){
return dojo.graphics.color.rgb2hex(r,g,b);
};
this.insertBefore=function(){
return dojo.dom.insertBefore.apply(dojo.dom,arguments);
};
this.before=this.insertBefore;
this.insertAfter=function(){
return dojo.dom.insertAfter.apply(dojo.dom,arguments);
};
this.after=this.insertAfter;
this.insert=function(){
return dojo.dom.insertAtPosition.apply(dojo.dom,arguments);
};
this.insertAtIndex=function(){
return dojo.dom.insertAtIndex.apply(dojo.dom,arguments);
};
this.textContent=function(){
return dojo.dom.textContent.apply(dojo.dom,arguments);
};
this.renderedTextContent=function(){
return dojo.dom.renderedTextContent.apply(dojo.dom,arguments);
};
this.remove=function(node){
return dojo.dom.removeNode.apply(dojo.dom,arguments);
};
};
dojo.provide("dojo.html");
dojo.require("dojo.dom");
dojo.require("dojo.style");
dojo.require("dojo.string");
dojo.lang.mixin(dojo.html,dojo.dom);
dojo.lang.mixin(dojo.html,dojo.style);
dojo.html.clearSelection=function(){
try{
if(window["getSelection"]){
if(dojo.render.html.safari){
window.getSelection().collapse();
}else{
window.getSelection().removeAllRanges();
}
}else{
if((document.selection)&&(document.selection.clear)){
document.selection.clear();
}
}
return true;
}
catch(e){
dojo.debug(e);
return false;
}
};
dojo.html.disableSelection=function(_3b3){
_3b3=_3b3||dojo.html.body();
var h=dojo.render.html;
if(h.mozilla){
_3b3.style.MozUserSelect="none";
}else{
if(h.safari){
_3b3.style.KhtmlUserSelect="none";
}else{
if(h.ie){
_3b3.unselectable="on";
}else{
return false;
}
}
}
return true;
};
dojo.html.enableSelection=function(_3b5){
_3b5=_3b5||dojo.html.body();
var h=dojo.render.html;
if(h.mozilla){
_3b5.style.MozUserSelect="";
}else{
if(h.safari){
_3b5.style.KhtmlUserSelect="";
}else{
if(h.ie){
_3b5.unselectable="off";
}else{
return false;
}
}
}
return true;
};
dojo.html.selectElement=function(_3b7){
if(document.selection&&dojo.html.body().createTextRange){
var _3b8=dojo.html.body().createTextRange();
_3b8.moveToElementText(_3b7);
_3b8.select();
}else{
if(window["getSelection"]){
var _3b9=window.getSelection();
if(_3b9["selectAllChildren"]){
_3b9.selectAllChildren(_3b7);
}
}
}
};
dojo.html.isSelectionCollapsed=function(){
if(document["selection"]){
return document.selection.createRange().text=="";
}else{
if(window["getSelection"]){
var _3ba=window.getSelection();
if(dojo.lang.isString(_3ba)){
return _3ba=="";
}else{
return _3ba.isCollapsed;
}
}
}
};
dojo.html.getEventTarget=function(evt){
if(!evt){
evt=window.event||{};
}
if(evt.srcElement){
return evt.srcElement;
}else{
if(evt.target){
return evt.target;
}
}
return null;
};
dojo.html.getScrollTop=function(){
return document.documentElement.scrollTop||dojo.html.body().scrollTop||0;
};
dojo.html.getScrollLeft=function(){
return document.documentElement.scrollLeft||dojo.html.body().scrollLeft||0;
};
dojo.html.getDocumentWidth=function(){
dojo.deprecated("dojo.html.getDocument* has been deprecated in favor of dojo.html.getViewport*");
return dojo.html.getViewportWidth();
};
dojo.html.getDocumentHeight=function(){
dojo.deprecated("dojo.html.getDocument* has been deprecated in favor of dojo.html.getViewport*");
return dojo.html.getViewportHeight();
};
dojo.html.getDocumentSize=function(){
dojo.deprecated("dojo.html.getDocument* has been deprecated in favor of dojo.html.getViewport*");
return dojo.html.getViewportSize();
};
dojo.html.getViewportWidth=function(){
var w=0;
if(window.innerWidth){
w=window.innerWidth;
}
if(document.documentElement&&document.documentElement.clientWidth){
var w2=document.documentElement.clientWidth;
if(!w||w2&&w2<w){
w=w2;
}
return w;
}
if(document.body){
return document.body.clientWidth;
}
return 0;
};
dojo.html.getViewportHeight=function(){
if(window.innerHeight){
return window.innerHeight;
}
if(document.documentElement&&document.documentElement.clientHeight){
return document.documentElement.clientHeight;
}
if(document.body){
return document.body.clientHeight;
}
return 0;
};
dojo.html.getViewportSize=function(){
var ret=[dojo.html.getViewportWidth(),dojo.html.getViewportHeight()];
ret.w=ret[0];
ret.h=ret[1];
return ret;
};
dojo.html.getScrollOffset=function(){
var ret=[0,0];
if(window.pageYOffset){
ret=[window.pageXOffset,window.pageYOffset];
}else{
if(document.documentElement&&document.documentElement.scrollTop){
ret=[document.documentElement.scrollLeft,document.documentElement.scrollTop];
}else{
if(document.body){
ret=[document.body.scrollLeft,document.body.scrollTop];
}
}
}
ret.x=ret[0];
ret.y=ret[1];
return ret;
};
dojo.html.getParentOfType=function(node,type){
dojo.deprecated("dojo.html.getParentOfType has been deprecated in favor of dojo.html.getParentByType*");
return dojo.html.getParentByType(node,type);
};
dojo.html.getParentByType=function(node,type){
var _3c4=node;
type=type.toLowerCase();
while(_3c4.nodeName.toLowerCase()!=type){
if((!_3c4)||(_3c4==(document["body"]||document["documentElement"]))){
return null;
}
_3c4=_3c4.parentNode;
}
return _3c4;
};
dojo.html.getAttribute=function(node,attr){
if((!node)||(!node.getAttribute)){
return null;
}
var ta=typeof attr=="string"?attr:new String(attr);
var v=node.getAttribute(ta.toUpperCase());
if((v)&&(typeof v=="string")&&(v!="")){
return v;
}
if(v&&typeof v=="object"&&v.value){
return v.value;
}
if((node.getAttributeNode)&&(node.getAttributeNode(ta))){
return (node.getAttributeNode(ta)).value;
}else{
if(node.getAttribute(ta)){
return node.getAttribute(ta);
}else{
if(node.getAttribute(ta.toLowerCase())){
return node.getAttribute(ta.toLowerCase());
}
}
}
return null;
};
dojo.html.hasAttribute=function(node,attr){
var v=dojo.html.getAttribute(node,attr);
return v?true:false;
};
dojo.html.getClass=function(node){
if(node.className){
return node.className;
}else{
if(dojo.html.hasAttribute(node,"class")){
return dojo.html.getAttribute(node,"class");
}
}
return "";
};
dojo.html.hasClass=function(node,_3ce){
var _3cf=dojo.html.getClass(node).split(/\s+/g);
for(var x=0;x<_3cf.length;x++){
if(_3ce==_3cf[x]){
return true;
}
}
return false;
};
dojo.html.prependClass=function(node,_3d2){
if(!node){
return null;
}
if(dojo.html.hasAttribute(node,"class")||node.className){
_3d2+=" "+(node.className||dojo.html.getAttribute(node,"class"));
}
return dojo.html.setClass(node,_3d2);
};
dojo.html.addClass=function(node,_3d4){
if(!node){
throw new Error("addClass: node does not exist");
}
if(dojo.html.hasClass(node,_3d4)){
return false;
}
if(dojo.html.hasAttribute(node,"class")||node.className){
_3d4=(node.className||dojo.html.getAttribute(node,"class"))+" "+_3d4;
}
return dojo.html.setClass(node,_3d4);
};
dojo.html.setClass=function(node,_3d6){
if(!node){
return false;
}
var cs=new String(_3d6);
try{
if(typeof node.className=="string"){
node.className=cs;
}else{
if(node.setAttribute){
node.setAttribute("class",_3d6);
node.className=cs;
}else{
return false;
}
}
}
catch(e){
dojo.debug("dojo.html.setClass() failed",e);
}
return true;
};
dojo.html.removeClass=function(node,_3d9,_3da){
if(!node){
return false;
}
var _3d9=dojo.string.trim(new String(_3d9));
try{
var cs=String(node.className).split(" ");
var nca=[];
if(_3da){
for(var i=0;i<cs.length;i++){
if(cs[i].indexOf(_3d9)==-1){
nca.push(cs[i]);
}
}
}else{
for(var i=0;i<cs.length;i++){
if(cs[i]!=_3d9){
nca.push(cs[i]);
}
}
}
node.className=nca.join(" ");
}
catch(e){
dojo.debug("dojo.html.removeClass() failed",e);
}
return true;
};
dojo.html.replaceClass=function(node,_3df,_3e0){
dojo.html.removeClass(node,_3e0);
dojo.html.addClass(node,_3df);
};
dojo.html.classMatchType={ContainsAll:0,ContainsAny:1,IsOnly:2};
dojo.html.getElementsByClass=function(_3e1,_3e2,_3e3,_3e4){
if(!_3e2){
_3e2=document;
}
var _3e5=_3e1.split(/\s+/g);
var _3e6=[];
if(_3e4!=1&&_3e4!=2){
_3e4=0;
}
var _3e7=new RegExp("(\\s|^)(("+_3e5.join(")|(")+"))(\\s|$)");
if(false&&document.evaluate){
var _3e8="//"+(_3e3||"*")+"[contains(";
if(_3e4!=dojo.html.classMatchType.ContainsAny){
_3e8+="concat(' ',@class,' '), ' "+_3e5.join(" ') and contains(concat(' ',@class,' '), ' ")+" ')]";
}else{
_3e8+="concat(' ',@class,' '), ' "+_3e5.join(" ')) or contains(concat(' ',@class,' '), ' ")+" ')]";
}
var _3e9=document.evaluate(_3e8,_3e2,null,XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE,null);
outer:
for(var node=null,i=0;node=_3e9.snapshotItem(i);i++){
if(_3e4!=dojo.html.classMatchType.IsOnly){
_3e6.push(node);
}else{
if(!dojo.html.getClass(node)){
continue outer;
}
var _3eb=dojo.html.getClass(node).split(/\s+/g);
for(var j=0;j<_3eb.length;j++){
if(!_3eb[j].match(_3e7)){
continue outer;
}
}
_3e6.push(node);
}
}
}else{
if(!_3e3){
_3e3="*";
}
var _3ed=_3e2.getElementsByTagName(_3e3);
outer:
for(var i=0;i<_3ed.length;i++){
var node=_3ed[i];
if(!dojo.html.getClass(node)){
continue outer;
}
var _3eb=dojo.html.getClass(node).split(/\s+/g);
var _3ef=0;
for(var j=0;j<_3eb.length;j++){
if(_3e7.test(_3eb[j])){
if(_3e4==dojo.html.classMatchType.ContainsAny){
_3e6.push(node);
continue outer;
}else{
_3ef++;
}
}else{
if(_3e4==dojo.html.classMatchType.IsOnly){
continue outer;
}
}
}
if(_3ef==_3e5.length){
if(_3e4==dojo.html.classMatchType.IsOnly&&_3ef==_3eb.length){
_3e6.push(node);
}else{
if(_3e4==dojo.html.classMatchType.ContainsAll){
_3e6.push(node);
}
}
}
}
}
return _3e6;
};
dojo.html.getElementsByClassName=dojo.html.getElementsByClass;
dojo.html.gravity=function(node,e){
var _3f2=e.pageX||e.clientX+dojo.html.body().scrollLeft;
var _3f3=e.pageY||e.clientY+dojo.html.body().scrollTop;
with(dojo.html){
var _3f4=getAbsoluteX(node)+(getInnerWidth(node)/2);
var _3f5=getAbsoluteY(node)+(getInnerHeight(node)/2);
}
with(dojo.html.gravity){
return ((_3f2<_3f4?WEST:EAST)|(_3f3<_3f5?NORTH:SOUTH));
}
};
dojo.html.gravity.NORTH=1;
dojo.html.gravity.SOUTH=1<<1;
dojo.html.gravity.EAST=1<<2;
dojo.html.gravity.WEST=1<<3;
dojo.html.overElement=function(_3f6,e){
var _3f8=e.pageX||e.clientX+dojo.html.body().scrollLeft;
var _3f9=e.pageY||e.clientY+dojo.html.body().scrollTop;
with(dojo.html){
var top=getAbsoluteY(_3f6);
var _3fb=top+getInnerHeight(_3f6);
var left=getAbsoluteX(_3f6);
var _3fd=left+getInnerWidth(_3f6);
}
return (_3f8>=left&&_3f8<=_3fd&&_3f9>=top&&_3f9<=_3fb);
};
dojo.html.renderedTextContent=function(node){
var _3ff="";
if(node==null){
return _3ff;
}
for(var i=0;i<node.childNodes.length;i++){
switch(node.childNodes[i].nodeType){
case 1:
case 5:
switch(dojo.style.getStyle(node.childNodes[i],"display")){
case "block":
case "list-item":
case "run-in":
case "table":
case "table-row-group":
case "table-header-group":
case "table-footer-group":
case "table-row":
case "table-column-group":
case "table-column":
case "table-cell":
case "table-caption":
_3ff+="\n";
_3ff+=dojo.html.renderedTextContent(node.childNodes[i]);
_3ff+="\n";
break;
case "none":
break;
default:
_3ff+=dojo.html.renderedTextContent(node.childNodes[i]);
break;
}
break;
case 3:
case 2:
case 4:
var text=node.childNodes[i].nodeValue;
switch(dojo.style.getStyle(node,"text-transform")){
case "capitalize":
text=dojo.string.capitalize(text);
break;
case "uppercase":
text=text.toUpperCase();
break;
case "lowercase":
text=text.toLowerCase();
break;
default:
break;
}
switch(dojo.style.getStyle(node,"text-transform")){
case "nowrap":
break;
case "pre-wrap":
break;
case "pre-line":
break;
case "pre":
break;
default:
text=text.replace(/\s+/," ");
if(/\s$/.test(_3ff)){
text.replace(/^\s/,"");
}
break;
}
_3ff+=text;
break;
default:
break;
}
}
return _3ff;
};
dojo.html.setActiveStyleSheet=function(_402){
var i,a,main;
for(i=0;(a=document.getElementsByTagName("link")[i]);i++){
if(a.getAttribute("rel").indexOf("style")!=-1&&a.getAttribute("title")){
a.disabled=true;
if(a.getAttribute("title")==_402){
a.disabled=false;
}
}
}
};
dojo.html.getActiveStyleSheet=function(){
var i,a;
for(i=0;(a=document.getElementsByTagName("link")[i]);i++){
if(a.getAttribute("rel").indexOf("style")!=-1&&a.getAttribute("title")&&!a.disabled){
return a.getAttribute("title");
}
}
return null;
};
dojo.html.getPreferredStyleSheet=function(){
var i,a;
for(i=0;(a=document.getElementsByTagName("link")[i]);i++){
if(a.getAttribute("rel").indexOf("style")!=-1&&a.getAttribute("rel").indexOf("alt")==-1&&a.getAttribute("title")){
return a.getAttribute("title");
}
}
return null;
};
dojo.html.body=function(){
return document.body||document.getElementsByTagName("body")[0];
};
dojo.html.createNodesFromText=function(txt,wrap){
var tn=document.createElement("div");
tn.style.visibility="hidden";
document.body.appendChild(tn);
tn.innerHTML=txt;
tn.normalize();
if(wrap){
var ret=[];
var fc=tn.firstChild;
ret[0]=((fc.nodeValue==" ")||(fc.nodeValue=="\t"))?fc.nextSibling:fc;
document.body.removeChild(tn);
return ret;
}
var _40b=[];
for(var x=0;x<tn.childNodes.length;x++){
_40b.push(tn.childNodes[x].cloneNode(true));
}
tn.style.display="none";
document.body.removeChild(tn);
return _40b;
};
if(!dojo.evalObjPath("dojo.dom.createNodesFromText")){
dojo.dom.createNodesFromText=function(){
dojo.deprecated("dojo.dom.createNodesFromText","use dojo.html.createNodesFromText instead");
return dojo.html.createNodesFromText.apply(dojo.html,arguments);
};
}
dojo.html.getAncestorsByTag=function(node,tag,_40f){
tag=tag.toLowerCase();
return dojo.dom.getAncestors(node,function(el){
return el.tagName&&(el.tagName.toLowerCase()==tag);
},_40f);
};
dojo.html.getFirstAncestorByTag=function(node,tag){
return dojo.html.getAncestorsByTag(node,tag,true);
};
dojo.html.isVisible=function(node){
return dojo.style.getComputedStyle(node||this.domNode,"display")!="none";
};
dojo.html.show=function(node){
if(node.style){
node.style.display=dojo.lang.inArray(node.tagName.toLowerCase(),["tr","td","th"])?"":"block";
}
};
dojo.html.hide=function(node){
if(node.style){
node.style.display="none";
}
};
dojo.html.toCoordinateArray=function(_416,_417){
if(dojo.lang.isArray(_416)){
while(_416.length<4){
_416.push(0);
}
while(_416.length>4){
_416.pop();
}
var ret=_416;
}else{
var node=dojo.byId(_416);
var ret=[dojo.html.getAbsoluteX(node,_417),dojo.html.getAbsoluteY(node,_417),dojo.html.getInnerWidth(node),dojo.html.getInnerHeight(node)];
}
ret.x=ret[0];
ret.y=ret[1];
ret.w=ret[2];
ret.h=ret[3];
return ret;
};
dojo.html.keepOnScreen=function(node,_41b,_41c,_41d,_41e){
if(dojo.lang.isArray(_41b)){
_41e=_41d;
_41d=_41c;
_41c=_41b[1];
_41b=_41b[0];
}
if(!isNaN(_41d)){
_41d=[Number(_41d),Number(_41d)];
}else{
if(!dojo.lang.isArray(_41d)){
_41d=[0,0];
}
}
var _41f=dojo.html.getScrollOffset();
var view=dojo.html.getViewportSize();
node=dojo.byId(node);
var w=node.offsetWidth+_41d[0];
var h=node.offsetHeight+_41d[1];
if(_41e){
_41b-=_41f.x;
_41c-=_41f.y;
}
var x=_41b+w;
if(x>view.w){
x=view.w-w;
}else{
x=_41b;
}
x=Math.max(_41d[0],x)+_41f.x;
var y=_41c+h;
if(y>view.h){
y=view.h-h;
}else{
y=_41c;
}
y=Math.max(_41d[1],y)+_41f.y;
node.style.left=x+"px";
node.style.top=y+"px";
var ret=[x,y];
ret.x=x;
ret.y=y;
return ret;
};
dojo.html.keepOnScreenPoint=function(node,_427,_428,_429,_42a){
if(dojo.lang.isArray(_427)){
_42a=_429;
_429=_428;
_428=_427[1];
_427=_427[0];
}
var _42b=dojo.html.getScrollOffset();
var view=dojo.html.getViewportSize();
node=dojo.byId(node);
var w=node.offsetWidth;
var h=node.offsetHeight;
if(_42a){
_427-=_42b.x;
_428-=_42b.y;
}
var x=-1,y=-1;
if(_427+w<=view.w&&_428+h<=view.h){
x=_427;
y=_428;
}
if((x<0||y<0)&&_427<=view.w&&_428+h<=view.h){
x=_427-w;
y=_428;
}
if((x<0||y<0)&&_427+w<=view.w&&_428<=view.h){
x=_427;
y=_428-h;
}
if((x<0||y<0)&&_427<=view.w&&_428<=view.h){
x=_427-w;
y=_428-h;
}
if(x<0||y<0||(x+w>view.w)||(y+h>view.h)){
return dojo.html.keepOnScreen(node,_427,_428,_429,_42a);
}
x+=_42b.x;
y+=_42b.y;
node.style.left=x+"px";
node.style.top=y+"px";
var ret=[x,y];
ret.x=x;
ret.y=y;
return ret;
};
dojo.html.BackgroundIframe=function(){
if(this.ie){
this.iframe=document.createElement("<iframe frameborder='0' src='about:blank'>");
var s=this.iframe.style;
s.position="absolute";
s.left=s.top="0px";
s.zIndex=2;
s.display="none";
dojo.style.setOpacity(this.iframe,0);
dojo.html.body().appendChild(this.iframe);
}else{
this.enabled=false;
}
};
dojo.lang.extend(dojo.html.BackgroundIframe,{ie:dojo.render.html.ie,enabled:true,visibile:false,iframe:null,sizeNode:null,sizeCoords:null,size:function(node){
if(!this.ie||!this.enabled){
return;
}
if(dojo.dom.isNode(node)){
this.sizeNode=node;
}else{
if(arguments.length>0){
this.sizeNode=null;
this.sizeCoords=node;
}
}
this.update();
},update:function(){
if(!this.ie||!this.enabled){
return;
}
if(this.sizeNode){
this.sizeCoords=dojo.html.toCoordinateArray(this.sizeNode,true);
}else{
if(this.sizeCoords){
this.sizeCoords=dojo.html.toCoordinateArray(this.sizeCoords,true);
}else{
return;
}
}
var s=this.iframe.style;
var dims=this.sizeCoords;
s.width=dims.w+"px";
s.height=dims.h+"px";
s.left=dims.x+"px";
s.top=dims.y+"px";
},setZIndex:function(node){
if(!this.ie||!this.enabled){
return;
}
if(dojo.dom.isNode(node)){
this.iframe.zIndex=dojo.html.getStyle(node,"z-index")-1;
}else{
if(!isNaN(node)){
this.iframe.zIndex=node;
}
}
},show:function(node){
if(!this.ie||!this.enabled){
return;
}
this.size(node);
this.iframe.style.display="block";
},hide:function(){
if(!this.ie){
return;
}
var s=this.iframe.style;
s.display="none";
s.width=s.height="1px";
},remove:function(){
dojo.dom.removeNode(this.iframe);
}});
dojo.provide("dojo.xml.htmlUtil");
dojo.require("dojo.html");
dojo.require("dojo.style");
dojo.require("dojo.dom");
dj_deprecated("dojo.xml.htmlUtil is deprecated, use dojo.html instead");
dojo.xml.htmlUtil=new function(){
this.styleSheet=dojo.style.styleSheet;
this._clobberSelection=function(){
return dojo.html.clearSelection.apply(dojo.html,arguments);
};
this.disableSelect=function(){
return dojo.html.disableSelection.apply(dojo.html,arguments);
};
this.enableSelect=function(){
return dojo.html.enableSelection.apply(dojo.html,arguments);
};
this.getInnerWidth=function(){
return dojo.style.getInnerWidth.apply(dojo.style,arguments);
};
this.getOuterWidth=function(node){
dj_unimplemented("dojo.xml.htmlUtil.getOuterWidth");
};
this.getInnerHeight=function(){
return dojo.style.getInnerHeight.apply(dojo.style,arguments);
};
this.getOuterHeight=function(node){
dj_unimplemented("dojo.xml.htmlUtil.getOuterHeight");
};
this.getTotalOffset=function(){
return dojo.style.getTotalOffset.apply(dojo.style,arguments);
};
this.totalOffsetLeft=function(){
return dojo.style.totalOffsetLeft.apply(dojo.style,arguments);
};
this.getAbsoluteX=this.totalOffsetLeft;
this.totalOffsetTop=function(){
return dojo.style.totalOffsetTop.apply(dojo.style,arguments);
};
this.getAbsoluteY=this.totalOffsetTop;
this.getEventTarget=function(){
return dojo.html.getEventTarget.apply(dojo.html,arguments);
};
this.getScrollTop=function(){
return dojo.html.getScrollTop.apply(dojo.html,arguments);
};
this.getScrollLeft=function(){
return dojo.html.getScrollLeft.apply(dojo.html,arguments);
};
this.evtTgt=this.getEventTarget;
this.getParentOfType=function(){
return dojo.html.getParentOfType.apply(dojo.html,arguments);
};
this.getAttribute=function(){
return dojo.html.getAttribute.apply(dojo.html,arguments);
};
this.getAttr=function(node,attr){
dj_deprecated("dojo.xml.htmlUtil.getAttr is deprecated, use dojo.xml.htmlUtil.getAttribute instead");
return dojo.xml.htmlUtil.getAttribute(node,attr);
};
this.hasAttribute=function(){
return dojo.html.hasAttribute.apply(dojo.html,arguments);
};
this.hasAttr=function(node,attr){
dj_deprecated("dojo.xml.htmlUtil.hasAttr is deprecated, use dojo.xml.htmlUtil.hasAttribute instead");
return dojo.xml.htmlUtil.hasAttribute(node,attr);
};
this.getClass=function(){
return dojo.html.getClass.apply(dojo.html,arguments);
};
this.hasClass=function(){
return dojo.html.hasClass.apply(dojo.html,arguments);
};
this.prependClass=function(){
return dojo.html.prependClass.apply(dojo.html,arguments);
};
this.addClass=function(){
return dojo.html.addClass.apply(dojo.html,arguments);
};
this.setClass=function(){
return dojo.html.setClass.apply(dojo.html,arguments);
};
this.removeClass=function(){
return dojo.html.removeClass.apply(dojo.html,arguments);
};
this.classMatchType={ContainsAll:0,ContainsAny:1,IsOnly:2};
this.getElementsByClass=function(){
return dojo.html.getElementsByClass.apply(dojo.html,arguments);
};
this.getElementsByClassName=this.getElementsByClass;
this.setOpacity=function(){
return dojo.style.setOpacity.apply(dojo.style,arguments);
};
this.getOpacity=function(){
return dojo.style.getOpacity.apply(dojo.style,arguments);
};
this.clearOpacity=function(){
return dojo.style.clearOpacity.apply(dojo.style,arguments);
};
this.gravity=function(){
return dojo.html.gravity.apply(dojo.html,arguments);
};
this.gravity.NORTH=1;
this.gravity.SOUTH=1<<1;
this.gravity.EAST=1<<2;
this.gravity.WEST=1<<3;
this.overElement=function(){
return dojo.html.overElement.apply(dojo.html,arguments);
};
this.insertCssRule=function(){
return dojo.style.insertCssRule.apply(dojo.style,arguments);
};
this.insertCSSRule=function(_43e,_43f,_440){
dj_deprecated("dojo.xml.htmlUtil.insertCSSRule is deprecated, use dojo.xml.htmlUtil.insertCssRule instead");
return dojo.xml.htmlUtil.insertCssRule(_43e,_43f,_440);
};
this.removeCssRule=function(){
return dojo.style.removeCssRule.apply(dojo.style,arguments);
};
this.removeCSSRule=function(_441){
dj_deprecated("dojo.xml.htmlUtil.removeCSSRule is deprecated, use dojo.xml.htmlUtil.removeCssRule instead");
return dojo.xml.htmlUtil.removeCssRule(_441);
};
this.insertCssFile=function(){
return dojo.style.insertCssFile.apply(dojo.style,arguments);
};
this.insertCSSFile=function(URI,doc,_444){
dj_deprecated("dojo.xml.htmlUtil.insertCSSFile is deprecated, use dojo.xml.htmlUtil.insertCssFile instead");
return dojo.xml.htmlUtil.insertCssFile(URI,doc,_444);
};
this.getBackgroundColor=function(){
return dojo.style.getBackgroundColor.apply(dojo.style,arguments);
};
this.getUniqueId=function(){
return dojo.dom.getUniqueId();
};
this.getStyle=function(){
return dojo.style.getStyle.apply(dojo.style,arguments);
};
};
dojo.require("dojo.xml.Parse");
dojo.hostenv.conditionalLoadModule({common:["dojo.xml.domUtil"],browser:["dojo.xml.htmlUtil"],svg:["dojo.xml.svgUtil"]});
dojo.hostenv.moduleLoaded("dojo.xml.*");
dojo.provide("dojo.widget.Manager");
dojo.require("dojo.lang");
dojo.require("dojo.event");
dojo.widget.manager=new function(){
this.widgets=[];
this.widgetIds=[];
this.topWidgets={};
var _445={};
var _446=[];
this.getUniqueId=function(_447){
return _447+"_"+(_445[_447]!=undefined?++_445[_447]:_445[_447]=0);
};
this.add=function(_448){
dojo.profile.start("dojo.widget.manager.add");
this.widgets.push(_448);
if(_448.widgetId==""){
if(_448["id"]){
_448.widgetId=_448["id"];
}else{
if(_448.extraArgs["id"]){
_448.widgetId=_448.extraArgs["id"];
}else{
_448.widgetId=this.getUniqueId(_448.widgetType);
}
}
}
if(this.widgetIds[_448.widgetId]){
dojo.debug("widget ID collision on ID: "+_448.widgetId);
}
this.widgetIds[_448.widgetId]=_448;
dojo.profile.end("dojo.widget.manager.add");
};
this.destroyAll=function(){
for(var x=this.widgets.length-1;x>=0;x--){
try{
this.widgets[x].destroy(true);
delete this.widgets[x];
}
catch(e){
}
}
};
this.remove=function(_44a){
var tw=this.widgets[_44a].widgetId;
delete this.widgetIds[tw];
this.widgets.splice(_44a,1);
};
this.removeById=function(id){
for(var i=0;i<this.widgets.length;i++){
if(this.widgets[i].widgetId==id){
this.remove(i);
break;
}
}
};
this.getWidgetById=function(id){
return this.widgetIds[id];
};
this.getWidgetsByType=function(type){
var lt=type.toLowerCase();
var ret=[];
dojo.lang.forEach(this.widgets,function(x){
if(x.widgetType.toLowerCase()==lt){
ret.push(x);
}
});
return ret;
};
this.getWidgetsOfType=function(id){
dj_deprecated("getWidgetsOfType is depecrecated, use getWidgetsByType");
return dojo.widget.manager.getWidgetsByType(id);
};
this.getWidgetsByFilter=function(_454){
var ret=[];
dojo.lang.forEach(this.widgets,function(x){
if(_454(x)){
ret.push(x);
}
});
return ret;
};
this.getAllWidgets=function(){
return this.widgets.concat();
};
this.byId=this.getWidgetById;
this.byType=this.getWidgetsByType;
this.byFilter=this.getWidgetsByFilter;
var _457={};
var _458=["dojo.widget","dojo.webui.widgets"];
for(var i=0;i<_458.length;i++){
_458[_458[i]]=true;
}
this.registerWidgetPackage=function(_45a){
if(!_458[_45a]){
_458[_45a]=true;
_458.push(_45a);
}
};
this.getWidgetPackageList=function(){
return dojo.lang.map(_458,function(elt){
return (elt!==true?elt:undefined);
});
};
this.getImplementation=function(_45c,_45d,_45e){
var impl=this.getImplementationName(_45c);
if(impl){
var ret=new impl(_45d);
return ret;
}
};
this.getImplementationName=function(_461){
var _462=_461.toLowerCase();
var impl=_457[_462];
if(impl){
return impl;
}
if(!_446.length){
for(var _464 in dojo.render){
if(dojo.render[_464]["capable"]===true){
var _465=dojo.render[_464].prefixes;
for(var i=0;i<_465.length;i++){
_446.push(_465[i].toLowerCase());
}
}
}
_446.push("");
}
for(var i=0;i<_458.length;i++){
var _467=dojo.evalObjPath(_458[i]);
if(!_467){
continue;
}
for(var j=0;j<_446.length;j++){
if(!_467[_446[j]]){
continue;
}
for(var _469 in _467[_446[j]]){
if(_469.toLowerCase()!=_462){
continue;
}
_457[_462]=_467[_446[j]][_469];
return _457[_462];
}
}
for(var j=0;j<_446.length;j++){
for(var _469 in _467){
if(_469.toLowerCase()!=(_446[j]+_462)){
continue;
}
_457[_462]=_467[_469];
return _457[_462];
}
}
}
throw new Error("Could not locate \""+_461+"\" class");
};
this.onResized=function(){
for(var id in this.topWidgets){
var _46b=this.topWidgets[id];
if(_46b.onResized){
_46b.onResized();
}
}
};
if(typeof window!="undefined"){
dojo.addOnLoad(this,"onResized");
dojo.event.connect(window,"onresize",this,"onResized");
}
};
dojo.widget.getUniqueId=function(){
return dojo.widget.manager.getUniqueId.apply(dojo.widget.manager,arguments);
};
dojo.widget.addWidget=function(){
return dojo.widget.manager.add.apply(dojo.widget.manager,arguments);
};
dojo.widget.destroyAllWidgets=function(){
return dojo.widget.manager.destroyAll.apply(dojo.widget.manager,arguments);
};
dojo.widget.removeWidget=function(){
return dojo.widget.manager.remove.apply(dojo.widget.manager,arguments);
};
dojo.widget.removeWidgetById=function(){
return dojo.widget.manager.removeById.apply(dojo.widget.manager,arguments);
};
dojo.widget.getWidgetById=function(){
return dojo.widget.manager.getWidgetById.apply(dojo.widget.manager,arguments);
};
dojo.widget.getWidgetsByType=function(){
return dojo.widget.manager.getWidgetsByType.apply(dojo.widget.manager,arguments);
};
dojo.widget.getWidgetsByFilter=function(){
return dojo.widget.manager.getWidgetsByFilter.apply(dojo.widget.manager,arguments);
};
dojo.widget.byId=function(){
return dojo.widget.manager.getWidgetById.apply(dojo.widget.manager,arguments);
};
dojo.widget.byType=function(){
return dojo.widget.manager.getWidgetsByType.apply(dojo.widget.manager,arguments);
};
dojo.widget.byFilter=function(){
return dojo.widget.manager.getWidgetsByFilter.apply(dojo.widget.manager,arguments);
};
dojo.widget.all=function(){
return dojo.widget.manager.getAllWidgets.apply(dojo.widget.manager,arguments);
};
dojo.widget.registerWidgetPackage=function(){
return dojo.widget.manager.registerWidgetPackage.apply(dojo.widget.manager,arguments);
};
dojo.widget.getWidgetImplementation=function(){
return dojo.widget.manager.getImplementation.apply(dojo.widget.manager,arguments);
};
dojo.widget.getWidgetImplementationName=function(){
return dojo.widget.manager.getImplementationName.apply(dojo.widget.manager,arguments);
};
dojo.widget.widgets=dojo.widget.manager.widgets;
dojo.widget.widgetIds=dojo.widget.manager.widgetIds;
dojo.widget.root=dojo.widget.manager.root;
dojo.provide("dojo.widget.Parse");
dojo.require("dojo.widget.Manager");
dojo.require("dojo.string");
dojo.require("dojo.dom");
dojo.widget.Parse=function(_46c){
this.propertySetsList=[];
this.fragment=_46c;
this.createComponents=function(_46d,_46e){
var _46f=dojo.widget.tags;
var _470=[];
for(var item in _46d){
var _472=false;
try{
if(_46d[item]&&(_46d[item]["tagName"])&&(_46d[item]!=_46d["nodeRef"])){
var tn=new String(_46d[item]["tagName"]);
var tna=tn.split(";");
for(var x=0;x<tna.length;x++){
var ltn=dojo.string.trim(tna[x]).toLowerCase();
if(_46f[ltn]){
_472=true;
_46d[item].tagName=ltn;
_470.push(_46f[ltn](_46d[item],this,_46e,_46d[item]["index"]));
}else{
if(ltn.substr(0,5)=="dojo:"){
dojo.debug("no tag handler registed for type: ",ltn);
}
}
}
}
}
catch(e){
dojo.debug(e);
}
if((!_472)&&(typeof _46d[item]=="object")&&(_46d[item]!=_46d.nodeRef)&&(_46d[item]!=_46d["tagName"])){
_470.push(this.createComponents(_46d[item],_46e));
}
}
return _470;
};
this.parsePropertySets=function(_477){
return [];
var _478=[];
for(var item in _477){
if((_477[item]["tagName"]=="dojo:propertyset")){
_478.push(_477[item]);
}
}
this.propertySetsList.push(_478);
return _478;
};
this.parseProperties=function(_47a){
var _47b={};
for(var item in _47a){
if((_47a[item]==_47a["tagName"])||(_47a[item]==_47a.nodeRef)){
}else{
if((_47a[item]["tagName"])&&(dojo.widget.tags[_47a[item].tagName.toLowerCase()])){
}else{
if((_47a[item][0])&&(_47a[item][0].value!="")){
try{
if(item.toLowerCase()=="dataprovider"){
var _47d=this;
this.getDataProvider(_47d,_47a[item][0].value);
_47b.dataProvider=this.dataProvider;
}
_47b[item]=_47a[item][0].value;
var _47e=this.parseProperties(_47a[item]);
for(var _47f in _47e){
_47b[_47f]=_47e[_47f];
}
}
catch(e){
dj_debug(e);
}
}
}
}
}
return _47b;
};
this.getDataProvider=function(_480,_481){
dojo.io.bind({url:_481,load:function(type,_483){
if(type=="load"){
_480.dataProvider=_483;
}
},mimetype:"text/javascript",sync:true});
};
this.getPropertySetById=function(_484){
for(var x=0;x<this.propertySetsList.length;x++){
if(_484==this.propertySetsList[x]["id"][0].value){
return this.propertySetsList[x];
}
}
return "";
};
this.getPropertySetsByType=function(_486){
var _487=[];
for(var x=0;x<this.propertySetsList.length;x++){
var cpl=this.propertySetsList[x];
var cpcc=cpl["componentClass"]||cpl["componentType"]||null;
if((cpcc)&&(propertySetId==cpcc[0].value)){
_487.push(cpl);
}
}
return _487;
};
this.getPropertySets=function(_48b){
var ppl="dojo:propertyproviderlist";
var _48d=[];
var _48e=_48b["tagName"];
if(_48b[ppl]){
var _48f=_48b[ppl].value.split(" ");
for(propertySetId in _48f){
if((propertySetId.indexOf("..")==-1)&&(propertySetId.indexOf("://")==-1)){
var _490=this.getPropertySetById(propertySetId);
if(_490!=""){
_48d.push(_490);
}
}else{
}
}
}
return (this.getPropertySetsByType(_48e)).concat(_48d);
};
this.createComponentFromScript=function(_491,_492,_493,_494){
var frag={};
var _496="dojo:"+_492.toLowerCase();
frag[_496]={};
var bo={};
_493.dojotype=_492;
for(var prop in _493){
if(typeof bo[prop]=="undefined"){
frag[_496][prop]=[{value:_493[prop]}];
}
}
frag[_496].nodeRef=_491;
frag.tagName=_496;
var _499=[frag];
if(_494){
_499[0].fastMixIn=true;
}
return this.createComponents(_499);
};
};
dojo.widget._parser_collection={"dojo":new dojo.widget.Parse()};
dojo.widget.getParser=function(name){
if(!name){
name="dojo";
}
if(!this._parser_collection[name]){
this._parser_collection[name]=new dojo.widget.Parse();
}
return this._parser_collection[name];
};
dojo.widget.fromScript=function(name,_49c,_49d,_49e){
if((typeof name!="string")&&(typeof _49c=="string")){
return dojo.widget._oldFromScript(name,_49c,_49d);
}
_49c=_49c||{};
var _49f=false;
var tn=null;
var h=dojo.render.html.capable;
if(h){
tn=document.createElement("span");
}
if(!_49d){
_49f=true;
_49d=tn;
if(h){
dojo.html.body().appendChild(_49d);
}
}else{
if(_49e){
dojo.dom.insertAtPosition(tn,_49d,_49e);
}else{
tn=_49d;
}
}
var _4a2=dojo.widget._oldFromScript(tn,name,_49c);
if(!_4a2[0]||typeof _4a2[0].widgetType=="undefined"){
throw new Error("Creation of \""+name+"\" widget fromScript failed.");
}
if(_49f){
if(_4a2[0].domNode.parentNode){
_4a2[0].domNode.parentNode.removeChild(_4a2[0].domNode);
}
}
return _4a2[0];
};
dojo.widget._oldFromScript=function(_4a3,name,_4a5){
var ln=name.toLowerCase();
var tn="dojo:"+ln;
_4a5[tn]={dojotype:[{value:ln}],nodeRef:_4a3,fastMixIn:true};
var ret=dojo.widget.getParser().createComponentFromScript(_4a3,name,_4a5,true);
return ret;
};
dojo.provide("dojo.widget.Widget");
dojo.provide("dojo.widget.tags");
dojo.require("dojo.lang");
dojo.require("dojo.widget.Manager");
dojo.require("dojo.event.*");
dojo.require("dojo.string");
dojo.widget.Widget=function(){
this.children=[];
this.rightClickItems=[];
this.extraArgs={};
};
dojo.lang.extend(dojo.widget.Widget,{parent:null,isTopLevel:false,isModal:false,isEnabled:true,isHidden:false,isContainer:false,widgetId:"",widgetType:"Widget",toString:function(){
return "[Widget "+this.widgetType+", "+(this.widgetId||"NO ID")+"]";
},enable:function(){
this.isEnabled=true;
},disable:function(){
this.isEnabled=false;
},hide:function(){
this.isHidden=true;
},show:function(){
this.isHidden=false;
},create:function(args,_4aa,_4ab){
this.satisfyPropertySets(args,_4aa,_4ab);
this.mixInProperties(args,_4aa,_4ab);
this.postMixInProperties(args,_4aa,_4ab);
dojo.widget.manager.add(this);
this.buildRendering(args,_4aa,_4ab);
this.initialize(args,_4aa,_4ab);
this.postInitialize(args,_4aa,_4ab);
this.postCreate(args,_4aa,_4ab);
return this;
},destroy:function(_4ac){
this.uninitialize();
this.destroyRendering(_4ac);
dojo.widget.manager.removeById(this.widgetId);
},destroyChildren:function(_4ad){
_4ad=(!_4ad)?function(){
return true;
}:_4ad;
for(var x=0;x<this.children.length;x++){
var tc=this.children[x];
if((tc)&&(_4ad(tc))){
tc.destroy();
}
}
},destroyChildrenOfType:function(type){
type=type.toLowerCase();
this.destroyChildren(function(item){
if(item.widgetType.toLowerCase()==type){
return true;
}else{
return false;
}
});
},getChildrenOfType:function(type,_4b3){
var ret=[];
type=type.toLowerCase();
for(var x=0;x<this.children.length;x++){
if(this.children[x].widgetType.toLowerCase()==type){
ret.push(this.children[x]);
}
if(_4b3){
ret=ret.concat(this.children[x].getChildrenOfType(type,_4b3));
}
}
return ret;
},satisfyPropertySets:function(args){
return args;
},mixInProperties:function(args,frag){
if((args["fastMixIn"])||(frag["fastMixIn"])){
for(var x in args){
this[x]=args[x];
}
return;
}
var _4ba;
var _4bb=dojo.widget.lcArgsCache[this.widgetType];
if(_4bb==null){
_4bb={};
for(var y in this){
_4bb[((new String(y)).toLowerCase())]=y;
}
dojo.widget.lcArgsCache[this.widgetType]=_4bb;
}
var _4bd={};
for(var x in args){
if(!this[x]){
var y=_4bb[(new String(x)).toLowerCase()];
if(y){
args[y]=args[x];
x=y;
}
}
if(_4bd[x]){
continue;
}
_4bd[x]=true;
if((typeof this[x])!=(typeof _4ba)){
if(typeof args[x]!="string"){
this[x]=args[x];
}else{
if(dojo.lang.isString(this[x])){
this[x]=args[x];
}else{
if(dojo.lang.isNumber(this[x])){
this[x]=new Number(args[x]);
}else{
if(dojo.lang.isBoolean(this[x])){
this[x]=(args[x].toLowerCase()=="false")?false:true;
}else{
if(dojo.lang.isFunction(this[x])){
var tn=dojo.event.nameAnonFunc(new Function(args[x]),this);
dojo.event.connect(this,x,this,tn);
}else{
if(dojo.lang.isArray(this[x])){
this[x]=args[x].split(";");
}else{
if(this[x] instanceof Date){
this[x]=new Date(Number(args[x]));
}else{
if(typeof this[x]=="object"){
var _4bf=args[x].split(";");
for(var y=0;y<_4bf.length;y++){
var si=_4bf[y].indexOf(":");
if((si!=-1)&&(_4bf[y].length>si)){
this[x][dojo.string.trim(_4bf[y].substr(0,si))]=_4bf[y].substr(si+1);
}
}
}else{
this[x]=args[x];
}
}
}
}
}
}
}
}
}else{
this.extraArgs[x]=args[x];
}
}
},postMixInProperties:function(){
},initialize:function(args,frag){
return false;
},postInitialize:function(args,frag){
return false;
},postCreate:function(args,frag){
return false;
},uninitialize:function(){
return false;
},buildRendering:function(){
dj_unimplemented("dojo.widget.Widget.buildRendering");
return false;
},destroyRendering:function(){
dj_unimplemented("dojo.widget.Widget.destroyRendering");
return false;
},cleanUp:function(){
dj_unimplemented("dojo.widget.Widget.cleanUp");
return false;
},addedTo:function(_4c7){
},addChild:function(_4c8){
dj_unimplemented("dojo.widget.Widget.addChild");
return false;
},addChildAtIndex:function(_4c9,_4ca){
dj_unimplemented("dojo.widget.Widget.addChildAtIndex");
return false;
},removeChild:function(_4cb){
dj_unimplemented("dojo.widget.Widget.removeChild");
return false;
},removeChildAtIndex:function(_4cc){
dj_unimplemented("dojo.widget.Widget.removeChildAtIndex");
return false;
},resize:function(_4cd,_4ce){
this.setWidth(_4cd);
this.setHeight(_4ce);
},setWidth:function(_4cf){
if((typeof _4cf=="string")&&(_4cf.substr(-1)=="%")){
this.setPercentageWidth(_4cf);
}else{
this.setNativeWidth(_4cf);
}
},setHeight:function(_4d0){
if((typeof _4d0=="string")&&(_4d0.substr(-1)=="%")){
this.setPercentageHeight(_4d0);
}else{
this.setNativeHeight(_4d0);
}
},setPercentageHeight:function(_4d1){
return false;
},setNativeHeight:function(_4d2){
return false;
},setPercentageWidth:function(_4d3){
return false;
},setNativeWidth:function(_4d4){
return false;
}});
dojo.widget.lcArgsCache={};
dojo.widget.tags={};
dojo.widget.tags.addParseTreeHandler=function(type){
var _4d6=type.toLowerCase();
this[_4d6]=function(_4d7,_4d8,_4d9,_4da){
return dojo.widget.buildWidgetFromParseTree(_4d6,_4d7,_4d8,_4d9,_4da);
};
};
dojo.widget.tags.addParseTreeHandler("dojo:widget");
dojo.widget.tags["dojo:propertyset"]=function(_4db,_4dc,_4dd){
var _4de=_4dc.parseProperties(_4db["dojo:propertyset"]);
};
dojo.widget.tags["dojo:connect"]=function(_4df,_4e0,_4e1){
var _4e2=_4e0.parseProperties(_4df["dojo:connect"]);
};
dojo.widget.buildWidgetFromParseTree=function(type,frag,_4e5,_4e6,_4e7){
var _4e8={};
var _4e9=type.split(":");
_4e9=(_4e9.length==2)?_4e9[1]:type;
var _4e8=_4e5.parseProperties(frag["dojo:"+_4e9]);
var _4ea=dojo.widget.manager.getImplementation(_4e9);
if(!_4ea){
throw new Error("cannot find \""+_4e9+"\" widget");
}else{
if(!_4ea.create){
throw new Error("\""+_4e9+"\" widget object does not appear to implement *Widget");
}
}
_4e8["dojoinsertionindex"]=_4e7;
var ret=_4ea.create(_4e8,frag,_4e6);
return ret;
};
dojo.hostenv.conditionalLoadModule({common:["dojo.uri.Uri",false,false]});
dojo.hostenv.moduleLoaded("dojo.uri.*");
dojo.provide("dojo.widget.DomWidget");
dojo.require("dojo.event.*");
dojo.require("dojo.string");
dojo.require("dojo.widget.Widget");
dojo.require("dojo.dom");
dojo.require("dojo.xml.Parse");
dojo.require("dojo.uri.*");
dojo.widget._cssFiles={};
dojo.widget.defaultStrings={dojoRoot:dojo.hostenv.getBaseScriptUri(),baseScriptUri:dojo.hostenv.getBaseScriptUri()};
dojo.widget.buildFromTemplate=function(obj,_4ed,_4ee,_4ef){
var _4f0=_4ed||obj.templatePath;
var _4f1=_4ee||obj.templateCssPath;
if(!_4f1&&obj.templateCSSPath){
obj.templateCssPath=_4f1=obj.templateCSSPath;
obj.templateCSSPath=null;
dj_deprecated("templateCSSPath is deprecated, use templateCssPath");
}
if(_4f0&&!(_4f0 instanceof dojo.uri.Uri)){
_4f0=dojo.uri.dojoUri(_4f0);
dj_deprecated("templatePath should be of type dojo.uri.Uri");
}
if(_4f1&&!(_4f1 instanceof dojo.uri.Uri)){
_4f1=dojo.uri.dojoUri(_4f1);
dj_deprecated("templateCssPath should be of type dojo.uri.Uri");
}
var _4f2=dojo.widget.DomWidget.templates;
if(!obj["widgetType"]){
do{
var _4f3="__dummyTemplate__"+dojo.widget.buildFromTemplate.dummyCount++;
}while(_4f2[_4f3]);
obj.widgetType=_4f3;
}
if((_4f1)&&(!dojo.widget._cssFiles[_4f1])){
dojo.html.insertCssFile(_4f1);
obj.templateCssPath=null;
dojo.widget._cssFiles[_4f1]=true;
}
var ts=_4f2[obj.widgetType];
if(!ts){
_4f2[obj.widgetType]={};
ts=_4f2[obj.widgetType];
}
if(!obj.templateString){
obj.templateString=_4ef||ts["string"];
}
if(!obj.templateNode){
obj.templateNode=ts["node"];
}
if((!obj.templateNode)&&(!obj.templateString)&&(_4f0)){
var _4f5=dojo.hostenv.getText(_4f0);
if(_4f5){
var _4f6=_4f5.match(/<body[^>]*>\s*([\s\S]+)\s*<\/body>/im);
if(_4f6){
_4f5=_4f6[1];
}
}else{
_4f5="";
}
obj.templateString=_4f5;
ts.string=_4f5;
}
if(!ts["string"]){
ts.string=obj.templateString;
}
};
dojo.widget.buildFromTemplate.dummyCount=0;
dojo.widget.attachProperties=["dojoAttachPoint","id"];
dojo.widget.eventAttachProperty="dojoAttachEvent";
dojo.widget.onBuildProperty="dojoOnBuild";
dojo.widget.attachTemplateNodes=function(_4f7,_4f8,_4f9){
var _4fa=dojo.dom.ELEMENT_NODE;
if(!_4f7){
_4f7=_4f8.domNode;
}
if(_4f7.nodeType!=_4fa){
return;
}
var _4fb=_4f7.getElementsByTagName("*");
var _4fc=_4f8;
for(var x=-1;x<_4fb.length;x++){
var _4fe=(x==-1)?_4f7:_4fb[x];
var _4ff=[];
for(var y=0;y<this.attachProperties.length;y++){
var _501=_4fe.getAttribute(this.attachProperties[y]);
if(_501){
_4ff=_501.split(";");
for(var z=0;z<this.attachProperties.length;z++){
if((_4f8[_4ff[z]])&&(dojo.lang.isArray(_4f8[_4ff[z]]))){
_4f8[_4ff[z]].push(_4fe);
}else{
_4f8[_4ff[z]]=_4fe;
}
}
break;
}
}
var _503=_4fe.getAttribute(this.templateProperty);
if(_503){
_4f8[_503]=_4fe;
}
var _504=_4fe.getAttribute(this.eventAttachProperty);
if(_504){
var evts=_504.split(";");
for(var y=0;y<evts.length;y++){
if((!evts[y])||(!evts[y].length)){
continue;
}
var _506=null;
var tevt=dojo.string.trim(evts[y]);
if(evts[y].indexOf(":")>=0){
var _508=tevt.split(":");
tevt=dojo.string.trim(_508[0]);
_506=dojo.string.trim(_508[1]);
}
if(!_506){
_506=tevt;
}
var tf=function(){
var ntf=new String(_506);
return function(evt){
if(_4fc[ntf]){
_4fc[ntf](dojo.event.browser.fixEvent(evt));
}
};
}();
dojo.event.browser.addListener(_4fe,tevt,tf,false,true);
}
}
for(var y=0;y<_4f9.length;y++){
var _50c=_4fe.getAttribute(_4f9[y]);
if((_50c)&&(_50c.length)){
var _506=null;
var _50d=_4f9[y].substr(4);
_506=dojo.string.trim(_50c);
var tf=function(){
var ntf=new String(_506);
return function(evt){
if(_4fc[ntf]){
_4fc[ntf](dojo.event.browser.fixEvent(evt));
}
};
}();
dojo.event.browser.addListener(_4fe,_50d,tf,false,true);
}
}
var _510=_4fe.getAttribute(this.onBuildProperty);
if(_510){
eval("var node = baseNode; var widget = targetObj; "+_510);
}
_4fe.id="";
}
};
dojo.widget.getDojoEventsFromStr=function(str){
var re=/(dojoOn([a-z]+)(\s?))=/gi;
var evts=str?str.match(re)||[]:[];
var ret=[];
var lem={};
for(var x=0;x<evts.length;x++){
if(evts[x].legth<1){
continue;
}
var cm=evts[x].replace(/\s/,"");
cm=(cm.slice(0,cm.length-1));
if(!lem[cm]){
lem[cm]=true;
ret.push(cm);
}
}
return ret;
};
dojo.widget.buildAndAttachTemplate=function(obj,_519,_51a,_51b,_51c){
this.buildFromTemplate(obj,_519,_51a,_51b);
var node=dojo.dom.createNodesFromText(obj.templateString,true)[0];
this.attachTemplateNodes(node,_51c||obj,dojo.widget.getDojoEventsFromStr(_51b));
return node;
};
dojo.widget.DomWidget=function(){
dojo.widget.Widget.call(this);
if((arguments.length>0)&&(typeof arguments[0]=="object")){
this.create(arguments[0]);
}
};
dojo.inherits(dojo.widget.DomWidget,dojo.widget.Widget);
dojo.lang.extend(dojo.widget.DomWidget,{templateNode:null,templateString:null,preventClobber:false,domNode:null,containerNode:null,addChild:function(_51e,_51f,pos,ref,_522){
if(!this.isContainer){
dojo.debug("dojo.widget.DomWidget.addChild() attempted on non-container widget");
return null;
}else{
this.addWidgetAsDirectChild(_51e,_51f,pos,ref,_522);
this.registerChild(_51e);
}
return _51e;
},addWidgetAsDirectChild:function(_523,_524,pos,ref,_527){
if((!this.containerNode)&&(!_524)){
this.containerNode=this.domNode;
}
var cn=(_524)?_524:this.containerNode;
if(!pos){
pos="after";
}
if(!ref){
ref=cn.lastChild;
}
if(!_527){
_527=0;
}
_523.domNode.setAttribute("dojoinsertionindex",_527);
if(!ref){
cn.appendChild(_523.domNode);
}else{
if(pos=="insertAtIndex"){
dojo.dom.insertAtIndex(_523.domNode,ref.parentNode,_527);
}else{
if((pos=="after")&&(ref===cn.lastChild)){
cn.appendChild(_523.domNode);
}else{
dojo.dom.insertAtPosition(_523.domNode,cn,pos);
}
}
}
},registerChild:function(_529,_52a){
_529.dojoInsertionIndex=_52a;
var idx=-1;
for(var i=0;i<this.children.length;i++){
if(this.children[i].dojoInsertionIndex<_52a){
idx=i;
}
}
this.children.splice(idx+1,0,_529);
_529.parent=this;
_529.addedTo(this);
delete dojo.widget.manager.topWidgets[_529.widgetId];
},removeChild:function(_52d){
for(var x=0;x<this.children.length;x++){
if(this.children[x]===_52d){
this.children.splice(x,1);
break;
}
}
return _52d;
},getFragNodeRef:function(frag){
return (frag?frag["dojo:"+this.widgetType.toLowerCase()]["nodeRef"]:null);
},postInitialize:function(args,frag,_532){
var _533=this.getFragNodeRef(frag);
if(_532&&(_532.snarfChildDomOutput||!_533)){
_532.addWidgetAsDirectChild(this,"","insertAtIndex","",args["dojoinsertionindex"],_533);
}else{
if(_533){
if(this.domNode&&(this.domNode!==_533)){
var _534=_533.parentNode.replaceChild(this.domNode,_533);
}
}
}
if(_532){
_532.registerChild(this,args.dojoinsertionindex);
}else{
dojo.widget.manager.topWidgets[this.widgetId]=this;
}
if(this.isContainer){
var _535=dojo.widget.getParser();
_535.createComponents(frag,this);
}
},startResize:function(_536){
dj_unimplemented("dojo.widget.DomWidget.startResize");
},updateResize:function(_537){
dj_unimplemented("dojo.widget.DomWidget.updateResize");
},endResize:function(_538){
dj_unimplemented("dojo.widget.DomWidget.endResize");
},buildRendering:function(args,frag){
var ts=dojo.widget.DomWidget.templates[this.widgetType];
if((!this.preventClobber)&&((this.templatePath)||(this.templateNode)||((this["templateString"])&&(this.templateString.length))||((typeof ts!="undefined")&&((ts["string"])||(ts["node"]))))){
this.buildFromTemplate(args,frag);
}else{
this.domNode=this.getFragNodeRef(frag);
}
this.fillInTemplate(args,frag);
},buildFromTemplate:function(args,frag){
var ts=dojo.widget.DomWidget.templates[this.widgetType];
if(ts){
if(!this.templateString.length){
this.templateString=ts["string"];
}
if(!this.templateNode){
this.templateNode=ts["node"];
}
}
var node=null;
if((!this.templateNode)&&(this.templateString)){
var _540=this.templateString.match(/\$\{([^\}]+)\}/g);
if(_540){
var hash=this.strings||{};
for(var key in dojo.widget.defaultStrings){
if(dojo.lang.isUndefined(hash[key])){
hash[key]=dojo.widget.defaultStrings[key];
}
}
for(var i=0;i<_540.length;i++){
var key=_540[i];
key=key.substring(2,key.length-1);
if(hash[key]){
if(dojo.lang.isFunction(hash[key])){
var _544=hash[key].call(this,key,this.templateString);
}else{
var _544=hash[key];
}
this.templateString=this.templateString.replace(_540[i],_544);
}
}
}
this.templateNode=this.createNodesFromText(this.templateString,true)[0];
ts.node=this.templateNode;
}
if(!this.templateNode){
dojo.debug("weren't able to create template!");
return false;
}
var node=this.templateNode.cloneNode(true);
if(!node){
return false;
}
this.domNode=node;
this.attachTemplateNodes(this.domNode,this);
if(this.isContainer&&this.containerNode){
var src=this.getFragNodeRef(frag);
if(src){
dojo.dom.moveChildren(src,this.containerNode);
}
}
},attachTemplateNodes:function(_546,_547){
if(!_547){
_547=this;
}
return dojo.widget.attachTemplateNodes(_546,_547,dojo.widget.getDojoEventsFromStr(this.templateString));
},fillInTemplate:function(){
},destroyRendering:function(){
try{
var _548=this.domNode.parentNode.removeChild(this.domNode);
delete _548;
}
catch(e){
}
},cleanUp:function(){
},getContainerHeight:function(){
return dojo.html.getInnerHeight(this.domNode.parentNode);
},getContainerWidth:function(){
return dojo.html.getInnerWidth(this.domNode.parentNode);
},createNodesFromText:function(){
dj_unimplemented("dojo.widget.DomWidget.createNodesFromText");
}});
dojo.widget.DomWidget.templates={};
dojo.provide("dojo.widget.HtmlWidget");
dojo.require("dojo.widget.DomWidget");
dojo.require("dojo.html");
dojo.require("dojo.string");
dojo.widget.HtmlWidget=function(args){
dojo.widget.DomWidget.call(this);
};
dojo.inherits(dojo.widget.HtmlWidget,dojo.widget.DomWidget);
dojo.lang.extend(dojo.widget.HtmlWidget,{templateCssPath:null,templatePath:null,allowResizeX:true,allowResizeY:true,resizeGhost:null,initialResizeCoords:null,toggle:"plain",toggleDuration:150,initialize:function(args,frag){
},postMixInProperties:function(args,frag){
dojo.lang.mixin(this,dojo.widget.HtmlWidget.Toggle[dojo.string.capitalize(this.toggle)]||dojo.widget.HtmlWidget.Toggle.Plain);
},getContainerHeight:function(){
dj_unimplemented("dojo.widget.HtmlWidget.getContainerHeight");
},getContainerWidth:function(){
return this.parent.domNode.offsetWidth;
},setNativeHeight:function(_54e){
var ch=this.getContainerHeight();
},startResize:function(_550){
_550.offsetLeft=dojo.html.totalOffsetLeft(this.domNode);
_550.offsetTop=dojo.html.totalOffsetTop(this.domNode);
_550.innerWidth=dojo.html.getInnerWidth(this.domNode);
_550.innerHeight=dojo.html.getInnerHeight(this.domNode);
if(!this.resizeGhost){
this.resizeGhost=document.createElement("div");
var rg=this.resizeGhost;
rg.style.position="absolute";
rg.style.backgroundColor="white";
rg.style.border="1px solid black";
dojo.html.setOpacity(rg,0.3);
dojo.html.body().appendChild(rg);
}
with(this.resizeGhost.style){
left=_550.offsetLeft+"px";
top=_550.offsetTop+"px";
}
this.initialResizeCoords=_550;
this.resizeGhost.style.display="";
this.updateResize(_550,true);
},updateResize:function(_552,_553){
var dx=_552.x-this.initialResizeCoords.x;
var dy=_552.y-this.initialResizeCoords.y;
with(this.resizeGhost.style){
if((this.allowResizeX)||(_553)){
width=this.initialResizeCoords.innerWidth+dx+"px";
}
if((this.allowResizeY)||(_553)){
height=this.initialResizeCoords.innerHeight+dy+"px";
}
}
},endResize:function(_556){
var dx=_556.x-this.initialResizeCoords.x;
var dy=_556.y-this.initialResizeCoords.y;
with(this.domNode.style){
if(this.allowResizeX){
width=this.initialResizeCoords.innerWidth+dx+"px";
}
if(this.allowResizeY){
height=this.initialResizeCoords.innerHeight+dy+"px";
}
}
this.resizeGhost.style.display="none";
},createNodesFromText:function(txt,wrap){
return dojo.html.createNodesFromText(txt,wrap);
},_old_buildFromTemplate:dojo.widget.DomWidget.prototype.buildFromTemplate,buildFromTemplate:function(args,frag){
dojo.widget.buildFromTemplate(this);
this._old_buildFromTemplate(args,frag);
},destroyRendering:function(_55d){
try{
var _55e=this.domNode.parentNode.removeChild(this.domNode);
if(!_55d){
dojo.event.browser.clean(_55e);
}
delete _55e;
}
catch(e){
}
},isVisible:function(){
return dojo.html.isVisible(this.domNode);
},doToggle:function(){
this.isVisible()?this.hide():this.show();
},show:function(){
this.showMe();
},hide:function(){
this.hideMe();
}});
dojo.widget.HtmlWidget.Toggle={};
dojo.widget.HtmlWidget.Toggle.Plain={showMe:function(){
dojo.html.show(this.domNode);
},hideMe:function(){
dojo.html.hide(this.domNode);
}};
dojo.widget.HtmlWidget.Toggle.Fade={showMe:function(){
dojo.fx.html.fadeShow(this.domNode,this.toggleDuration);
},hideMe:function(){
dojo.fx.html.fadeHide(this.domNode,this.toggleDuration);
}};
dojo.widget.HtmlWidget.Toggle.Wipe={showMe:function(){
dojo.fx.html.wipeIn(this.domNode,this.toggleDuration);
},hideMe:function(){
dojo.fx.html.wipeOut(this.domNode,this.toggleDuration);
}};
dojo.widget.HtmlWidget.Toggle.Explode={showMe:function(){
dojo.fx.html.explode(this.explodeSrc,this.domNode,this.toggleDuration);
},hideMe:function(){
dojo.fx.html.implode(this.domNode,this.explodeSrc,this.toggleDuration);
}};
dojo.provide("dojo.widget.html.Button");
dojo.require("dojo.widget.HtmlWidget");
dojo.require("dojo.widget.Button");
dojo.widget.html.Button=function(){
dojo.widget.Button.call(this);
dojo.widget.HtmlWidget.call(this);
};
dojo.inherits(dojo.widget.html.Button,dojo.widget.HtmlWidget);
dojo.lang.extend(dojo.widget.html.Button,{templateString:"<button style=\"padding-top: 2px; padding-bottom: 2px;\" class='dojoButton dojoButtonNoHover' resizeHandle='true'\n	dojoAttachEvent='onMouseOver; onMouseOut; onClick;'\n	dojoAttachPoint='containerNode'></button>\n",templateCssPath:dojo.uri.dojoUri("src/widget/templates/HtmlButtonTemplate.css"),label:"undefined",labelNode:null,containerNode:null,postCreate:function(args,frag){
this.labelNode=this.containerNode;
if(this.label!="undefined"){
this.domNode.appendChild(document.createTextNode(this.label));
}
},onMouseOver:function(e){
dojo.html.addClass(this.domNode,"dojoButtonHover");
dojo.html.removeClass(this.domNode,"dojoButtonNoHover");
},onMouseOut:function(e){
dojo.html.removeClass(this.domNode,"dojoButtonHover");
dojo.html.addClass(this.domNode,"dojoButtonNoHover");
},onClick:function(e){
var _564=dojo.dom.getFirstChildElement(this.domNode);
if(_564){
if(_564.click){
_564.click();
}else{
if(_564.href){
location.href=_564.href;
}
}
}
}});
dojo.provide("dojo.widget.Button");
dojo.require("dojo.widget.Widget");
dojo.requireIf("html","dojo.widget.html.Button");
dojo.widget.tags.addParseTreeHandler("dojo:button");
dojo.widget.Button=function(){
dojo.widget.Widget.call(this);
this.widgetType="Button";
this.isContainer=true;
};
dojo.inherits(dojo.widget.Button,dojo.widget.Widget);

