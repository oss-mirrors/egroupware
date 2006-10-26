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
		 function closethis()
		 {
			   opener.window.location.href=opener.window.location.href;
			   self.close()
		 }
	  </script>
	  <style type="text/css">
		 td
		 {
			   vertical-align:top;
		 }
	  </style>
   </head>

   <body>
	  <?php
		 if($_POST['GENXXXelement_type']=='lay_out')
		 {
			$option_eltype_layout='selected="selected"';	
		 }

	  ?>
	  <form name="popfrm" action="<?=$this->action?>" method="post" enctype="multipart/form-data">
		 <input type="hidden" name="submitted" value="true">
		 <input type="hidden" name="el_changes" value="false">
		 <div id="divMain">
			<div id="divAppboxHeader"><?=$this->website_title?></div>
			<div id="divAppbox">
			   <?php if($_POST['el_changes']=='false' && $_POST['submitted']=='true'):?>
			   <p/> 
			   <?=lang('Element is saved. You can close this window.');?><p/>
			   <input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="closethis();" />
			   <p/>

			   <?php else:?>
				<table>
				   <tr>
					  <td><?=lang('Element type')?></td>
					  <td>	
						 <select name="GENXXXelement_type" onchange="change_el_type();">
							<option value="table_field"><?=lang('input element');?></option>
							<option <?=$option_eltype_layout?> value="lay_out"><?=lang('lay-out element');?></option>
						 </select>
					  </td>
				   </tr>
				</table>
				<hr>
				<table style="border-spacing:20px;">
				<tr>
				   <td style="width:150px;"><strong><?=lang('Label')?></strong></td>
				   <td>	
					  <input name="GENXXXelement_label" value="<?=($_POST['GENXXXelement_label']?$_POST['GENXXXelement_label']:lang('New element %1',$new_element_counter))?>" />
				   </td>
				</tr>
				
				<?php if($_POST['GENXXXelement_type']=='lay_out'):?>
				<tr>
				   <td><strong><?=lang('lay-out plugin')?></strong><br/><?=lang('You can tune this configuration later')?></td>
				   <td>	
					  <select name="plugin_name" >
							<?=$this->lay_out_plug_opt_arr?>
					  </select>
				   </td>
				</tr>
				<?php else:?>
				<tr>
				   <td><strong><?=lang('Table field')?></strong><br/><?=lang('Choose the datasource for this input plugin. You can edit configuration later')?></td>
				   <td>	
					  <select name="GENXXXdata_source" >
						 <?=$this->fields_opt_arr?>
					  </select>
				   </td>
				</tr>
				<?php endif?>



			 </table>
 
			 <p/>	
			   <input class="egwbutton"  type="submit" value="<?=lang('save')?>"  />
			   <input class="egwbutton"  type="button" value="<?=lang('cancel')?>" onClick="self.close()" />
			   <?php endif?>
		 </div>
		 </div>
	  </form>
   </body>
</html>
