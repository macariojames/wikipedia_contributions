<?php
/**
 * Plugin Name: Wikipedia User Contributions
 * Description: Grab a Wikipedia's user's contributions list and display it on the page
 * Version: 0.2
 * Author: Macario James
 * Author URI: http://macariojames.com
 * License: GPL2
 */

/*  Copyright 2017 Macario James - email : hellothere@macariojames.com

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

// Creating the Settings page
function wikipedia_uc_options_page(){ ?>
    <div class="wrap">
    <h1>Wikipedia User Contributions Options</h1>
    <form method="post" action="options.php">
        <?php
            settings_fields("section");
            do_settings_sections("plugin-options");      
            submit_button(); 
        ?>          
    </form>
	</div>
<?php 
}

function add_wikipedia_uc_menu_item(){
	add_menu_page("Wikipedia UC", "Wikipedia UC", "manage_options", "theme-panel", "wikipedia_uc_options_page", null, 99);
}

add_action("admin_menu", "add_wikipedia_uc_menu_item");

function display_wikipedia_username() { ?>
    <input type="text" name="wikipedia_username" id="wikipedia_username" value="<?php echo get_option('wikipedia_username'); ?>" />
<?php
}

function display_theme_panel_fields()
{
	add_settings_section("section", "All Settings", null, "plugin-options");
	
	add_settings_field("wikipedia_username", "Wikipedia Username", "display_wikipedia_username", "plugin-options", "section");

    register_setting("section", "wikipedia_username");
}

add_action("admin_init", "display_theme_panel_fields");

$dom_script = 'simple_html_dom.php';
include($dom_script);

function wikipedia_user_contribution()  {
	$wu = get_option('wikipedia_username');

	$url = "https://en.wikipedia.org/wiki/Special:Contributions/".$wu;
	 
	$html = file_get_html($url);

	$output = "<div class='col-md-12' style='clear: both: margin: 0 auto;'> <h5>Wikipedia Contributions (most recent order)</h5><ol class='wikipedia-contributions'>";

	foreach($html->find('ul[class=mw-contributions-list] li') as $ct) {
		foreach($ct->find('a') as $a) {
			// prepends each link's href with wikipedia.org since 
			// by default wikipedia uses relative paths ~mj
			$a->href = 'https://en.wikipedia.org' . $a->href;
			$a->target = '_blank'; // adds attribute to open in new window
		}
		$output .= "<li>" . $ct->innertext . "</li>";
	}

	$output .= "</ol></div>";

	$html->clear();

	return $output;
	 
}

// adds a shortcode to display in your page/post easily
add_shortcode('wuc', 'wikipedia_user_contribution');

?>