<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=$this->website_title?></title>
	  <meta http-equiv="content-type" content="text/html; charset=<?=$this->charset?>" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="description" content="eGroupware" />
	  <meta name="keywords" content="eGroupWare" />
	  <meta name="copyright" content="eGroupWare http://www.egroupware.org (c) 2005" />
	  <meta name="author" content="eGroupWare http://www.egroupware.org" />
	  <meta name="robots" content="none" />
	  <link rel="icon" href="<?=$this->img_icon?>" type="image/x-ico" />
	  <link rel="shortcut icon" href="<?=$this->img_shortcut?>" />
	  <link href="<?=$this->theme_css?>" type="text/css" rel="StyleSheet" />
	  <script type="text/javascript" src="<?=$GLOBALS['phpgw_info']['server']['webserver_url']?>/phpgwapi/js/tabs/./tabs.js"></script>

	  <?=$this->css?>
	  <style type="text/css">

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

		 body 
		 {
			   color: #333;
			   background-color: #eeeeee;
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
   </head>

   <body onload="initAll()">
	  <div id="divMain">
		 <div id="divAppboxHeader"><?=lang('Test Site Settings')?>: <?=$this->object_name?></div>
		 <div id="divAppbox">

			<table style="border-spacing:10px;">
			   <tr>
				  <td>
					 <strong><?=lang('Host Profile')?>:</strong>
				  </td>
				  <td>
					 <span style=""><?=$this->host_profile?></span>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <strong><?=lang('Database connection')?>:</strong>
				  </td>
				  <td>
					 <?php if($this->dbconnect):?>
					 <span style="color:green"><?=lang("Successful")?></span>
					 <?php else:?>
					 <span style="color:red"><?=lang("Failed")?></span>
					 <?php endif?>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <strong><?=lang('Upload path exist')?>:</strong>
				  </td>
				  <td>
					 <?php if($this->path_exist):?>
					 <span style="color:green"><?=lang("Yes")?></span>
					 <?php else:?>
					 <span style="color:red"><?=lang("No")?></span>
					 <?php endif?>
				  </td>
			   </tr>
			   <tr>
				  <td>
					 <strong><?=lang('Upload path writable')?>:</strong>
				  </td>
				  <td>
					 <?php if($this->path_writeable):?>
					 <span style="color:green"><?=lang("Yes")?></span>
					 <?php else:?>
					 <span style="color:red"><?=lang("No")?></span>
					 <?php endif?>
				  </td>
			   </tr>
			   <tr>
			   <td>
				  <strong><?=lang('Upload path write test')?>:</strong>
			   </td>
			   <td>
				  <?php if($this->path_writeable):?>
				  <span style="color:green"><?=lang("Successful")?></span>
				  <?php else:?>
				  <span style="color:red"><?=lang("Failed")?></span>
				  <?php endif?>
			   </td>
			</tr>
			<tr>
			   <td>
				  <strong><?=lang('Upload URL location correct')?>:</strong>
			   </td>
			   <td>
				  <?php if($this->url_correct):?>
				  <span style="color:green"><?=lang("Yes")?></span>
				  <?php else:?>
				  <span style="color:red"><?=lang("No")?></span>
				  <?php endif?>
			   </td>
			</tr>
		 </table>
	  </div>
	  <br/>
		 <div align="center" style="<?=$this->buttons_visibility?>">
			<input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="self.close()" />
		 </div>
	  </div>
   </form>
   <?=$this->jsscript?>
</body>
</html>

