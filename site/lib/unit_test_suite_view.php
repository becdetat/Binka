<?php
/* UnitTestSuite view
** A special library view for the unit test suite controller
** This will just be rendered with the app's default layout
** (CC-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

$caseCount = 0;
$casePassCount =0;
$caseFailCount = 0;
foreach ($testCases as $testCase) {
	$caseCount ++;
	if ($testCase['failCount'] == 0) {
		$casePassCount ++;
	} else {
		$caseFailCount ++;
	}
}
?>

<h2>Test suite</h2>

<!-- summary of tests -->
<p class="test_result <?php
	if ($caseFailCount == 0) {
		e('test_result_success');
	} else {
		e('test_result_failure');
	}
?>"><?php e($caseCount); ?> test cases, <strong><?php e($casePassCount); ?></strong> passes and <strong><?php e($caseFailCount); ?></strong> fails.</p>

<br/>

<!-- break down each test -->
<?php foreach ($testCases as $testCase) { ?>
	<h3>Test case: <?php e(Inflector::humanize(Inflector::underscore($testCase['name']))); ?></h3>
	<?php foreach ($testCase['failedTests'] as $test=>$result) { ?>
		<p class="test_detail"><strong class="test_failure">Fail:</strong> <?php e($test); ?><br />
		<?php e($result); ?></p>
	<?php } ?>
	<p><?php e($testCase['testCount']); ?> tests, <strong class="test_success"><?php e($testCase['passCount']); ?></strong> passes and <strong class="test_failure"><?php e($testCase['failCount']); ?></strong> fails.</p>
	<br/>
<?php } ?>





<style type="text/css">
	#content h3 {
		padding-bottom: 0;
	}
	.test_success {
		color: #0f0;
	}
	.test_failure {
		color: #f00;
	}
	.test_result {
		font-weight: bold;
		font-style: italic;
		padding: 1em;
		color: #000;
	}
	.test_result_success {
		background-color: #0f0;
	}
	.test_result_failure {
		background-color: #f00;
	}
	.test_detail {
		padding: 0 0 0 2em;
	}
</style>