<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Certificate Class
* Child Class. Extends from LLMS_Certificate.
*
* Generates certificate post for user. Triggered from engagement.
*/
class LLMS_Certificate_User extends LLMS_Certificate {

	var $user_login;
	var $user_email;
	var $user_pass;

	/**
	 * Constructor
	 */
	function __construct() {

		parent::__construct();
	}

	/**
	 * Check if the user has already earned this achievement
	 * used to prevent duplicates
	 * @return   boolean
	 * @since    3.4.1
	 * @version  3.4.1
	 */
	private function has_user_earned() {

		global $wpdb;

		$count = (int) $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT( pm.meta_id )
			FROM {$wpdb->postmeta} AS pm
			JOIN {$wpdb->prefix}lifterlms_user_postmeta AS upm ON pm.post_id = upm.meta_value
			WHERE pm.meta_key = '_llms_certificate_template'
			  AND pm.meta_value = %d
			  AND upm.meta_key = '_certificate_earned'
			  AND upm.user_id = %d
			  AND upm.post_id = %d
			  LIMIT 1
			;",
			array( $this->certificate_template_id, $this->userid, $this->lesson_id )
		) );

		return ( $count >= 1 );

	}

	/**
	 * Sets up data needed to generate certificate.
	 *
	 * @param  int $email_id  [ID of Certificate]
	 * @param  int $person_id [ID of the user recieving the certificate]
	 * @param  int $lesson_id [ID of associated lesson]
	 *
	 * @return void
	 */
	public function init( $email_id, $person_id, $lesson_id ) {
		global $wpdb;

		$email_content = get_post( $email_id );
		$email_meta = get_post_meta( $email_content->ID );

		$this->certificate_template_id	= $email_id;
		$this->lesson_id    			= $lesson_id;
		$this->title 					= $email_content->post_title;
		$this->certificate_title 		= $email_meta['_llms_certificate_title'][0];
		$this->content 					= $email_content->post_content;
		$this->image 					= $email_meta['_llms_certificate_image'][0];
		$this->userid           		= $person_id;
		$this->user             		= get_user_meta( $person_id );
		$this->user_data				= get_userdata( $person_id );
		$this->user_firstname			= ($this->user['first_name'][0] != '' ?  $this->user['first_name'][0] : $this->user['nickname'][0]);
		$this->user_lastname			= ($this->user['last_name'][0] != '' ?  $this->user['last_name'][0] : '');
		$this->user_email				= $this->user_data->data->user_email;
		$this->template_html 	= 'certificates/template.php';
		$this->email_content	= $email_content->post_content;
		$this->account_link 	= get_permalink( llms_get_page_id( 'myaccount' ) );

		$this->user_login = $this->user_data->user_login;

	}

	/**
	 * [trigger description]
	 *
	 * @param  int $user_id   [ID of the user recieving the certificate]
	 * @param  int $email_id  [ID of the certificate]
	 * @param  int $lesson_id [ID of the associated lesson]
	 *
	 * @return void
	 */
	function trigger( $user_id, $email_id, $lesson_id ) {

		$this->init( $email_id, $user_id, $lesson_id );

		// only award cert if the user hasn't already earned it
		if ( $this->has_user_earned() ) {
			return;
		}

		if ( $user_id ) {
			$this->object				= new WP_User( $user_id );
			$this->user_email         = stripslashes( $this->object->user_email );
			$this->recipient          = $this->user_email;

		}

		if ( ! $this->is_enabled() ) {
			return; }

		$this->create( $this->get_content() );
	}

	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
	function get_content_html() {

		$this->find = array(
			'{site_title}',
			'{user_login}',
			'{site_url}',
			'{first_name}',
			'{last_name}',
			'{email_address}',
			'{current_date}',
		);
		$this->replace = array(
			$this->get_blogname(),
			$this->user_login,
			$this->account_link,
			$this->user_firstname,
			$this->user_lastname,
			$this->user_email,
			date_i18n( get_option( 'date_format' ), strtotime( current_time( 'mysql' ) ) ),
		);

		$content = $this->format_string( $this->content );

		ob_start();
		llms_get_template( $this->template_html, array(
			'email_message' 	 => $content,
			'title'				 => $this->title,
			'image'				 => $this->image,
		) );
		return ob_get_clean();
	}

}

return new LLMS_Certificate_User();
