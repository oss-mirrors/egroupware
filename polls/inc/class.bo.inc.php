<?php
  /**************************************************************************\
  * eGroupWare - Polls                                                       *
  * http://www.egroupware.org                                                *
  * Copyright (c) 1999 Till Gerken (tig@skv.org)                             *
  * Modified by Greg Haygood (shrykedude@bellsouth.net)                      *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	class bo
	{
		var $so;
		var $debug = false;

        var $start  = 0;
        var $query  = '';
        var $sort   = '';
        var $order  = '';
        var $filter = 0;
        var $limit  = 0;
        var $total  = 0;

		var $public_functions = array(
				'user_can_vote'		=> True,
				'view_results'		=> True,
				'generate_ui'		=> True,
				'get_list'			=> True,
				'add_vote'			=> True,
				'get_poll_data'		=> True,
				'somebusinessfunc'	=> True,
		);

		function bo($session=False)
		{
			$this->so = createobject('polls.so');
			$this->load_settings();

            if($session)
            {
                $this->read_sessiondata();
                $this->use_session = True;
            }

            $_start   = get_var('start',array('POST','GET'));
            $_query   = get_var('query',array('POST','GET'));
            $_sort    = get_var('sort',array('POST','GET'));
            $_order   = get_var('order',array('POST','GET'));
            $_limit   = get_var('limit',array('POST','GET'));
            $_filter  = get_var('filter',array('POST','GET'));


            if(isset($_start))
            {
                if($this->debug) { echo '<br>overriding $start: "' . $this->start . '" now "' . $_start . '"'; }
                $this->start = $_start;
            }

            if($_limit)
            {
                $this->limit  = $_limit;
            }
			else
			{
				 $this->limit = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
			}

            if((empty($_query) && !empty($this->query)) || !empty($_query))
            {
                $this->query  = $_query;
            }

			if(!empty($_sort))
            {
                if($this->debug) { echo '<br>overriding $sort: "' . $this->sort . '" now "' . $_sort . '"'; }
                $this->sort   = $_sort;
            }
			else
			{
				$this->sort = 'ASC';
			}

            if(!empty($_order))
            {
                if($this->debug) { echo '<br>overriding $order: "' . $this->order . '" now "' . $_order . '"'; }
                $this->order  = $_order;
            }
			else
			{
				$this->order = 'poll_title';
			}

            if(!empty($_filter))
            {
                if($this->debug) { echo '<br>overriding $filter: "' . $this->filter . '" now "' . $_filter . '"'; }
                $this->filter = $_filter;
            }

		}

		function load_settings()
		{
			$this->so->load_settings();
		}

		function save_settings($data)
		{
			if(isset($data) && is_array($data))
			{
				$this->so->save_settings($data);
			}
		}

        function save_sessiondata($data = '')
        {
            if ($this->use_session)
            {
				if(empty($data) || !is_array($data))
				{
					$data = array();
				}
				$data += array(
					'start'   => $this->start,
					'order'   => $this->order,
					'limit'   => $this->limit,
					'query'   => $this->query,
					'sort'    => $this->sort,
					'filter'  => $this->filter
				);
                if($this->debug) { echo '<br>Save:'; _debug_array($data); }
                $GLOBALS['phpgw']->session->appsession('session_data','polls_list',$data);
            }
        }

        function read_sessiondata()
        {
            $data = $GLOBALS['phpgw']->session->appsession('session_data','polls_list');
            if($this->debug) { echo '<br>Read:'; _debug_array($data); }

            $this->start   = $data['start'];
            $this->limit   = $data['limit'];
            $this->query   = $data['query'];
            $this->sort    = $data['sort'];
            $this->order   = $data['order'];
            $this->filter  = $data['filter'];
        }

		function somebusinessfunc()
		{
			//nothing to be added yet
		}

		function add_vote($poll_id,$vote_id,$user_id)
		{
			if(isset($poll_id) && isset($vote_id) 
			   && (int)$poll_id >= 0 && (int)$vote_id >= 0)
			{
				$this->so->add_vote($poll_id,$vote_id,$user_id);
			}
		}

		function add_answer($poll_id,$answer)
		{
			$this->so->add_answer($poll_id,$answer);		
		}

		function add_question()
		{
			$question = $_POST['question'];
			if(!empty($question))
			{
				$this->so->add_question($question);
				return $this->so->get_last_added_poll();
			}
		}

		function delete_answer($poll_id,$vote_id)
		{
			if(!empty($poll_id) && !empty($vote_id))
			{
				$this->so->delete_answer($poll_id,$vote_id);
			}
		}

		function delete_question($poll_id)
		{
			if(!empty($poll_id))
			{
				$this->so->delete_question($poll_id);
			}
		}

		function update_answer($poll_id,$vote_id,$answer)
		{
			if(!empty($poll_id) && !empty($vote_id) && isset($answer))
			{
				$this->so->update_answer($poll_id,$vote_id,$answer);
			}
		}

		function update_question($poll_id,$question)
		{
			if(!empty($poll_id) && isset($question))
			{
				$this->so->update_question($poll_id,$question);
			}
		}

		function makelink($action,$args)
		{
			$menuaction = 'polls.uiadmin.'.$action;
			return $GLOBALS['phpgw']->link('/index.php',array('menuaction'=>$menuaction,$args));
		}

		function user_can_vote($poll_id)
		{
			if ($GLOBALS['poll_settings']['allow_multiple_votes'])
			{
				return True;
			}   
			
			$poll_id = (int)($poll_id);
			$votes = $this->so->get_user_votecount($poll_id);
			
			return ($votes == 0) ? True : False;
		}

		function get_poll_title($poll_id)
		{
			return trim($this->so->get_poll_title($poll_id));
		}

		function get_poll_total($poll_id)
		{
			$sum = (int)$this->so->get_poll_total($poll_id);
			return $sum >= 0 ? $sum : false;
		}

		function get_poll_data($poll_id,$vote_id = -1)
		{
			return $this->so->get_poll_data($poll_id,$vote_id);
		}

		function get_list($type = 'question', $returnall=false)
		{
			$ret = '';
			$options = array(
				'start'  => $this->start,
				'query'  => $this->query,
				'sort'   => $this->sort,
				'order'  => $this->order
			);
			if(!$returnall)
			{
				$options['limit'] = $this->limit;
			}
			if($type == 'question')
			{
				$ret = $this->so->list_questions($options);
			}
			elseif($type == 'answer')
			{
				$ret = $this->so->list_answers($options);
			} 	
			$this->total = $this->so->total;
			return $ret;
		}

		function Xview_results($poll_id,$showtitle=true,$showtotal=true,$returnstring=false)
		{
			$output = '';
			$poll_id = (int)$poll_id;

            $title = $this->so->get_poll_title($poll_id);
			$sum = $this->so->get_poll_total($poll_id);
			$results = $this->so->get_poll_data($poll_id);

			$output .= '<p><table border="0" align="center" width="400">';
			if($showtitle)
			{
			$output .= ' <tr>' . "\n"
				. '  <td colspan="3" bgcolor="' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '" align="center">'
				. $title . '</td>' . "\n"
				. '</tr>' . "\n";
			}

			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			foreach($results as $result)
			{
				$poll_optionText  = $result['text']; 
				$poll_optionCount = $result['votes'];

				$tr_color = $this->nextmatchs->alternate_row_color($tr_color);
				$output .= ' <tr bgcolor="' . $tr_color . '">' . "\n";

				if ($poll_optionText != '')
				{
					$output .= "  <td>$poll_optionText</td>\n";

					if ($sum)
					{
						$poll_percent = 100 * $poll_optionCount / $sum;
					}
					else
					{
						$poll_percent = 0;
					}

					if ($poll_percent > 0)
					{
						$poll_percentScale = (int)($poll_percent * 1);
						$output .= '  <td><img src="' . $GLOBALS['phpgw_info']['server']['webserver_url']
							. '/polls/images/pollbar.gif" height="12" width="' . $poll_percentScale
							. '"></td>' . "\n";
					}
					else
					{
						$output .= '  <td>&nbsp;</td>' . "\n";
					}

					$output .= sprintf('  <td> %.2f %% (%d)</td>' . "\n" . ' </tr>' . "\n", $poll_percent, $poll_optionCount);

					$output .= ' </tr>' . "\n";
				}
			}

			if($showtotal)
			{
				$output .= ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
					. '  <td>' . lang('Total votes') . ': ' . $sum . '</td>' . "\n"
					. ' </tr>' . "\n";
			}

			$output .= '</table>' . "\n";

			if($returnstring)
			{
				return $output;
			}
			print $output;
			return 0;
		}

		function Xgenerate_ui($poll_id = '')
		{   
			if(empty($poll_id))
			{   
				$poll_id = $this->so->get_latest_poll();
			}
			$poll_id = (int)$poll_id;
			
			if(!$this->user_can_vote($poll_id))
			{   
				return False;
			}

            $poll_title = $this->so->get_poll_title($poll_id);
			$poll_sum = $this->so->get_poll_total($poll_id);
			$results = $this->so->get_poll_data($poll_id);

			echo "\n";
			echo '<form action="' . $GLOBALS['phpgw']->link('/index.php',array('menuaction'=>'polls.ui.vote')) . '" method="post">' . "\n";
			echo '<input type="hidden" name="poll_id" value="' . $poll_id . '">' . "\n";
	//      echo '<input type="hidden" name="poll_forwarder" value="' . $poll_forwarder . '">';
			echo '<table border="0" align="center" width="50%">' . "\n"
				. ' <tr>' . "\n"
				. '  <td colspan="2" bgcolor="' . $GLOBALS['phpgw_info']['theme']['th_bg'] . '" align="center">&nbsp;'
				. $poll_title . '&nbsp;</td>' . "\n"
				. ' </tr>' . "\n";
			
			print_r($results);
			foreach($results as $result)
			{
				$vote_id = $result['vote_id'];
				$poll_optionText  = $result['text']; 
				$poll_optionCount = $result['votes'];

				$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
				echo ' <tr bgcolor="' . $tr_color . '">' . "\n" 
					. '  <td align="center"><input type="radio" name="poll_voteNr" value="'
					. $vote_id . '"></td>' . "\n"
					. '  <td>&nbsp;' . $poll_optionText . '</td>' . "\n"
					. ' </tr>' . "\n";
			}

			echo ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
				. '  <td colspan="2">&nbsp;</td>' . "\n"
				. ' </tr>' . "\n"
				. ' <tr bgcolor="' . $GLOBALS['phpgw_info']['theme']['bgcolor'] . '">' . "\n"
				. '  <td colspan="2" align="center">'
				. '   <input name="submit" type="submit" value="' . lang('Vote') . '"></td>' . "\n"
				. ' </tr>' . "\n"
				. '</table>' . "\n" . '</form>' . "\n";
		}

	}
?>
