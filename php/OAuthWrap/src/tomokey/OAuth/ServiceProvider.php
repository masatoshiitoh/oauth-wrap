<?php
class ServiceProvider {
	public $request_token_url;
	public $user_authorization_url;
	public $access_token_url;

	function __construct($request_token_url, $user_authorization_url, $access_token_url)
	{
		$this->request_token_url = $request_token_url;
		$this->user_authorization_url = $user_authorization_url;
		$this->access_token_url = $access_token_url;
	}

	function __toString()
	{
		return "ServiceProvider[request_token_url=$this->request_token_url,user_authorization_url=$this->user_authorization_url,access_token_url=$this->access_token_url]";
	}
}
?>