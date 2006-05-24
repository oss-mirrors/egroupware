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
   </head>
   <body>
		 <div id="divMain">
			<div id="divAppboxHeader"><?=lang('Walk Records')?></div>
			<div id="divAppbox">
			   <form name='sel' action ="<?=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.do_loop_walk_events');?>" method="post">
				  <input type='hidden' id='sel' name ='sel' value='all'>
				  <input type='hidden' name ='selvalues' value='<?=$this->selval?>'>
				  <input type='hidden' name ='submitted' value='true'>
				  <input name='data_source' type='radio' value='unfiltered'  onClick = 'document.forms[0].temp.value=this.value' checked='checked'><?=lang('all records');?><br>
				  <input name='data_source' type='radio' value='filtered' onClick = 'document.forms[0].temp.value=this.value'><?=lang('filterd list');?><br>
				  <input name='data_source' type='radio' value='selected' onClick = 'document.forms[0].temp.value=this.value'><?=lang('Selection');?><br>
				  <input class="egwbutton"  type='submit' value='<?=lang('Submit');?>' >
			   </form>

			</div>
		 </div>
   </body>
</html>
