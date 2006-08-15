<html style="width: 580px; height: 440px;">
   <head>
	  <title>JiNN File Manager</title>

	  <script type="text/javascript" src="js/popup.js"></script>

	  <script language="JavaScript" type="text/JavaScript">
		 var preview_window = null;

		 function Init() {

			   __dlg_init();
			   var doc = MM_findObj("f_url");

			   if(doc != null) {
					 doc.focus();
			   }

			   var field = opener.document.getElementById('CURRENT_FIELD').value;

			   document.getElementById("f_width").value="<?php echo($this->config[Max_image_width]); ?>";
			   document.getElementById("f_height").value="<?php echo($this->config[Max_image_height]); ?>";

			   document.getElementById("f_type").readOnly=true;

			   <?php if($this->config['Allow_other_images_sizes']=="False") : ?>
			   document.getElementById("f_width").readOnly=true;
			   document.getElementById("f_height").readOnly=true;
			   <?php endif ?>

			   <?php if($this->config['Generate_thumbnail']=='True') : ?>
			   document.getElementById("thumb").value="true";
			   document.getElementById("thumbwidth").value="<?php echo($this->config['Max_thumbnail_width']); ?>";
			   document.getElementById("thumbheight").value="<?php echo($this->config['Max_thumbnail_height']); ?>";
			   <?php endif ?>
		 };

		 function onOK() {
			   var required = {
					 "f_url": "You must enter the URL"
			   };
			   for (var i in required) {
					 var el = MM_findObj(i);
					 if (!el.value) {
						   alert(required[i]);
						   el.focus();
						   return false;
					 }
			   }

			   var path = document.form1.f_url.value;
			   var filetype = document.form1.f_type.value;
			   __dlg_close(path, filetype);
			   return false;
		 };

		 function onCancel() {
			   if (preview_window) {
					 preview_window.close();
			   }
			   __dlg_close(null, null);
			   return false;
		 };
		 <!--
		 function pviiClassNew(obj, new_style) 
		 { 
			   //v2.6 by PVII
			   obj.className=new_style;
		 }
		 function goUpDir() 
		 {
			   var selection = document.forms[0].dirPath;
			   var dir = selection.options[selection.selectedIndex].value;
			   if(dir != '/')
			   {
					 imgManager.goUp();	
					 changeLoadingStatus('load');
			   }

		 }

		 function updateDir(selection) 
		 {
			   var newDir = selection.options[selection.selectedIndex].value;
			   imgManager.changeDir(newDir);
			   changeLoadingStatus('load');
		 }

		 function process_new_folder(param)
		 {
			   var selection = document.forms[0].dirPath;
			   var dir = selection.options[selection.selectedIndex].value;
			   var folder = param['f_foldername'];
			   if (folder && folder != '') {
					 imgManager.newFolder(dir,folder); 
			   }
		 }

		 function newFolder() 
		 {
			   childWindow=open("newFolder.html","newfolder","height=150,width=300,resizable=yes");
			   if (childWindow.opener == null)	childWindow.opener = self;
		 }

		 function toggleConstrains(constrains) 
		 {
			   if(constrains.checked) 
			   {
					 document.locked_img.src = "img/locked.gif";	
					 checkConstrains('width') 
			   }
			   else
			   {
					 document.locked_img.src = "img/unlocked.gif";	
			   }
		 }

		 function checkConstrains(changed) 
		 {
			}

			function MM_findObj(n, d) { //v4.01
			   var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
					 d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
				  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
				  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
				  if(!x && d.getElementById) x=d.getElementById(n); return x;
			}

			function P7_Snap() { //v2.62 by PVII
			   var x,y,ox,bx,oy,p,tx,a,b,k,d,da,e,el,args=P7_Snap.arguments;a=parseInt(a);
			   for (k=0; k<(args.length-3); k+=4)
			   if ((g=MM_findObj(args[k]))!=null) {
					 el=eval(MM_findObj(args[k+1]));
					 a=parseInt(args[k+2]);b=parseInt(args[k+3]);
					 x=0;y=0;ox=0;oy=0;p="";tx=1;da="document.all['"+args[k]+"']";
					 if(document.getElementById) {
						   d="document.getElementsByName('"+args[k]+"')[0]";
						   if(!eval(d)) {d="document.getElementById('"+args[k]+"')";if(!eval(d)) {d=da;}}
					 }else if(document.all) {d=da;} 
					 if (document.all || document.getElementById) {
						   while (tx==1) {p+=".offsetParent";
							  if(eval(d+p)) {x+=parseInt(eval(d+p+".offsetLeft"));y+=parseInt(eval(d+p+".offsetTop"));
						}else{tx=0;}}
						ox=parseInt(g.offsetLeft);oy=parseInt(g.offsetTop);var tw=x+ox+y+oy;
						if(tw==0 || (navigator.appVersion.indexOf("MSIE 4")>-1 && navigator.appVersion.indexOf("Mac")>-1)) {
							  ox=0;oy=0;if(g.style.left){x=parseInt(g.style.left);y=parseInt(g.style.top);
							  }else{var w1=parseInt(el.style.width);bx=(a<0)?-5-w1:-10;
							  a=(Math.abs(a)<1000)?0:a;b=(Math.abs(b)<1000)?0:b;
							  x=document.body.scrollLeft + event.clientX + bx;
							  y=document.body.scrollTop + event.clientY;}}
					 }else if (document.layers) {x=g.x;y=g.y;var q0=document.layers,dd="";
					 for(var s=0;s<q0.length;s++) {dd='document.'+q0[s].name;
						if(eval(dd+'.document.'+args[k])) {x+=eval(dd+'.left');y+=eval(dd+'.top');break;}}}
				  if(el) {e=(document.layers)?el:el.style;
					 var xx=parseInt(x+ox+a),yy=parseInt(y+oy+b);
					 if(navigator.appName=="Netscape" && parseInt(navigator.appVersion)>4){xx+="px";yy+="px";}
					 if(navigator.appVersion.indexOf("MSIE 5")>-1 && navigator.appVersion.indexOf("Mac")>-1){
						   xx+=parseInt(document.body.leftMargin);yy+=parseInt(document.body.topMargin);
						   xx+="px";yy+="px";}e.left=xx;e.top=yy;}}
			}

			function MM_showHideLayers() { //v6.0
			   var i,p,v,obj,args=MM_showHideLayers.arguments;
			   for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
				  if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
				  obj.visibility=v; }
		 }

		 function changeLoadingStatus(state) 
		 {
			   var statusText = null;
			   if(state == 'load') {
					 statusText = 'Loading Images';	
			   }
			   else if(state == 'upload') {
					 statusText = 'Uploading Files';
			   }
			   if(statusText != null) {
					 var obj = MM_findObj('loadingStatus');
					 if (obj != null && obj.innerHTML != null)
					 obj.innerHTML = statusText;
					 MM_showHideLayers('loading','','show')		
			   }
		 }

		 function refresh()
		 {
			   var selection = document.forms[0].dirPath;
			   updateDir(selection);
		 }
	  </script>

	  <style type="text/css">
		 html, body 
		 {
			   background: ButtonFace;
			   color: ButtonText;
			   font: 11px Tahoma,Verdana,sans-serif;
			   margin: 0px;
			   padding: 0px;
		 }
		 body { padding: 5px; }
		 table {
			   font: 11px Tahoma,Verdana,sans-serif;
		 }
		 form p {
			   margin-top: 5px;
			   margin-bottom: 5px;
		 }
		 .fl { width: 9em; float: left; padding: 2px 5px; text-align: right; }
		 .fr { width: 6em; float: left; padding: 2px 5px; text-align: right; }
		 fieldset { padding: 0px 10px 5px 5px; }
		 select, input, button { font: 11px Tahoma,Verdana,sans-serif; }
		 button { width: 70px; }
		 .space { padding: 2px; }

		 .title 
		 { 
			   background: #ddf; 
			   color: #000; 
			   font-weight: bold; 
			   font-size: 120%; 
			   padding: 3px 10px; 
			   margin-bottom: 10px;
			   border-bottom: 1px solid black; 
			   letter-spacing: 2px;
		 }
		 form 
		 { 
			   padding: 0px; 
			   margin: 0px; 
		 }
		 .buttonHover {
			   border: 1px solid;
			   border-color: ButtonHighlight ButtonShadow ButtonShadow ButtonHighlight;
			   cursor: hand;
		 }
		 .buttonOut
		 {
			   border: 1px solid ButtonFace;
		 }

		 .separator {
			   position: relative;
			   margin: 3px;
			   border-left: 1px solid ButtonShadow;
			   border-right: 1px solid ButtonHighlight;
			   width: 0px;
			   height: 16px;
			   padding: 0px;
		 }
		 .statusLayer
		 {
			   background:#FFFFFF;
			   border: 1px solid #CCCCCC;
		 }
		 .statusText {
			   font-family: Verdana, Arial, Helvetica, sans-serif;
			   font-size: 15px;
			   font-weight: bold;
			   color: #6699CC;
			   text-decoration: none;
		 }
	  </style>
   </head>
   <body onload="Init(); P7_Snap('dirPath','loading',120,70);">
	  <div class="title">managing <font size="+1"><?php echo($this->config[Filetype]); ?></font> files</div>
	  <form action="iframe.dircontent.php?field=<?php echo($_GET[field]); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>" name="form1" method="post" target="imgManager" enctype="multipart/form-data">
		 <div id="loading" style="position:absolute; left:200px; top:130px; width:184px; height:48px; z-index:1" class="statusLayer">
			<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
			   <tr>
				  <td><div align="center"><span id="loadingStatus" class="statusText">Loading Images</span><img src="img/dots.gif" width="22" height="12"></div></td>
			   </tr>
			</table>
		 </div>
		 <table width="100%" border="0" align="center" cellspacing="2" cellpadding="2">
			<tr>
			   <td align="center">	  <fieldset>
					 <legend><?=lang('Files')?></legend>
					 <table width="99%" align="center" border="0" cellspacing="2" cellpadding="2">
						<tr>
						   <td><table border="0" cellspacing="1" cellpadding="3">
								 <tr> 
									<td><?=lang('Directory')?></td>
									<td>
									   <select name="dirPath" id="dirPath" style="width:30em" onChange="updateDir(this)">
										  <option value="/">/</option>

										  <?php
											 if($this->no_dir == false) 
											 {
												dirs2($this->BASE_DIR,'');	
											 }
										  ?>

									   </select>
									</td>
									<td class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
									   <a href="#" onClick="javascript:goUpDir();"><img src="img/btnFolderUp.gif" width="15" height="15" border="0" alt="Up"></a></td>
									<? if ($tplsav2->SAFE_MODE == false) { ?>
									<td><div class="separator"></div></td>
									<td class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
									   <a href="#" onClick="javascript:newFolder();"><img src="img/btnFolderNew.gif" width="15" height="15" border="0" alt="New Folder"></a></td>
									<? } ?>
								 </tr>
						   </table></td>
						</tr>
						<tr>
						   <td align="center" bgcolor="white"><div name="manager" class="manager">
								 <iframe src="iframe.dircontent.php?field=<?php echo($_GET[field]); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>" name="imgManager" id="imgManager" width="520" height="150" marginwidth="0" marginheight="0" align="top" scrolling="auto" frameborder="0" hspace="0" vspace="0" background="white"></iframe>
							  </div>
						   </td>
						</tr>
					 </table>
			   </fieldset></td>
			</tr>
			<tr>
			   <td>
				  <table style="width:100%" border="0" align="center" cellpadding="2" cellspacing="2">
					 <tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><div align="right"> Max Width </div></td>
						<td><input name="width" id="f_width" type="text" size="5" style="width:4em" onChange="javascript:checkConstrains('width');"></td>
						<td rowspan="2"><img src="img/locked.gif" name="locked_img" width="25" height="32" id="locked_img" alt="Locked"></td>
					 </tr>
					 <tr> 
						<td><div align="right">Upload </div></td>
						<td><input type="file" name="upload" id="upload"> 
						   <input type="hidden" name="thumb" id="thumb" value="">
						   <input type="hidden" name="thumbwidth" id="thumbwidth" value="">
						   <input type="hidden" name="thumbheight" id="thumbheight" value="">
						   <input type="submit" style="width:5em" value="Upload" onClick="javascript:changeLoadingStatus('upload');" />
						</td>
						<td><div align="right">Max Height </div></td>
						<td><input name="height" id="f_height" type="text" size="5" style="width:4em" onChange="javascript:checkConstrains('height');"></td>
					 </tr>
					 <tr> 
						<td nowrap><div align="right">File </div></td>
						<td colspan="4"><input name="url" id="f_url" type="text" style="width:100%" size="30"></td>
					 </tr>
					 <tr> 
						<td nowrap><div align="right">Type </div></td>
						<td colspan="4"><input name="type" id="f_type" type="text" size="10"></td>
					 </tr>
				  </table>
			   </td>
			</tr>
			<tr>
			   <td><div style="text-align: right;"> 
					 <hr />
					 <button type="button" name="ok" onclick="return refresh();">Refresh</button>
					 <button type="button" name="ok" onclick="return onOK();">OK</button>
					 <button type="button" name="cancel" onclick="return onCancel();">Cancel</button>
			   </div></td>
			</tr>
		 </table>
	  </form>
   </body>
</html>
