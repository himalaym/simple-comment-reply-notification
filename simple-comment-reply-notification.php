<?php
// Avoid direct access to this piece of code
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Plugin Name: Simple Comment Reply Notification
 * Description: A simple and light-weight plugin for sending
 * 				email notification on reply to a comment.
 * 				
 * Version: 1.0.0
 * Author: Himalay
 * Author URI: http://www.thewebfosters.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

//call register settings function
add_action( 'admin_init', 'register_crn_plugin_settings' );
add_action( 'admin_menu', 'add_admin_menu' );
add_action( 'comment_post', 'crn_save_comment_meta_data' );
add_action( 'comment_post', 'crn_send_email_notification', 10, 2 );

add_filter( 'comment_form_default_fields', 'crn_add_checkbox_to_comment_form' );

function register_crn_plugin_settings() {
	//register settings for notification form
	register_setting( 'crn-settings-group', 'crn_name' );
	register_setting( 'crn-settings-group', 'crn_email', 'crn_email_validation'  );
	register_setting( 'crn-settings-group', 'crn_subject', 'crn_subject_validation' );
	register_setting( 'crn-settings-group', 'crn_checkbox_text' );
	register_setting( 'crn-settings-group', 'crn_checkbox_option' );
	register_setting( 'crn-settings-group', 'crn_message_body' );
}

//validation for settings
function crn_subject_validation($input){
	$message = null;
    $type = null;
	
	if ( null == $input ) {
        $type = 'error';
        $message = __( 'Subject field can not be empty', 'my-text-domain' );
    } else {
		return $input;
	}
	
	add_settings_error(
			'myUniqueIdentifyer',
			esc_attr( 'settings_updated' ),
			$message,
			$type
			);
}

function crn_email_validation($input){
	$message = null;
    $type = null;

    if ( null == $input ) {
        $type = 'error';
        $message = __( 'Email field can not be empty', 'my-text-domain' );
		
    } else {
		if( is_email( $input ) ) {
			return $input;
		} else {
			$type = 'error';
			$message = __( 'Not a valid email', 'my-text-domain' );
		}
	}
	
	add_settings_error(
		'myUniqueIdentifyer',
		esc_attr( 'settings_updated' ),
		$message,
		$type
	);  
} 

//adds admin menu
function add_admin_menu() {
    add_menu_page (
        'Options for email notification',
        'Comment Reply',
        'manage_options',
        'comment-notification',
        'crn_setting_form_for_notification',
		'dashicons-testimonial'
    );
}

//Notification setting form
function crn_setting_form_for_notification() { ?>
	<div class="wrap">
		<h2>Choose options for notification</h2>
			<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'crn-settings-group' ); ?>
			<?php do_settings_sections( 'crn-settings-group' ); ?>
			<?php $message_body = "Hi [commenter_name]\nA new reply to your comment on [post_title]\n[post_link] to view it.\nThanks,\n[sender_name]"; ?>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="crn_name">Sender's Name</label>
						</th>
						<td>
							<input type="text" id="crn_name" name="crn_name"
								   value="<?php echo esc_attr( get_option('crn_name') ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="crn_email">Sender's Email</label>
						</th>
						<td>
							<input type="text" id="crn_email" name="crn_email"
								   value="<?php echo esc_attr( get_option('crn_email') ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="crn_subject">Subject</label>
						</th>
						<td>
							<input type="text" id="crn_subject" name="crn_subject"
								   value="<?php echo esc_attr( get_option('crn_subject') ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="crn_checkbox_text">Checkbox Text</label>
						</th>
						<td>
							<input type="text" id="crn_checkbox_text" name="crn_checkbox_text"
								   value="<?php echo esc_attr( get_option('crn_checkbox_text','Send email
																		  only on reply to my comment') ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							Decide whether checkbox should be checked By Default
						</th>
						<td>
							<input type="radio" name="crn_checkbox_option" id="checkbox_option1"
								   value="yes" <?php if ( get_option('crn_checkbox_option') == 'yes') echo 'checked'; ?> /><label
								   for="checkbox_option1"> Yes</label>
							<input type="radio" name="crn_checkbox_option" id="checkbox_option2"
								   value="no" <?php if ( get_option('crn_checkbox_option') == 'no') echo 'checked';  ?> /><label
								   for="checkbox_option2"> No</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="crn_message_body">Notification message</label>
						</th>
						<td>
							<textarea rows = 6 cols= 50 type="text" id="crn_message_body"
									  name="crn_message_body"><?php
									  echo esc_attr( get_option('crn_message_body', $message_body ) ); ?></textarea>
							<p class="description"><b>Allowed tags :</b>[commenter_name], [post_title], [post_link], [sender_name],
							[reply_content]; [post_link] adds 'click here' text</p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	
<?php }

//checkbox added to comment form
function crn_add_checkbox_to_comment_form($fields) {
	
	$checked_string = "";
	if ( get_option('crn_checkbox_option') == 'yes' ) {
		$checked_string = 'checked = "checked" ';
	}
	$label_string = "Send email only on reply to my comment";
	$checkbox_text = get_option('crn_checkbox_text');
	if ( isset( $checkbox_text ) ) {
		$label_string = "$checkbox_text";
		if( $checkbox_text == '' ) {
			$label_string = "Send email only on reply to my comment";
		}
	}
	
	$fields['comment_notification'] = '<label for = "comment_notification"><input type="checkbox" value = "1"
    name="comment_notification" id="comment_notification"' . $checked_string . '/> ' . $label_string;
	return $fields;
}

function crn_save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['comment_notification'] ) ) && ( $_POST['comment_notification'] == '1') ){
		$comment_notification = $_POST['comment_notification'];
		add_comment_meta( $comment_id, 'comment_notification', 1 );
	}
}

//sends email to commenter
function crn_send_email_notification( $comment_ID, $comment_approved ) {
         
	if( 1 === $comment_approved ) {
		$comment = get_comment($comment_ID);
		if ( $comment->comment_parent ) {
			$parent_comment = get_comment( $comment->comment_parent );
			$value_comment_notification = intval( get_comment_meta ( $parent_comment->comment_ID,
																 'comment_notification', true ) );
			if ( $value_comment_notification == 1 ) {
				 
				$sender_email = get_option( 'crn_email' );
				$sender_name = get_option( 'crn_name' );
				$email_subject = get_option( 'crn_subject' );
				$message_body = get_option( 'crn_message_body' );
				
				
				$commenter_name = $parent_comment->comment_author ;
				$title =  get_the_title( $comment->comment_post_ID );
				$link_to_post = get_comments_link( $comment->comment_post_ID );
				$to = $parent_comment->comment_author_email;
				$reply_content = $comment->comment_content ;
				
				$message_body = str_replace('[commenter_name]','<b>'.$commenter_name.'</b>',$message_body);
				$message_body = str_replace('[post_title]','<b>'.$title.'</b>',$message_body);
				$message_body = str_replace('[sender_name]','<b>'.$sender_name.'</b>',$message_body);
				$message_body = str_replace('[post_link]',"<a href = '$link_to_post'>Click here</a>",$message_body);
				$message_body = str_replace('[reply_content]',$reply_content,$message_body);
				
				$email_header[] = 'Content-Type: text/html';
				$email_header[] = 'From: '.$sender_name.' <'.$sender_email.'>';
				
				wp_mail($to,$email_subject,nl2br($message_body),$email_header) ;
		   }
		}
	}
}
?>