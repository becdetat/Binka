<?php
/* inflector.php
** Based on CakePHP's Inflector class, this converts strings to different inflections, such as my_controller_class to MyControllerClass
** Should be static but I've had to wreck it for php4
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

class Inflector extends Object {
	// From CakePHP: Returns the given lower_case_and_underscored_word as a CamelCased word.
	// This is for class names
	//static 
	function camelize($w) { return Inflector::camelcase($w); }
	//static 
	function camelcase($lowerCaseAndUnderscoredWord) {
		return str_replace(" ", "", ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord)));
	}
	
	// From CakePHP (as variable()). Returns the given lower_case_and_underscored_word as a camelBacked word
	// This is for variable names
	//static 
	function camelback($lowerCaseAndUnderscoredWord) {
		$c = Inflector::camelcase($lowerCaseAndUnderscoredWord);
		$replace = strtolower(substr($c, 0, 1));
		return preg_replace('/\\w/', $replace, $c, 1);
	}
	
	// From CakePHP: Returns the given camelCasedWord as an underscored_word.
	// this is for filenames and database fields and tables
	//static 
	function underscore($camelCasedWord) {
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
	}

	// Returns the given underscored_word_group as a Human Readable Word Group.
	//static 
	function humanize($lowerCaseAndUnderscoredWord) {
		return ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord));
	}
	
	// Returns 'hello_world' as 'Hello world'
	//static 
	function sentence($lowerCaseAndUnderscoredWord) {
		return ucfirst(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
	}
};

?>