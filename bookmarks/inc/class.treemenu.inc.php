<?php
	/* $Id$ */

	class treemenu
	{
		function showmenu($treeinfo)
		{
			$p = $GLOBALS['HTTP_GET_VARS']['p'];

			$img_expand   = $GLOBALS['phpgw']->common->image('bookmarks','plus');
			$img_collapse = $GLOBALS['phpgw']->common->image('phpgwapi','tree_collapse');
			$img_line     = $GLOBALS['phpgw']->common->image('bookmarks','tree_vertline');
			$img_split    = $GLOBALS['phpgw']->common->image('bookmarks','tree_split');
			$img_end      = $GLOBALS['phpgw']->common->image('bookmarks','tree_end');
			$img_leaf     = $GLOBALS['phpgw']->common->image('bookmarks','minus');
			$img_spc      = $GLOBALS['phpgw']->common->image('phpgwapi','tree_space');
			$img_closed   = $GLOBALS['phpgw']->common->image('bookmarks','closed');
			$img_open     = $GLOBALS['phpgw']->common->image('bookmarks','open');

			/*********************************************/
			/* read file to $tree array                  */
			/* tree[x][0] -> tree level                  */
			/* tree[x][1] -> item text                   */
			/* tree[x][2] -> item link                   */
			/* tree[x][3] -> link target                 */
			/* tree[x][4] -> last item in subtree        */
			/*********************************************/

			$maxlevel = 0;
			$cnt = 0;

			while (list($null,$buffer) = each($treeinfo))
			{
				$tree[$cnt][0] = strspn($buffer,'.');
				$tmp  = rtrim(substr($buffer,$tree[$cnt][0]));
				$node = explode('|',$tmp);
				$tree[$cnt][1] = $node[0];
				$tree[$cnt][2] = $node[1];
				$tree[$cnt][3] = $node[2];
				$tree[$cnt][4] = 0;
				if ($tree[$cnt][0] > $maxlevel)
				{
					$maxlevel=$tree[$cnt][0];
				}
				$cnt++;
			}

			for ($i=0; $i<count($tree); $i++)
			{
				$expand[$i]  = 0;
				$visible[$i] = 0;
				$levels[$i]  = 0;
			}

			// Get Node numbers to expand
			if ($p!='')
			{
				$explevels = explode('|',$p);
			}
			$i=0;
			while($i<count($explevels))
			{
				$expand[$explevels[$i]]=1;
				$i++;
			}

			// Find last nodes of subtrees
			$lastlevel=$maxlevel;
			for ($i=count($tree)-1; $i>=0; $i--)
			{
				if ( $tree[$i][0] < $lastlevel )
				{
					for ($j=$tree[$i][0]+1; $j <= $maxlevel; $j++)
					{
						$levels[$j] = 0;
					}
				}
				if ( $levels[$tree[$i][0]]==0 )
				{
					$levels[$tree[$i][0]] = 1;
					$tree[$i][4]          = 1;
				}
				else
				{
					$tree[$i][4] = 0;
					$lastlevel   = $tree[$i][0];
				}
			}

			// Determine visible nodes
			// all root nodes are always visible
			for ($i=0; $i < count($tree); $i++)
			{
				if ($tree[$i][0]==1)
				{
					$visible[$i] = 1;
				}
			}

			for ($i=0; $i < count($explevels); $i++)
			{
				$n = $explevels[$i];
				if ( ($visible[$n]==1) && ($expand[$n]==1) )
				{
					$j=$n+1;
					while ( $tree[$j][0] > $tree[$n][0] )
					{
						if ($tree[$j][0]==$tree[$n][0]+1)
						{
							$visible[$j] = 1;
						}
						$j++;
					}
				}
			}

			// Output nicely formatted tree
			for ($i=0; $i<$maxlevel; $i++)
			{
				$levels[$i] = 1;
			}

			$maxlevel++;

			$out  = '<form><table cellspacing="0" cellpadding="0" border="0" cols="' . ($maxlevel+3) . '" width="70%">' . "\n";
			$out .= '<tr>' . "\n";
			for ($i=0; $i<$maxlevel; $i++)
			{
				$out .= '<td width="36"></td>';
			}
			$out .= '<td width="100%">&nbsp;</td></tr>' . "\n";
			$cnt  = 0;
			while ($cnt<count($tree))
			{
				if ($visible[$cnt])
				{
					// start new row
					$out .= '<tr>';

					// vertical lines from higher levels
					$i=0;
					while ($i<$tree[$cnt][0]-1) 
					{
						if ($levels[$i]==1)
						{
							$out .= '<td><a name="' . $cnt . '">&nbsp;</td>';
						}
						else
						{
							$out .= '<td><a name="' . $cnt . '"></a><img src="' . $img_spc . '"></td>';
						}
						$i++;
					}

					// corner at end of subtree or t-split
					if ($tree[$cnt][4]==1)
					{
						$out .= '<td>&nbsp;</td>' . "\n";
						$levels[$tree[$cnt][0]-1] = 0;
					}
					else
					{
						$out .= '<td>&nbsp;</td>';
						$levels[$tree[$cnt][0]-1] = 1;
					} 

					// Node (with subtree) or Leaf (no subtree)
					if ($tree[$cnt+1][0]>$tree[$cnt][0])
					{
						// Create expand/collapse parameters
						$i=0; $params="p=";
						while($i<count($expand))
						{
							if ( ($expand[$i]==1) && ($cnt!=$i) || ($expand[$i]==0 && $cnt==$i))
							{
								$params = $params . $i;
								$params = $params . '|';
							}
							$i++;
						}

						if ($expand[$cnt]==0)
						{
							$out .= '<td><a href="'
								. $GLOBALS['phpgw']->link('/bookmarks/tree.php',$params . '!' . $cnt)
								. '"><img src="' . $img_expand . '" border=no></a><img src="' . $img_closed
								. '" border=no></td>';
						}
						else
						{
							$out .= '<td><a href="'
								. $GLOBALS['phpgw']->link('/bookmarks/tree.php',$params . '!' . $cnt)
								. '"><img src="' . $img_collapse . '" border=no></a><img src="' . $img_open
								. '" border=no></td>';
						}
					}
					else
					{
						// Tree Leaf
						$out .= '<td><img src="' . $img_leaf . '"><img src="' . $img_closed . '"></td>';
					}

					// output item text
					if ($tree[$cnt][2]=="")
					{
						$out .= '<td colspan="' . ($maxlevel-$tree[$cnt][0]) . '">' . $tree[$cnt][1] . '</td>';
					}
					else
					{
						$out .= '<td colspan=' . ($maxlevel-$tree[$cnt][0]) . '">' . $tree[$cnt][2] . '</td>';
					}
 
					// end row
					$out .= "</tr>\n";
				}
				$cnt++;
			}
			$out .= '</table></form>' . "\n";

			return $out;
		}
	}	// end class
