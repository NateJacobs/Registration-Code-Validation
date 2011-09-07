<?php
/**
 *	Plugin Name: Registration Code Validation
 *	Plugin URI: 
 *	Description: Takes an array of registration codes and validates new user registrations based upon the code the user enters. If the code is valid and used to register, it is then removed from the list of valid codes.
 *	Version: 0.1
 *	License: GPL V2
 *	Author: Nate Jacobs <nate@natejacobs.org>
 *	Author URI: http://natejacobs.org
 */

class RCVUserRegistration
{
	// hook into specific actions for registration forms
	public function __construct()
	{
		add_action( 'register_form', array( __CLASS__, 'add_registration_field' ) );
		add_action( 'registration_errors', array( __CLASS__, 'check_reg_code' ), 10, 3 );
		add_action( 'user_register', array( __CLASS__, 'update_reg_codes' ) );
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
	}
	
	/** 
	 *	Activation
	 *
	 *	Only called when plugin is activated. It creates the array of registration codes.
	 *	If you activate the plugin without changing the codes you will need to run
	 *	update_option( 'my_registration_codes', $array ) and pass an array of registration codes.
	 *	You can do this in your functions.php, but remember to delete the function call 
	 *	after you run it otherwise your codes will never run out.
	 *
	 *	@todo		create an options page where users can add an array of codes to use.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function activation()
	{
		$reg_codes = array( 12, 123, 1234, 12345, 123456, 1234567, 12345678, 123456789, 1234567890, 'abc123' );
		// saves the registration codes in the wp_options table
		add_option( 'my_registration_codes', $reg_codes );
	}
	
	/** 
	 *	Add Registratoin Fields
	 *
	 *	Hook into the registration form and output a new form field.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function add_registration_field()
	{
		?>
		<p>
			<label for="registration-code"><?php echo __( 'Registration Code' ); ?><br>
				<input type="text" id="registration-code" class="input" name="reg-code" tabindex="20" value="<?php if( isset( $_POST['reg-code'] ) ) echo $_POST['reg-code']; ?>">
			</label>
		</p>
		<?php
	}
	
	/** 
	 *	Check Registration Code
	 *
	 *	Validate the registration code. If invalid give error, if valid register user.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function check_reg_code( $errors, $login, $email )
	{
		// is the registration code field empty?
		if( empty( $_POST['reg-code'] ) )
		{
			// if so, send an error
			$errors->add('empty_realname', __( '<strong>ERROR: </strong>Please enter a registration code.' ) );
		}
		else
		{
			// okay so there is a registration code. Lets get the valid codes from the options table
			$my_codes = get_option( 'my_registration_codes' );
			// is the registration code the user entered in the list of valid ones from the options table?
			if( !in_array( $_POST['reg-code'], $my_codes ) )
				// sorry, it isn't. Go ahead and pass an error back.
				$errors->add('empty_realname', __( '<strong>ERROR: </strong>Sorry no match for that registration code.' ) );
		}
		return $errors;
		
		// yay, if no errors go ahead and process the registration
	}
	
	/** 
	 *	Update Registration Codes
	 *
	 *	Takes the user entered registration code and removes it from the options table.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function update_reg_codes()
	{
		// get the registration codes again
		$my_codes = get_option( 'my_registration_codes' );
		// get the key of the user entered registration code, then delete that value from the valid code array
		if( ( $key = array_search( $_POST['reg-code'], $my_codes ) ) !== false )
			unset( $my_codes[$key] );
		// reindex the valid code array
		$my_new_codes = array_values( $my_codes );
		// store the updated array back in the options table
		update_option( 'my_registration_codes', $my_new_codes );
	}
}
new RCVUserRegistration();