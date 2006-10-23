<?php
   $phpgw_flags = Array(
	  'currentapp'	=>	'jinn',
	  'noheader'	=>	True,
	  'nonavbar'	=>	True,
	  'noappheader'	=>	True,
	  'noappfooter'	=>	True,
	  'nofooter'	=>	True
   );

   $GLOBALS['phpgw_info']['flags'] = $phpgw_flags;
   $GLOBALS['egw_info']['flags'] = $phpgw_flags;
   require_once('../../../../header.inc.php');

   $config=unserialize(base64_decode($_GET['config2base64']));

   $tmpso = CreateObject('jinn.sojinn');

   $objects_arr=$tmpso->get_objects_by_name($config['objname'],$_GET['site_id']);
   $object_id=$objects_arr[0];

   $plug_root= EGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/__mediabrowser';
   $tplsav2 = CreateObject('phpgwapi.tplsavant2');
   $tplsav2->addPath('template',$plug_root.'/tpl');

   $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
   '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

   $tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
   $tplsav2->assign('website_title',lang('Media Browser'));
   $tplsav2->assign('theme_css',$theme_css);
   $tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
   $tplsav2->assign('lang',$GLOBALS['phpgw_info']['user']['preferences']['common']['lang']);

   $japie = CreateObject('jinn.japie',$object_id,'jinnmedia',false);
   $japie->baselink='jinn.uijinn.pluginwrapper&config2base64='.$_GET['config2base64'].'&site_id='.$_GET['site_id'].'&plugname=mediabrowser&plugfile=popup.mediabrowser.php&japie=true';

   $tplsav2->display('pop.mediabrowser.header.tpl.php');
   $japie->display();
   $tplsav2->display('pop.mediabrowser.footer.tpl.php');
?>
