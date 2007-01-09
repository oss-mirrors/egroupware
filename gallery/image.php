<?php
/**
 * eGroupWare Gallery2 integration
 * 
 * This script redirect to the download URL of the first image of a G2 external imageblock.
 * 
 * It takes the same arguments as the external imageblock and just parses them on:
 * - g2_blocks 	   Pipe(|) separate list chosen from: randomImage, recentImage, viewedImage, randomAlbum, recentAlbum, viewedAlbum, dailyImage, 
 *                 weeklyImage, monthlyImage, dailyAlbum, weeklyAlbum, monthlyAlbum, specificItem; default is randomImage
 * - g2_itemId 	   Limit the item selection to the subtree of the gallery under the album with the given id; 
 *                 or the id of the item to display when used with specificItem block type
 * - g2_maxSize    Scale images to this maximum size. If used alone Gallery will locate the most-closely-sized image to the specified value - 
 *                 larger images will be scaled down as necessary in your browser. If specified along with g2_show=fullSize the full size 
 *                 image will always be used and scaled down as necessary.
 * - g2_exactSize  Just like g2_maxSize except that it will not substitute an image smaller than the size you request, 
 *                 so you'll get the closest match in size possible. 
 *                 Note that this may use a lot more bandwidth if a much larger image has to be scaled down in your browser.
 * 
 * @link http://www.egroupware.org
 * @link http://gallery.sourceforge.net/
 * @package gallery
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright 2006 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
	$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).
	'/gallery2/main.php?g2_view=imageblock.External&g2_show=none';
	
foreach($_GET as $name => $value)
{
	$url .= '&'.$name.'='.urlencode($value);
}
//echo $url;
//header('Location: '.str_replace('&amp;','&',$url)); exit;

$f = fopen($url,'r');
$content = fread($f,1024);
//echo "<pre>".htmlspecialchars($content)."</pre>\n";

if (preg_match('/<img.*src="([^"]+)"/',$content,$matches))
{
	header('Location: '.str_replace('&amp;','&',$matches[1]));
	fclose($f);
	exit;
}
// if we cant find the usl, we output the page, it might contain an error or permission denied
echo $content;
fpassthru($f);
fclose($f);