<?php
require_once 'tomokey/conf/Consumers.php';
require_once 'tomokey/OAuth/OAuthWrapUtil.php';

$instance = new HowToUse($TOMOKEY);

// リクエストトークン取得
$instance->howToGetRequestToken();

// アクセストークン取得
//$instance->howToGetAccessToken($req_token, $req_token_secret, $verifier);

// APIアクセス
//$instance->howToAccessApi($access_token, $access_token_secret);


class HowToUse
{
	// 対象consumer設定
	public $target_consumer = array();
	
	function __construct($consumer)
	{
	  $this->target_consumer = $consumer;
	}
	
	/**
	 * リクエストトークンの取得方法サンプル
	 */
	function howToGetRequestToken()
	{
		//
		// Google Data APIを利用する場合には"scope"パラメータが必須
		// 参考：http://code.google.com/intl/ja/apis/accounts/docs/OAuth_ref.html#RequestToken
		// 参考：http://code.google.com/intl/ja/apis/gdata/faq.html#AuthScopes
		//
		$additional_param = array('scope' => 'https://www.google.com/calendar/feeds/');
		
		//
		// request tokenを取得
		//
		$req_token = OAuthWrapUtil::get_request_token($this->target_consumer, $additional_param);
		
		print $req_token . "\n";
		
		//
		// user authorizatio url
		//
		$auth_url = OAuthWrapUtil::get_user_authorization_url($this->target_consumer);
		
		print $auth_url . '?' . OAuthUtil::build_http_query(array('oauth_token' => $req_token->key)) . "\n";
	}
	
	/**
	 * アクセストークンの取得方法サンプル
	 * @param $req_token リクエストトークン
	 * @param $req_token_secret リクエストトークンシークレット
	 * @param $verifier ベリファイア
	 */
	function howToGetAccessToken($req_token, $req_token_secret, $verifier)
	{
		// リクエストトークン
		$token = new OAuthToken($req_token, $req_token_secret);
		
		//
		// access tokenを取得
		//
		$access_token = OAuthWrapUtil::get_access_token($this->target_consumer, $token, $verifier);
		
		print $access_token;
	}
	
	/**
	 * APIアクセスのサンプル
	 * @param $access_token アクセストークン
	 * @param $access_token_secret アクセストークンシークレット
	 */
	function howToAccessApi($access_token, $access_token_secret)
	{
		// APIのURL
		$url = "https://www.google.com/calendar/feeds/default/allcalendars/full";
	        
		// パラメータ
		$params = array();
		
		//
		// APIアクセス
		//
	    $token = new OAuthToken($access_token, $access_token_secret);
	    
	    $result = OAuthWrapUtil::invoke(
			$this->target_consumer, 
			$token, 
			'GET', 
			$url, 
			$params);
	        
		var_dump($result);
	}
}
?>
