<?php

   /**
   * plg_conf_widget general class for creating configuration forms for JINN Plugins 
    * 
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
	* @author Rob van Kraanen <rob-AT-lingewoud-DOT-nl> 
    * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
   class plg_conf_widget
   {
	  var $public_functions = Array(
		 'display_plugin_widget' => True,
		 );
		 var $tplsav2, $set_val;
	  /**
	  * uiadmin 
	  * 
	  * @access public
	  * @return void
	  */
	  function plg_conf_widget()
	  {
		 $this->bo = CreateObject('jinn.boadmin'); //FIXME not specifically ADMIN
		 $this->set_val = '';
	  }

	  /**
	   * display_plugin_widget 
	   * 
	   * @param string $widget  widget type
	   * @param mixed $savant a savant object
	   * @param mixed $cval $config value
	   * @param mixed $fld_plug_conf_arr $complete array of config settings stored in register.php
	   * @param mixed $multi is true a special multi widget is created
	   * @access public
	   * @return string html with the widget layout 
	   */
	  function display_plugin_widget($widget, $savant,$cval,$fld_plug_conf_arr, $multi=false)
	  {
		 $this->tplsav2 = $savant;
		 if(!$cval['fname'] and $cval['fname'] =='')
		 {
			$cval['fname']='PLGXXX'.$cval['name'];
		 }
		 /* if configuration is already set use these values */
		 if(!$multi)
		 {
			unset($this->set_val);
		 }

		 if($this->set_val == '' and !$this->set_val)
		 {
			if(is_array($fld_plug_conf_arr))
			{
			   if ($fld_plug_conf_arr['conf'][$cval['name']])
			   {
				  $this->set_val=$fld_plug_conf_arr['conf'][$cval['name']];
			   }
			   else
			   {
				  $this->set_val=$cval['def_val'];
			   }
			}
		 }
	    $this->tplsav2->assign('set_val',$this->set_val);
		$this->tplsav2->assign('cval',$cval);
		eval('$widget = $this->plg_conf_widget_'.$widget.'();');
		return $widget;
	  }
	  function plg_conf_widget_text()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_text.tpl.php");
	  }
	  function plg_conf_widget_area()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_area.tpl.php");
	  }

	  function plg_conf_widget_checkbox()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_checkbox.tpl.php");
	  }

	  function plg_conf_widget_multi()
	  {
		 $cval = $this->tplsav2->cval;
		 $set_val = $this->set_val;
		 if(is_array($set_val))
		 {
			$nr=1;
			foreach($set_val as $mname => $val)
			{
			   unset($items);
			   foreach($cval['items'] as $item)
			   {
				  if($nr < 10)
				  {
					 $nrstr = "00$nr";
				  }
				  if($nr >10 and $nr <100)
				  {
					 $nrstr = "0$nr";
				  }
				  if($nr > 100)
				  {
					 $nrstr = "$nr";
				  }
				  $item['fname'] = "MLT$nrstr{$cval['name']}_SEP_{$item['name']}";
				  $this->tplsav2->assign('set_val',$val[$item['name']]);
				  $this->set_val = $val[$item['name']];
				  $this->tplsav2->assign('multi_val',$this->tplsav2->cval);

				  $this->tplsav2->assign('cval',$item);
				  $items[] = $this->display_plugin_widget($item['type'], $this->tplsav2,$item,'',true);
			   }
			   //echo $nr;
			   $this->tplsav2->assign('nr',$nr);
			   $this->tplsav2->assign('multi_items',$items);
			   $slots[]=$this->tplsav2->fetch("plg_conf_widget_multi_slot.tpl.php");
			   $nr++;
			}
		 }
		 else
		 {
			unset($items);
			$set_val = $this->set_val;
			foreach($cval['items'] as $item)
			{
			   // FIXME no till 9  MLT001
			   $item['fname'] = "MLT001{$cval['name']}_SEP_{$item['name']}";

			   $this->tplsav2->assign('set_val',$set_val[$item['name']]);
			   $this->set_val = $set_val[$item['name']];
			   $this->tplsav2->assign('multi_val',$this->tplsav2->cval);

			   $this->tplsav2->assign('cval',$item);
			   $items[] = $this->display_plugin_widget($item['type'], $this->tplsav2,$item,'');
			}
			$this->tplsav2->assign('nr',1);
			$this->tplsav2->assign('multi_items',$items);
			$slots[]=$this->tplsav2->fetch("plg_conf_widget_multi_slot.tpl.php");
		 }
		 $this->tplsav2->assign('slots',$slots);

		 return $this->tplsav2->fetch("plg_conf_widget_multi.tpl.php");
	  }


	  function plg_conf_widget_radio()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_radio.tpl.php");
	  }

	  function plg_conf_widget_select()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_select.tpl.php");
	  }

	  function plg_conf_widget_sitefile()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_sitefile.tpl.php");
	  }

	  function plg_conf_widget_spacer()
	  {
		 return $this->tplsav2->fetch("plg_conf_widget_spacer.tpl.php");
	  }
	  function plg_conf_widget_select_form_elements()
	  {
		 $arr= array();
		 $arr = $this->bo->so->mk_field_conf_arr_for_obj($_GET['object_id']);
		 $vals = split(',',$this->set_val['value']);
		 $this->tplsav2->assign('set_val',$vals);
		 $this->tplsav2->assign('fields',$arr);
		 return $this->tplsav2->fetch("plg_conf_widget_select_form_elements.tpl.php");
	  }
	  
   }
?>


