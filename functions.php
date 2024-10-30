<?php
/*
Plugin Name: Contact Form 7 Radio Field Mandatory
Plugin URI: https://wordpress.org/plugins/contact-form-7-radio-field-mandatory/
Description: The radio input is a required field by nature!
Author: Laxman Thapa
Author URI: http://www.procab.ch
Version: 1.12
*/

/*  Copyright 2014  Laxman Thapa  (email : thapa.laxman@hotmail.com)

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


add_action('plugins_loaded', 'wcf7_radio_field_mandatory_loader', 11);
//add_action('wpcf7_init', 'wcf7_radio_field_mandatory_loader', 11);
add_action( 'admin_init', 'wcf7_radio_field_mandatory_update_tag_generator', 31 );



/**
 * Contact Form 7 Radio Field Mandatory Loader
 */
function wcf7_radio_field_mandatory_loader(){
	global $pagenow;
	if (function_exists('wpcf7_add_shortcode')) {
		
		//add shortcodes for radio
		wpcf7_add_shortcode( array( 'radio*' ),'wpcf7_checkbox_shortcode_handler', true );
		
		//add shortcodes for button
		wpcf7_add_shortcode('button','wpcf7_rfm_button_shortcode_handler');
		
		//validation for radio
		add_filter( 'wpcf7_validate_radio*', 'wcf7_radio_field_mandatory_validation_filter', 10, 2 );
	}else{
		if ($pagenow != 'plugins.php') { return; }
		add_action('admin_notices', 'wcf7_radio_field_mandatory_error');
		wp_enqueue_script('thickbox');

		function wcf7_radio_field_mandatory_error() {
			$out = '<div class="error" id="messages"><p>';
			if(file_exists(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php')) {
				$out .= __('The Contact Form 7 is installed, but <strong>you must activate Contact Form 7</strong> below for Mandatory Radio Button Plugin to work.','wpcf7_mandatory_radio_button');
			} else {
				$out .= __('The Contact Form 7 plugin must be installed for the Mandatory Radio Button Plugin to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>', 'wpcf7_mandatory_radio_button');
			}
			$out .= '</p></div>';
			echo $out;
		}
	}
}

/**
 * This callback will inject 'Required Field' to the radio tag generator 
 */
function wcf7_radio_field_mandatory_update_tag_generator() {
	if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
		return;
	wpcf7_add_tag_generator( 'radio', __( 'Radio buttons', 'contact-form-7' ),'wpcf7-tg-pane-radio', 'wcf7_radio_field_mandatory_tag_call_bck' );
	
	//button
	wpcf7_add_tag_generator( 'button', __( 'Buttons!', 'contact-form-7' ),'wpcf7-tg-pane-rfm-button', 'wcf7_radio_field_mandatory_button_tag_call_bck', array( 'nameless' => 1 ) );
}

function wcf7_radio_field_mandatory_tag_call_bck(){
	?>
	<script>
	jQuery('<tr />').html('<td><input type="checkbox" name="required">&nbsp;Required field?</td>')
					.prependTo(jQuery('#wpcf7-tg-pane-radio form table:first-child > tbody'));
	</script>
	<?php
} 

function wcf7_radio_field_mandatory_validation_filter( $result, $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	$type = $tag->type;
	$name = $tag->name;

	$value = isset( $_POST[$name] ) ? (array) $_POST[$name] : array();
	if ( 'radio*' == $type ) {
		if ( empty( $value ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
		}
	}

	if ( isset( $result['reason'][$name] ) && $id = $tag->get_id_option() ) {
		$result['idref'][$name] = $id;
	}
	return $result;
}


//button tag generator
function wcf7_radio_field_mandatory_button_tag_call_bck( $contact_form ) {
	?>
<div id="wpcf7-tg-pane-rfm-button" class="hidden">
<form action="">
<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Label', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="text" name="values" class="oneline" /></td>

<td></td>
</tr>
</table>

<div class="tg-tag">
	<?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?><br />
	<input type="text" name="button" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" />
</div>
</form>
</div>
<?php
}


//button shortcode
function wpcf7_rfm_button_shortcode_handler($tag){
	$tag = new WPCF7_Shortcode( $tag );
	$class = wpcf7_form_controls_class( $tag->type );
	$atts = array();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

	if ( empty( $value ) )
		$value = __( 'Button', 'contact-form-7' );

	$atts['type'] = 'button';
	$atts['value'] = $value;

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf( '<input %1$s />', $atts );
	return $html;
}
?>