##Registration Validation Code##
This is a WordPress plugin. It validates a new user registration off a set of predefined validation codes. This does not work with multi-site. 

###Future Development###
Next version - probably will be 1.0 - will create a settings page to allow for a user to decide how many registration codes to generate.

###Changelog###

== Changelog ==

= 0.3 =
* Changed __CLASS__ to $this
* Changed meta_key name to be more unique
 
= 0.2 =
* Added code generation with three new functions
* createRandomString(), validUniqueString(), createRandomStringCollection()
* The plugin will now create 10 random codes and add them to the options table. If you want to generate more, before activation you must change the $number_of_strings variable in the __construct() method.
 
= 0.1 =
* Initial plugin version