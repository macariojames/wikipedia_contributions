<?php
/**
 * Plugin Name: Wikipedia User Contributions Display
 * Description: Grab a Wikipedia's user's contributions list and display it on a page via shortcode
 * Version: 0.3
 * Author: Macario James
 * Author URI: http://macariojames.com
 * License: GPL2
 */

/*  Copyright 2018 Macario James - email : hello@macariojames.com

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
function wucd_options_page(){ ?>
    <div class="wrap">
    <h1>Wikipedia User Contributions Display &mdash; Options</h1>
    <form method="post" action="options.php">
        <?php
            // Output the hidden fields, nonce, etc. 
            settings_fields("wucd_option_group");
            // Output the settings section(s)
            do_settings_sections("wucd"); // $page - must be same as in add_settings_field and add_settings_section();      
            // Submit button
            submit_button(); 
        ?>          
    </form>
	</div>
<?php 
}

function add_wucd_admin_menu_item(){
	add_menu_page(
		"Wikipedia UCD", 
		"Wikipedia UCD", 
		"manage_options", 
		"wucd-options", 
		"wucd_options_page", 
		null, 
		99
	);
}

add_action("admin_menu", "add_wucd_admin_menu_item");

function display_wikipedia_username() {?>
    <input type="text" name="wikipedia_username" id="wikipedia_username"
    value="<?php echo get_option('wikipedia_username'); ?>" />
<?php
}

function wucd_settings_init()
{
	register_setting(
    	"wucd_option_group", 	// $option_group - unique name for option set
    	"wikipedia_username", 	// $option_name - name of each option (more than one option in the same register_settings() function requires array of options
    	"wucd_settings_sanitize" // $sanitize_callback - section/field callback function to validate data; 
    );
    
    add_settings_section(
		"wucd_settings_section", // $id - unique ID for the section / field
		"All Settings", // $title - the title of the section/field (displayed on options page)
		"wucd_settings_callback", // $callback - callback function to be executed
		"wucd" // $page - Options page name (use __FILE__ if creating new options page);
    );

	add_settings_field(
		"wikipedia_username", // $id - unique ID for the section / field
		"Wikipedia Username", // $title - the title of the section / field (displayed on the options page)
		"display_wikipedia_username", // $callback 	
        "wucd", // $page - options page name (use __FILE__ if creating new options page); must be same as $page in add_settings_section
        "wucd_settings_section" // $section; must be same as add_settings_section $id
        //$args = array()
	);
}
add_action( 'admin_init', 'wucd_settings_init' );

function wucd_settings_callback() {
    //echo "<p>Settings Callback. Idk what this does really. ~mj </p>";
}

function wucd_settings_sanitize() {
	return isset( $input ) ? true : false;
}

// Start code for plugin implementation
$dom_script = 'simple_html_dom.php';
include($dom_script);

function wucd()  {
	$wu 	= get_option('wikipedia_username');
	echo $wu;
    $wu 	= sanitize_text_field($wu);
	echo $wu;
    //$limit  = get_option('limit_results');
	$url 	= "https://en.wikipedia.org/wiki/Special:Contributions/".$wu;
	$html 	= file_get_html($url); // added last 3 parameters since php 7.1 breaks stuff
	//var i;

	// uses Bootstrap classes to display contributions
	$output = "$wu
	<div class='col-md-12 clearfix'> 
		<strong>Wikipedia User Contributions &mdash; Most Recent Displayed
        First</strong>
			<ol class='wikipedia-contributions' reversed>";
    //echo $html;
    if (!empty($url)) {
        if(!empty($html)) {
            $content = $html->find('ul[class=mw-contributions-list] li');
	        foreach($content as $ct) {
		        if ( (!empty($limit)) && ($i === $limit) ) break;
		        foreach($ct->find('a') as $a) {
			        // prepends each link's href with wikipedia.org since 
			        // by default wikipedia uses relative paths ~mj
			        $a->href = 'https://en.wikipedia.org' . $a->href;
			        $a->target = '_blank'; // adds attribute to open in new window
		        }
		        $output .= "<li>" . $ct->innertext . "</li>";
		        $i++;
	        }
        }
    }

	$output .= "</ol>
	</div>";

	if(!empty($html)) 
        $html->clear();

	return $output;
}

// adds a shortcode to display in your page/post easily
add_shortcode('wucd', 'wucd');

?>
