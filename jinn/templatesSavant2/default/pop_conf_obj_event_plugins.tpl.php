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
	  <link href="<?=$this->theme_css?>" type="text/css" rel="StyleSheet" />

	  <script type="text/javascript" >
		 // this set the plugchanges field so the class know it has changed
		 function change_el_type()
		 {
			   document.popfrm.el_changes.value="true";
			   document.popfrm.submit();
		 }
	  </script>
	  <?=$this->css?>
	  <style type="text/css">
		 td
		 {
			   vertical-align:top;
			}
	  </style>
   </head>

   <body>
	  <?php
		 if($_POST['element_type']=='lay_out')
		 {
			$option_eltype_layout='selected="selected"';	
		 }

	  ?>
	  <form name="popfrm" action="<?=$this->action?>" method="post" enctype="multipart/form-data">
		 <input type="hidden" name="submitted" value="true">
		 <input type="hidden" name="el_changes" value="true">
		 <div id="divMain">
			<div id="divAppboxHeader"><?=$this->website_title?></div>
			<div id="divAppbox">
			  
			 <p/>	
			   <input class="egwbutton"  type="submit" value="<?=lang('save')?>"  />
			   <input class="egwbutton"  type="button" value="<?=lang('cancel')?>" onClick="self.close()" />
		 </div>
		 </div>
	  </form>
   </body>
</html>

