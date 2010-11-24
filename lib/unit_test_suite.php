<?php
/* unit_test_suite.php
** Base class for a suite of unit test cases
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class UnitTestSuite extends Controller {
	var $__testCases = array();
	
	function addTest($test) {
		// the test should be in SLAB_APP/tests/$test_test_case.php
		$filename = SLAB_APP.'/tests/'.$test.'_test_case.php';
		if (!file_exists($filename)) {
			e('The <em>'.$test.'</em> unit test case could not be found at <code>'.$filename.'</code>');
			die();
		}
		require_once($filename);
		
		$className = Inflector::camelize($test).'TestCase';
		if (!class_exists($className)) {
			e('The <em>'.$className.'</em> unit test case could not be loaded<br/>');
			e("Make sure the <em>$className</em> unit test case is defined at <code>$filename</code><br/>");
			die();
		}
		
		$testCase =  new $className();
		$this->__testCases[$test] =& $testCase;
	}
	
	function runTestCases() {
		$this->view->viewFilename = SLAB_LIB.'/unit_test_suite_view.php';
	
		$testCases = array();
		
		foreach ($this->__testCases as $testCaseName => $testCase) {
			$testCase->runTests();			
			
			$testCount = 0;
			$passCount = 0;
			$failCount = 0;
			$failedTests = array();
			foreach ($testCase->results as $test=>$result) {
				$testCount ++;
				if (!empty($result)) {
					$failCount ++;
					$failedTests[$test] = $result;
				} else {
					$passCount ++;
				}
			}
			
			$testCases[] = array(
				'name' => $testCaseName,
				'testCount' => $testCount,
				'passCount' => $passCount,
				'failCount' => $failCount,
				'failedTests' => $failedTests
			);
		}
		
		$this->set('testCases', $testCases);
	}
	
	function index() {
		$this->runTestCases();
	}
};

?>