<?php

class HappyApplication extends Application
{
	protected $login_action = array('account', 'signin');

	public function getRootDir()
	{
		return dirname(__FILE__);
	}

	protected function registerRoutes()
	{
		//Router の foreach (array_expression as $key => $value) で使用
		return array(
			'admin/signin'
				=> array('controller' => 'admin', 'action' => 'index'),
			'/'
				=> array('controller' => 'status', 'action' => 'index'),
			'/status/post'
				=> array('controller' => 'status', 'action' => 'post'),
			'/user/:user_name'
				=> array('controller' => 'status', 'action' => 'user'),
			'/user/:user_name/status/:id'
				=> array('controller' => 'status', 'action' => 'show'),
			'/account'
				=> array('controller' => 'account', 'action' => 'index'),
			'/account/:action'
				=> array('controller' => 'account'),
			'/follow'
				=> array('controller' => 'account', 'action' => 'follow'),
		);
	}

	protected function configure()
	{
		$this->db_manager->connect('master', array(
			'dsn' => 'mysql:dbname=happy2;host=localhost',
			'user' => 'root',
			'password' => 'penpen',
		));
	}
}
