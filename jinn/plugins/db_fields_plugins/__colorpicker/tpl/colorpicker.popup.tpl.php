<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title>ColorPicker</title>
	  <meta http-equiv="content-type" content="text/html; charset=<?=$this->charset?>" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="description" content="eGroupware" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2005" />
	  <meta name="author" content="eGroupWare http://www.egroupware.org" />
	  <meta name="robots" content="none" />
	  <link rel="icon" href="<?=$this->img_icon?>" type="image/x-ico" />
	  <link rel="shortcut icon" href="<?=$this->img_shortcut?>" />
	  <script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/phpgwapi/js/tabs/./tabs.js"></script>
	  <style type="text/css" media="screen">

		 body
		 {
			   background-color:white;	
		 }
		 body {
			   padding: 5px;
			   overflow: hidden;
			   font-size: 12px;
			   font-family: tahoma, verdana, arial, sans-serif;
			   background-color: #468;
			   margin: 10px;
			   overflow: hidden;
		 }

		 table{
			   cursor:default;
			   font-size: 12px;
			   border: 0px;
		 }

		 #Free, #Image, #Palette{
			   position: absolute;
			   top: 20px;
			   left: 0px;
			   width: 565px;
			   height: 100%;
			   margin:10px;
			   visibility: hidden;
			   border-top: 0px;
			   height: 390px;
		 }
		 .tab{
			   background-color: #ccc;
			   border-right: 1px #000 solid;
			   border-left: 1px #eee solid;
			   border-top: 1px #eee solid;
			   border-bottom: 1px #000 solid;
			   text-decoration: none;
			   padding: 5px;
			   color: #000;
		 }
		 .dialog 
		 {
			   cursor: default;
			   padding: 5px;
			   /*position:relative;*/
			   height: 300px;
		 }

		 .grey{
			   cursor: default;
			   padding: 2px;
			   FONT-WEIGHT: normal;
			   FONT-SIZE: 11px;
			   BACKGROUND: #ccc;
			   COLOR: #000000;
			   FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica, Sans;
			   margin: 0px;
		 }

		 #mainselector{
			   position:absolute;bottom:15px; left: 20px; border:1px #888 groove;
		 }

		 #curColor{
			   position:relative;
			   background-color:#ffffff;
			   width:40px;
			   height:40px;
			   padding-left: 18px;
			   padding-top: 18px;
		 }

		 #mouseover{
			   position: absolute;width:20px; height: 20px; background-color: #ffffff;border: 1px #888 groove;
		 }
		 #gradient{
			   position:absolute;
			   right: 40px;
			   bottom: 35px;
			   width: 200px; height: 204px; border:solid 1px #888; font-size:1px; cursor: wait; background-color: #888;
		 }

		 #slideplot{
			   position:absolute;
			   right: 10px;
			   bottom: 35px;
			   width: 16px; height: 208px; border:solid 1px #888 groove; font-size: 1px; cursor: wait;
		 }
		 #refColors{
			   position:absolute;bottom:15px; left: 100px;width: 400px; border:0px #888 groove;
		 }
		 #OkBtn
		 {
			   position:absolute;bottom:15px; left: 120px;width: 80px; border:0px;
		 }		 

		 div.activetab
		 { 
			   display:block; 
			   background-color:#EEEEEE;
			   padding:10px;

		 }
		 div.inactivetab
		 { 
			   display:none; 
		 }

		 body {
			   color: #333;
			   background-color: #ffffff;
			   /*			   padding: 0 5%;*/
			   margin:0;
			   font-family: arial, geneva, lucida, sans-serif;
			   font-size:83.333%;
		 }

		 a:link, a:visited {
			   text-decoration:none;
			   font-weight:bold;
			   color: #FF4000;
		 }

		 a:hover {
			   color:#002c99;
		 }

		 #topnav {
			   margin:0;
			   padding: 0 0 0 12px;
		 }

		 #topnav ul 
		 {
			   list-style: none;
			   margin: 0;
			   padding: 0;
			   border: none;
		 } 

		 #topnav li,
		 li.inactivetab{
			   display: block;
			   margin: 0;
			   padding: 0;
			   float:left;
			   width:auto;
		 }

		 #topnav A {
			   color:#444;
			   display:block;
			   width:auto;
			   text-decoration:none;
			   background: #BBBBBB;
			   margin:0;
			   padding: 2px 10px;
			   border-left: 1px solid #fff;
			   border-top: 1px solid #fff;
			   border-right: 1px solid #aaa;
		 }

		 #topnav A:hover, 
		 #topnav A:active 
		 {
			   background: #EEEEEE;
		 }

		 #topnav A.activetab:visited,#topnav A.activetab:link,#topnav A.here:link, #topnav A.here:visited {
			   position:relative;
			   z-index:102;
			   background: #EEEEEE;
			   font-weight:bold;
		 }

		 #subnav
		 {
			   position:relative;
			   top:-1px;
			   z-index:101;
			   margin:0;
			   /*padding: 0px 0 3px 0;*/
			   background: #EEEEEE;
			   border-top:1px solid #fff;
			   border-bottom:1px solid #aaa;
		 }

		 #subnav BR, #topnav BR 
		 {
			   clear:both;
		 } 

		 td
		 {
			   vertical-align:top;
		 }
	  </style>
	  <script type="text/javascript" src="colorpicker.js"></script>
	  <?php
		 if($this->config[activetabs][primpalet]) $acttab=1;
		 elseif($this->config[activetabs][extrapalets]) $acttab=2;
		 elseif($this->config[activetabs][free]) $acttab=3;
		 elseif($this->config[activetabs][fromimg]) $acttab=4;
	  ?>
   <script type="text/javascript">
		 var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

		 // init all tabs
		 function initAll()
		 {
			   tab.init();
			   /*			   if(document.popfrm.currenttab.value)
			   {
					 tab.display(document.popfrm.currenttab.value);
			   }
			   */
			   tab.display(<?=($_POST[acttab]?$_POST[acttab]:$acttab)?>);
		 } 

		 // store current tab so we can open it directly
		 function setCurrent(tab)
		 {
			   //document.popfrm.currenttab.value=tab;
			}

			function init(){
			   if(typeof document.onselectstart != 'undefined') 
			   {
					 document.onselectstart = function () 
					 { 
						   return false; 
					 }
			   }
			   <?php if($this->curColor):?>
			   document.getElementById('curColor').style.backgroundColor="<?=$this->curColor?>";
			   document.getElementById('form_curColor').value="<?=$this->curColor?>";
			   document.getElementById('hex').value="<?=$this->curColor?>";
			   <?php endif?>

			   setTimeout("plot()",200); // Timeout to allow rendering of the rest of the page first!
			   setTimeout("slideplot()",250);
			   document.body.scroll="no";
		 }
	  </script>
   </head>
   <body onload="initAll();init();">
	  
	  <!--
	  Array
	  (
		 [activetabs] => Array
		 (
			[fromimg] => fromimg
			[free] => free
			[primpalet] => primpalet
			[extrapalets] => extrapalets
		 )

		 [primpalet] => #FF0000,00FF00,#0000FF,#CCC,CCC
		 [palets] => 
		 [defaultimage] => 12429-1.jpg
	  )

	  -->
	  <div id="divMain">
		 <div id="divAppbox">

			<br/>

			<div id="topnav">
			   <ul>
				  <?php if($this->config[activetabs][primpalet]):?>
				  <li><a href="javascript:void(0)" id="tab1" class="activetab" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); setCurrent(1);return(false);"><?=lang('Primary Palet')?></a></li>
				  <?php endif?>

				  <?php if($this->config[activetabs][extrapalets]):?>
				  <li><a href="javascript:void(0)" id="tab2" class="activetab" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); setCurrent(2); return(false);"><?=lang('Extra Palets')?></a></li>
				  <?php endif?>
				  
				  <?php if($this->config[activetabs][free]):?>
				  <li><a href="javascript:void(0)" id="tab3" class="activetab" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); setCurrent(3); return(false);"><?=lang('Free Picker')?></a></li>
				  <?php endif?>


				  <?php if($this->config[activetabs][fromimg]):?>
				  <li><a href="javascript:void(0)" id="tab4" class="activetab" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); setCurrent(4); return(false);"><?=lang('Image Picker')?></a></li>
				  <?php endif?>
			   </ul>
			   <br />
			</div>
			<div id="subnav">


			   <div id="tabcontent1" class="inactivetab" >

				  <?php if(is_array($this->prim_palet_arr)):?>

				  <?php foreach($this->prim_palet_arr as $pcolor):?>
				  <div style="border:1px groove #000; z-index:99999;color: #888;float:left;margin:5px;background-color: <?=$pcolor?>; width: 30px; height:30px;" onmousedown="sel2('<?=$pcolor?>');" onmouseover="mo('<?=$pcolor?>');"></div>	
				  <?php endforeach?>
				  <?php else:?>
				  <?=lang('No palet defined')?>
				  <?php endif?>
				  <div style="clear:both"></div>

				  
			   </div>


			   <div id="tabcontent2" class="inactivetab" >
				  <div class="dialog" ><br />
					 <form method="post" action="<?=$this->self?>" id="form_palet">
						<select name="pallet_id" onchange="submitter('form_palet');">
						   <option value="0" style="border-bottom:1px solid #000; color: #888;">Please select:</option> 
						   <? //paletMenu(); ?>
						</select>
						<input type="hidden" name="mode" value="1" />
						<input type="hidden" name="acttab" value="2" />
						<input type="hidden" id="ref1" name="ref" value="<?=$this->fieldid ?>" />
						<input type="hidden" name="form_curColor" id="form_curColor" value="<?=$this->curColor?>" />
					 </form>
					 <? //paletTable(); ?>
				  </div>
			   </div>

			   <div id="tabcontent3" class="inactivetab" >
				  <div class="dialog">
					 <table cellspacing="4" cellpadding="4" border="0" >
						<tr valign="top"><td colspan="2">
							  <table cellspacing="0" cellpadding="0" border="0">
								 <tr>
									<td>Hue: </td>
									<td><input style="width:40px;" type="text" tabindex="1" class="textfield" id="H" value="0" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
									<td>&nbsp;Red: </td>
									<td><input style="width:40px;"  type="text" tabindex="4" class="textfield" id="R" value="255" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
									<td>&nbsp;Color Code: </td>
									<td><input type="text" style="width:80px;" maxlength="7" id="hex" value="#ff0000" class="textfield" onkeyup="calcHex();" onchange="calcHex();" onblur="calcHex();" /></td>
								 </tr>

								 <tr>
									<td>Sat:&nbsp;&nbsp;</td>
									<td><input type="text" style="width:40px;"  tabindex="2" class="textfield" id="S" value="100" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
									<td>&nbsp;Green:&nbsp;</td>
									<td><input type="text" style="width:40px;"  tabindex="5" class="textfield" id="G" value="0" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
									<td>&nbsp;</td><td>&nbsp;</td>
								 </tr>

								 <tr>
									<td>Lum:&nbsp;&nbsp;</td>
									<td><input type="text" style="width:40px;"  tabindex="3" class="textfield" id="L" value="100" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
									<td>&nbsp;Blue: </td>
									<td><input type="text" style="width:40px;"  tabindex="6" class="textfield" id="B" value="0" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
									<td>&nbsp;</td><td>&nbsp;</td>
								 </tr>
							  </table>
						</td></tr>
					 </table>
					 <div id="gradient"></div>
					 <div id="slideplot"></div>
				  </div>			
			   </div>

			   <div id="tabcontent4" class="inactivetab" >
				  <div class="dialog" style="height:100%;"><br />
					 <form method="post" enctype="multipart/form-data" action="<?= $this->self; ?>">
						<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
						<input type="hidden" name="mode" value="3" />
						<input type="hidden" name="acttab" value="4" />
						<input type="file" name="userfile" title="File" />
						<input type="submit" name="" value="Upload" />
					 </form>
					 <form method="get" action="image.php" target="console">
						<input name="imgfile" type="hidden" value="<?=$this->imagefile?>" />
						<input name="imgimg" id="generatedImage" type="image" src="<?=$this->imagefile?>" ismap="ismap" style="cursor:crosshair; border:1px #888 inset" /> 
					 </form>
					 <iframe name="console" src="" style="visibility:hidden;width:1px;height:1px;"></iframe>
				  </div>

			   </div>
			</div>
		 </div>

		 <!--	  <a class="tab" href="#" onclick="switchMode('1');" id="tab1">Color Tables</a> <a class="tab" href="#" onclick="switchMode('2');" id="tab2">Free</a> <a href="#" class="tab" onclick="switchMode('3');" id="tab3">From image</a>
		 -->
		 <!-- -->
		 <div id="Free">

		 </div>

		 <!-- -->

		 <div id="Palette">
		 </div>

		 <!-- -->

		 <div id="Image">
		 </div>

		 <div id="OkBtn">
			<input type="button" value="Ok" onclick="doQuit();" />
		 </div>

		 <!--<div id="refColors">
			<? //print $refColors; ?>
		 </div>-->
		 <div id="mainselector">
			<div id="curColor">
			   <div id="mouseover">
			   </div>
			</div>
		 </div>
		 <input type="hidden" id="fieldid" value="<?=$this->fieldid?>">
	  </body>
   </html>
