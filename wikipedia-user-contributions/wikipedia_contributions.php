<?php
/**
 * Plugin Name: Wikipedia User Contributions
 * Description: Grab a Wikipedia's user's contributions list and display it on the page
 * Version: 0.1
 * Author: Macario James
 * Author URI: http://macariojames.com
 * License: GPL2
 */

/*  Copyright 2016 Macario James - email : hellothere@macariojames.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once('simple_html_dom.php');

function wikipedia_contribution()  {
	$search_term = "macariojames";
	$url = "https://en.wikipedia.org/wiki/Special:Contributions/".$search_term;
	 
	$html = file_get_html($url);

	$output = "<div class='col-md-10 col-md-offset-1' style='clear: both: margin: 0 auto;'> <h6>Wikipedia Contributions (most recent order)</h6><ol class='wikipedia-contributions'>";

	foreach($html->find('ul[class=mw-contributions-list] li') as $ct) {
		foreach($ct->find('a') as $a) {
			//prepends each link's href with wikipedia.org since 
			//by default wikipedia uses relative paths ~mj
			$a->href = 'http://en.wikipedia.org' . $a->href;
			$a->target = '_blank'; // adds attribute to open in new window ~mj
		}
		$output .= "<li>" . $ct->innertext . "</li>";
	}

	$output .= "</ol></div>";

	$html->clear();

	return $output;
	 
}


add_shortcode('wiki_contribute', 'wikipedia_contribution');

?>
