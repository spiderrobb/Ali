<?php
namespace Ali\Controller;

use Ali\Base\ControllerAbstract;
use Ali\Config;
use Exception;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequestException;
use Facebook\FacebookRequest;

class Account extends ControllerAbstract {
	public function init() {
		FacebookSession::setDefaultApplication(
			Config::get('facebook.appId'),
			Config::get('facebook.secret')
		);
	}
	public function actionIndex() {
		?><h2>account</h2><?php
	}
	public function actionFacebookAuth() {
		$helper = new FacebookRedirectLoginHelper(self::getAbsoluteURL('FacebookAuth'));
		try {
			$session = $helper->getSessionFromRedirect();
		} catch(FacebookRequestException $ex) {
			// When Facebook returns an error
			?><pre><?php var_dump($ex); ?></pre><?php
		} catch(Exception $ex) {
			// When validation fails or other local issues
			?><pre><?php var_dump($ex); ?></pre><?php
		}
		if ($session) {
			// Logged in
			?><h2>Logged In!</h2><?php
			?><pre><?php var_dump($session); ?></pre><?php
			// Add `use Facebook\FacebookRequest;` to top of file
			$request = new FacebookRequest($session, 'GET', '/me');
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			?><pre><?php var_dump($graphObject); ?></pre><?php
		} else {
			?><h2>Not Logged In!</h2><?php
		}
		?><pre><?php print_r($_SESSION); ?></pre><?php
	}
}