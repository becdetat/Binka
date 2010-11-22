<?php
/* unit_test_case.php
** Base class for a unit test case. I'm going to assume testing is done on PHP5, this should work on PHP4 however the
** test names will be mangled.
** Well PHP4 doesn't actually support try..catch so screw it
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

// Set up a function to convert errors into exceptions. This is only hooked in when
// UnitTestCase::runTests() is run (ie when testing) (http://au.php.net/manual/en/class.errorexception.php)
function unitTestCase_exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
	//throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

class UnitTestCase extends Controller {
	var $testMethods = array();
	
	var $currentTestName = '';
	var $results = array();

	function __construct() {
		parent::__construct();
	
		// find all the test methods in this unit test
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (strStartsWith($method, 'test')) {
				$this->testMethods[] = $method;
			}
		}
	}
	
	// override these to implement test setup and teardown
	function setup() {}
	function teardown() {}

	function runTests() {
		set_error_handler('unitTestCase_exceptionErrorHandler');
		
		$this->results = array();
		
		foreach ($this->testMethods as $test) {
			$this->currentTestName = $test;
			$this->results[$test] = '';	// a test is successful if $this->results[$test] is still empty after running the test
			
//			try {
				// setup
				$this->setup();
				
				// execute test and record results
					$this->dispatchMethod($test);

				// teardown
				$this->teardown();
//			} catch (Exception $ex) {
//				$this->results[$test] .= 'Exception caught: '.$ex->getMessage().'<br />';
//			}
		}
		
		$this->set('testResults', $this->results);
	}
	
	function assert($b, $moreInfo = null) { 
		$this->assertTrue($b, $moreInfo); 
	}
	function assertTrue($b, $moreInfo = null) {
		if ($b !== true) {
			$this->results[$this->currentTestName] .= 'Call to assertTrue() failed';
			if (isset($moreInfo)) {
				$this->results[$this->currentTestName] .= ': '.$moreInfo;
			}
			$this->results[$this->currentTestName] .= '<br />';
		}
	}
	function assertFalse($b, $moreInfo = null) {
		if ($b !== false) {
			$this->results[$this->currentTestName] .= 'Call to assertFalse() failed';
			if (isset($moreInfo)) {
				$this->results[$this->currentTestName] .= ': '.$moreInfo;
			}
			$this->results[$this->currentTestName] .= '<br />';
		}
	}
	
	
};

?>