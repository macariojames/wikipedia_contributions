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
add_action( 'admin_init', 'wucd_settings_init_fn' );
add_action( 'admin_menu', 'wucd_add_admin_menu_item' );
register_activation_hook(__FILE__, 'wucd_add_defaults_to_db');

// Creating the Settings page
function wucd_options_page_fn(){ ?>
    <!--// Start Wikipedia User Contribution Display by Macario James \\-->
    <div class="wrap">
        <h1>Wikipedia User Contributions Display &mdash; Options</h1>
        <form method="post" action="options.php">
        <?php
            // Output the hidden fields, nonce, etc. 
            settings_fields(__FILE__);
            // Output the settings section(s)
            do_settings_sections(__FILE__); // $page - must be same as in add_settings_field and add_settings_section();      
            // Submit button
            //submit_button();
        ?>
        <p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Options'); ?>" />
		</p>          
        </form>
    </div>
    <!--// END Wikipedia User Contribution Display by Macario James \\-->

<?php 
}

function wucd_setting_wikipedia_username() {
    $options = (array) get_option('plugin_options');
    ?>
    <input type='text' name='plugin_options[wikipedia_username]'
    id='wikipedia_username' value='<?php echo $options['wikipedia_username'];?>' />
<?php
}

function wucd_settings_init_fn() {
	register_setting(
    	__FILE__, 	// $option_group - unique name for option set
    	"plugin_options",	// $option_name - name of each option (more than one option in the same register_settings() function requires array of options
    	"wucd_plugin_options_validate" // $sanitize_callback - section/field callback function to validate data; 
    );
    
    add_settings_section(
		"main_settings_section", // $id - unique ID for the section / field
		"Main Settings", // $title - the title of the section/field (displayed on options page)
		"wucd_settings_fn", // $callback - callback function to be executed
		__FILE__ // $page - Options page name (use __FILE__ if creating new options page);
    );

	add_settings_field(
		"wikipedia_username", // $id - unique ID for the section / field
		"Wikipedia Username", // $title - the title of the section / field (displayed on the options page)
		"wucd_setting_wikipedia_username", // $callback 	
        __FILE__, // $page - options page name (use __FILE__ if creating new options page); must be same as $page in add_settings_section
        "main_settings_section" // $section; must be same as add_settings_section $id
    );
    add_settings_field(
        'chkbox1', 
        'Restore Defaults Upon Reactivation?',
        'wucd_setting_chk1_fn', 
        __FILE__, 
        'main_settings_section'
    );
}

function wucd_add_admin_menu_item(){
	add_options_page(
		"Wikipedia UCD Page", 
		"Wikipedia UCD", 
		"manage_options", 
		__FILE__, 
		"wucd_options_page_fn"
	);
}


function wucd_settings_fn() {
    //echo "<p>Settings Callback. Idk what this does really. ~mj </p>";
}

function wucd_plugin_options_validate() {
	return isset( $input ) ? true : false;
}


// Define default option settings
function wucd_add_defaults_to_db() {
    $tmp = (array) get_option('plugin_options');
    if(($tmp['chkbox1'] == 'on') || (!is_array($tmp))) {
        $arr = array(
            "wikipedia_username" => "macariojames",
            "chkbox1" => "",
            "chkbox2" => "on"
        );
    update_option('plugin_options', $arr);
    }
}

function wucd_setting_chk1_fn() {
	$options = (array) get_option('plugin_options');
	if($options['chkbox1']) { 
        $checked = ' checked="checked" '; 
    }
	echo "<input ".$checked." id='plugin_chk1' name='plugin_options[chkbox1]' type='checkbox' />";
}

/*function wucd_plugin_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);
	return $input; // return validated input
} */

// Start code for plugin implementation
$dom_script = 'simple_html_dom.php';
include($dom_script);

function wucd() {
	$wu 	= get_option('wikipedia_username');
	//echo $wu;
    $wu 	= sanitize_text_field($wu);
	//echo $wu;
    //$limit  = get_option('limit_results');
	$url 	= "https://en.wikipedia.org/wiki/Special:Contributions/".$wu;
	$html 	= file_get_html($url); // added last 3 parameters since php 7.1 breaks stuff
	//var i;

	// uses Bootstrap classes to display contributions
	$output = "
	<div class='col-md-12 clearfix'> 
		<strong>Wikipedia User Contributions &mdash; Most Recent Displayed
        First</strong>
			<ol class='wikipedia-contributions' reversed>";
    //echo $html;
    if (!empty($url)) {
        if(!empty($html)) {
            $content = $html->find('ul[class=mw-contributions-list] li');
	        foreach($content as $ct) {
		        //if ( (!empty($limit)) && ($i === $limit) ) break;
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
