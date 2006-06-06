<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="<?=$this->lang?>" xmlns="http://www.w3.org/1999/xhtml">
   <head>
	  <title><?=lang('Event Plugin Configuration')?></title>
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
   <body <?=$this->close?>>

	  <form method="post" name="frm" action=<?=$this->action?>>
		 <div id="divMain">
			<div id="divAppboxHeader"><?=lang('Event Plugin Configuration')?></div>
			<div id="divAppbox">

			   <?php  if($_GET[edit]==''):?>
			   <?php //if(!$xxxx):?>

			   <?php if(count($this->stored_events_arr)>0):?>
			   <h2><?=lang('Stored Events')?></h2>
			   <table >
				  <thead>
					 <tr>
						<th>
						   <?=lang('delete')?>
						</th>
						<th>
						   <?=lang('description')?>
						</th>
					 </tr>
				  </thead>
				  <tbody>
					 <?php foreach($this->stored_events_arr as $stored_event):?>
					 <tr>
						<td>
						   <input type="checkbox" name="delete_<?=$stored_event['config_id']?>" value="true"/>
						</td>
						<td>
						   <a href="<?=$stored_event['edit_url']?>"><?=$stored_event['config_description']?></a>
						</td>
					 </tr>
					 <?php endforeach?>
				  </tbody>
			   </table>
			   <?php endif?>

			   <h2><?=lang('add a new configuration:')?></h2>
			   <table>
				  <tr><td><?=lang('select an event')?></td>
					 <td><select name="event" onChange="<?=$this->option_selected?>">
						   <?=$this->event_options?>
						</select>
				  </td></tr>
				  <tr><td><?=lang('select a plugin')?></td>
					 <td><select name="plugin" onChange="<?=$this->option_selected?>">
						   <?=$this->plugin_options?>
						</select>
				  </td></tr>
			   </table>
			   <?php endif?>

			   <?php if($_POST['plugin'] || $_GET['edit']!=''):?>

			   <h2><?=lang('Plugin Configuration')?> - <?=$this->plug_name?></h2>
			   <?php if(!$_POST['plugin']):?>
			   <input type="hidden" name="plugin" value="<?=$this->plugin?>" />
			   <?php endif?>
			   <?php if(!$_POST['event']):?>
			   <input type="hidden" name="event" value="<?=$this->event?>" />
			   <?php endif?>

			   <table style="border-spacing:15px;">
				  <tr>
					 <td style="vertical-align:top;width:170px;">
						<strong><?=lang('Event label');?></strong>
						<br/>
						<?=lang('Name of this event setup which will appear in buttons, icons')?>
					 </td>
					 <td style="vertical-align:top;">
						<input type="text" name="eventlabel" value="<?=$this->complete_conf_arr['eventlabel']?>" />
					 </td>
				  </tr>
				  <tr style="">
					 <td style="vertical-align:top;width:170px;">
						<strong><?=lang('Icon');?></strong>
						<br/>
						<?=lang('Icon used by some event types.')?>
					 </td>
					 <td style="vertical-align:top;">
						<input type="file" name="iconupload" value="<?=$this->label?>" />
						<input type="hidden" name="iconfile" value="<?=$this->iconfile?>"/>
					 </td>
				  </tr>
				  <tr>
					 <td colspan="2" style="border-bottom:solid 1px #bbbbbb">
						&nbsp;
				  </td>
			   </tr>
			   <?php if(is_array($this->cfg_arr)):?>
			   <?php foreach($this->cfg_arr as $cfg):?>
				  <tr>
					 <td style="vertical-align:top;width:170px;">
						<strong><?=$cfg['label']?></strong>
						<br/>
						<?=$cfg['help']?>
					 </td>
					 <td style="vertical-align:top;">
						<?php if($cfg['type']=='radio'):?>
						<?php foreach($cfg['value'] as $radio):?>
						<input name="EPL<?=$cfg['name']?>" type="radio" value="<?=$radio?>" /><?=$radio?><br/>
						<?php endforeach?>
						<?php elseif($cfg['type']=='text'):?>
						<input name="EPL<?=$cfg['name']?>" type="text" value="<?=$cfg['value']?>">
						<?php elseif($cfg['type']=='area'):?>
						<textarea name="EPL<?=$cfg['name']?>" rows="5" cols="50"><?=$cfg['value']?></textarea>
						<?php elseif($cfg['type']=='select'):?>
						<select name="EPL<?=$cfg['name']?>">
						   <?php foreach($cfg['value'] as $option):?>
						   <option value="<?=$option?>"><?=$option?></option>
						   <?php endforeach?>
						</select>
					 </td>
					 <?php endif?>
					 </tr>
					 <?php endforeach?>
			   <?php endif?>
			   </table>
			   <?php endif?>
			   <br/>
			   <input class="egwbutton"  type="submit" name="submitted" value="<?=lang('submit')?>">
			   <input class="egwbutton"  type="button" value="<?=lang('close')?>" onClick="self.close()">
			   <input  class="egwbutton" type="button" value="<?=lang('back')?>" onclick="location.href='<?=$this->startlink?>'">
			   <br/>
			</div>
		 </div>
	  </form>
   </body>
</html>
