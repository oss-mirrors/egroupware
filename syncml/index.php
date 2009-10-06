<?php
/**
 * eGroupWare - SyncML
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package syncml
 * @subpackage preferences
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @copyright (c) 2009 by Joerg Lehrke <jlehrke@noc.de>
 * @version $Id$
 */

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'currentapp'		=> 'syncml',
		'noheader'		=> False,
		'nonavbar'		=> False,
		'include_xajax'		=> True
	),
);
include('../header.inc.php');

print("
<h1>The SyncML application</h1>
This application does not provide <i>frontend</i> functionality but implements synchronization of various content with external devices via the SyncML/HTTP protocol.<br/>
This EGroupware version does support SyncML 1.0, 1.1 and 1.2.<br/>
You can synchronize your
<ul>
<li>Addressbook</li>
<li>Calendar<li>
<li>Task (InfoLog)</li>
<li>Notes (InfoLog)</li></ul>
The SyncML module provides some customization parameters for conflict handling which you can find in your ".html::a_href('SyncML preferences',array('menuaction'=>'preferences.uisettings.index','appname'=>'syncml')).".

<h2>Conflict handling and server R/O options</h2>
If items are changed on both &mdash; server and client side &mdash; before the next synchronization, this is called a conflict situation. You can configure the conflict handling of EGroupware for every target individually. There are three different ways to handle colliding client changes:
<ol>
<li>The client wins and overwrites the server data.</li>
<li>The server wins and overwrites the client data (default). </li>
<li>Duplicate entries are created from both versions.</li></ol>
The next two options will put the server in read only mode. If the client sends changes to the server, you can choose between:
<ol start='4'>
<li>The client and server data is unchanged (split brain).</li>
<li>The server reverts all client changes.</li></ol>
The second option relies on the client's cooperation, though. If it gets a change, the server will send its own version of the data back to the client. The client is supposed to roll back the changes this way.
<h2>Conflict categories for EGroupware data types</h2>
If you select <b>duplicates</b> as conflict resolution, you may want to assign a certain <b>category</b> for these data items. If EGroupware detects a collision, it will assign the old server item to the selected <i>conflict category</i> and replace the original item with the clients content.
<h2>Client configuration</h2>
There are certain settings of the client which you have to adjust for EGroupware synchronization:
<dl>
<dt><b>Server location/URL</b></dt>
<dd>");
if (preg_match('/http[s]?:\/\/.+/', $GLOBALS['egw_info']['server']['webserver_url'])) {
	print($GLOBALS['egw_info']['server']['webserver_url'] . '/rpc.php');
} else {
	print('http(s)://' . $GLOBALS['egw_info']['server']['hostname'] . $GLOBALS['egw_info']['server']['webserver_url'] . '/rpc.php');
}
print("
</dd>
<dt><b>Username</b></dt>
<dd>");
print($GLOBALS['egw_info']['user']['userid']);
print('@');
print($GLOBALS['egw_info']['user']['domain']);
print("
</dd>
<dt><b>Password</b></dt>
<dd>Your EGroupware password<dd>
</dl>
<h4>Remote database names</h4>
<table border='1'>
<tr><th>&nbsp;EGroupware application&nbsp;</th><th>&nbsp;datastore name&nbsp;</th><th>&nbsp;content type&nbsp;</th></tr>
<tr><td><b>Addressbook</b></td><td>./contacts</td><td>text/vCard</td></tr>
<tr><td></td><td>./sifcontacts</td><td>text/x-s4j-sifc</td></tr>
<tr><td></td><td>./scard</td><td>text/x-s4j-sifc</td></tr>
<tr><td><b>Calendar</b></td><td>./calendar</td><td>text/calendar</td></tr>
<tr><td></td><td>./events</td><td>text/calendar</td></tr>
<tr><td></td><td>./sifcalendar</td><td>text/x-s4j-sife</td></tr>
<tr><td></td><td>./scal</td><td>text/x-s4j-sife</td></tr>
<tr><td><b>Task (InfoLog)</b></td><td>./tasks</td><td>text/calendar</td></tr>
<tr><td></td><td>./siftasks</td><td>text/x-s4j-sift</td></tr>
<tr><td></td><td>./stask</td><td>text/x-s4j-sift</td></tr>
<tr><td><b>Notes (InfoLog)</b></td><td>./notes</td><td>text/x-vnote</td></tr>
<tr><td></td><td>./sifnotes</td><td>text/x-s4j-sifn</td></tr>
<tr><td></td><td>./snote</td><td>text/x-s4j-sifn</td></tr>
</table>
");

$GLOBALS['egw']->common->egw_footer();
