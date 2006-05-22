<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=lang('Walk Records')?></title>

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
	  <script>
		 function closeme()
		 {
			   opener.window.location.href = opener.window.location.href;
			   self.close();
			}
	  </script>
   </head>
   <body>

	  <div id="divMain">
		 <div id="divAppboxHeader"><?=lang('Walk Records')?></div>
		 <div id="divAppbox">
			<?=$this->amount?> records have been modified<br>
			You can close this window now.<br>			 
			<input type="button" onclick="closeme();" value="<?=lang('Close');?>">
		 </div>
	  </div>
   </body>
</html>
