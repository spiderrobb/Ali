<?php
namespace Ali\Controller;

use Ali\Controller\Account;
use Ali\Base\ControllerAbstract;
use Ali\Config;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Ali\App;

class Index extends ControllerAbstract {
	public function init() {
		
		FacebookSession::setDefaultApplication(
			Config::get('facebook.appId'),
			Config::get('facebook.secret')
		);
		
	}
	public function actionIndex() {
		?><pre><?php
		var_dump($this->_input);
		?></pre><?php
	}
	public function actionFacebookRedirect() {
		// building facebook login url
		$helper = new FacebookRedirectLoginHelper(
			Account::getAbsoluteURL('FacebookAuth')
		);
		$app->redirect($helper->getLoginURL());
	}
	public function actionFacebook(){
		$this->_view('facebook');
	}
}