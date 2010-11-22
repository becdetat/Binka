<?php
/* EmailComponent
** Adds basic email send support
** BJS20101031
** (CC A-SA) 2010 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

/*
Example:
$this->Email->send(Array(
	'to' => 'to@domain.com',
	'from' => 'from@domain.com',
	'subject' => 'Email from Belfry Slab',
	'content' => 'Content'
));
If 'from' is not provided, 'to' is used as the from address.
*/

class EmailComponent extends Component {
	
	function init() {
	}

	function send($settings) {
		extract($settings);
		if (!isset($to)) throw new Exception('To address was not provided');
		if (!isset($subject)) $subject = '';
		if (!isset($content)) $content = '';
		if (!isset($from)) $from = $to;

		if (!$this->__checkForEmailHeaderInjection($content)) throw new Exception('Content contains an illegal email header');
		if (!$this->__checkReferer()) throw new Exception('Referer is invalid');
		
		$headers = 
			"From: {$from}".PHP_EOL.
			"Return-Path: {$from}".PHP_EOL.
			"Reply-To: {$from}".PHP_EOL;
			
		mail($to, $subject, $content, $headers);
	}
	
	
	
		// check content for email header injection. The regex is copied from the intarwebz (http://snipplr.com/view/28723/check-for-email-header-injection/)
		// cause I'm lazy.
	function __checkForEmailHeaderInjection($content) {
		return !preg_match('/\b^to+(?=:)\b|^content-type:|^cc:|^bcc:|^from:|^subject:|^mime-version:|^content-transfer-encoding:/im', $content);
	}
	// check referrer (to deny cross-site posts)
	function __checkReferer() {
		return !empty($_SERVER['HTTP_REFERER']) || !strContains($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']);
	}
}
?>