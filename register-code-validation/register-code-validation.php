<?php
/**
 *	Plugin Name: Registration Code Validation
 *	Plugin URI: 
 *	Description: Takes an array of registration codes and validates new user registrations based upon the code the user enters. If the code is valid and used to register, it is then removed from the list of valid codes.
 *	Version: 0.2
 *	License: GPL V2
 *	Author: Nate Jacobs <nate@natejacobs.org>
 *	Author URI: http://natejacobs.org
 */
 
/**
 *	@todo	create an options page where users can choose how many codes to generate.
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
	 *	As of version 0.2 it will only add 10 codes. If you want to add more you need to modify
	 *	the $number_of_strings variable.
	 *
	 *	@author		Nate Jacobs
	 *	@since		0.1
	 */
	public function activation()
	{
		$character_set = 'abcdefghjkmnpqrstuvwxyz23456789#';
		$string_length = 10;
		$number_of_strings = 10;
		$existing_strings = '';
		$reg_codes = explode( ',', self::createRandomStringCollection( $string_length, $number_of_strings, $character_set, $existing_strings ) );
		// saves the registration codes in the wp_options table
		add_option( 'my_registration_codes', $reg_codes );
	}
	
	/** 
	 *	Create a Random String
	 *
	 *	Generate a random string using the characters and string length passed.
	 *
 	 *	@author		Nate Jacobs
	 *	@since		0.2
	 *
	 *	@param		int		$string_length
	 *	@param		string 	$character_set
	 *	@return		string 	$random_string
	 */
	private function createRandomString( $string_length, $character_set ) 
	{
	  $random_string = array();
	  for ( $i = 1; $i <= $string_length; $i++ ) 
	  {
	    $rand_character = $character_set[rand(0, strlen( $character_set ) - 1)];
	    $random_string[] = $rand_character;
	  }
	  shuffle( $random_string );
	  return implode( '', $random_string );
	}
	
	/** 
	 *	Create a valid Unique String
	 *
	 *	Ensure random string is unique. 
	 *	This method is not fully implemented yet. It is in place for random string passthrough.
	 *	Eventually, it will allow users to input their own custom codes. This will check against them
	 *	to make sure the plugin string generation will not create identical ones.
	 *
 	 *	@author		Nate Jacobs
	 *	@since		0.2
	 *
	 *	@param		string	$string_collection
	 *	@param		string 	$new_string
	 *	@param		string 	$existing_strings
	 */
	private function validUniqueString( $string_collection, $new_string, $existing_strings = '' ) 
	{
	  if ( !strlen( $string_collection ) && !strlen( $existing_strings ) )
	    return true;
		$combined_strings = $string_collection . ", " . $existing_strings;
	  return ( strlen( strpos( $combined_strings, $new_string ) ) ) ? false : true;
	}
	
	/** 
	 *	Generate a Collection of Random Strings
	 *
	 *	Creates a specified number of random strings using the characters provided.
	 *
 	 *	@author		Nate Jacobs
	 *	@since		0.2
	 *
	 *	@param		int		$string_length
	 *	@param		int 	$number_of_strings
	 *	@param		string 	$character_set
	 *	@param		string 	$existing_strings
	 *	@return		string	$string_collection
	 */
	private function createRandomStringCollection( $string_length, $number_of_strings, $character_set, $existing_strings = '' ) 
	{
	  $string_collection = '';
	  for ( $i = 1; $i <= $number_of_strings; $i++ ) 
	  {
	    $random_string = self::createRandomString( $string_length, $character_set );
	    while (!self::validUniqueString( $string_collection, $random_string, $existing_strings ) ) 
	    {
	      $random_string = self::createRandomString( $string_length, $character_set );
	    }
	    $string_collection .= ( !strlen($string_collection ) ) ? $random_string : ", " . $random_string;
	  }
	  return $string_collection;
	}
	
	/** 
	 *	Add Registration Fields
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