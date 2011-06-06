<?php
require_once 'tomokey/OAuth/ServiceProvider.php';
require_once 'OAuth/OAuth.php';

class OAuthWrapUtil
{	
	/**
	 * RequestTokenを取得する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @param array $addional_params 追加パラメータ
	 * @return OAuthToken
	 */
	public static function get_request_token($consumer_config, $addional_params=array())
	{
		// consumer
		$consumer = OAuthWrapUtil::buildConsumer($consumer_config);
		
		// service provider
		$provider = OAuthWrapUtil::buildProvider($consumer_config);
		
		// 署名方式はHMAC-SHA1
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		// oauth_callback_urlパラメータをマージ
		$addional_params = array_merge(
			array('oauth_callback' => $consumer->callback_url),
			$addional_params);
		
		$request = OAuthRequest::from_consumer_and_token(
			$consumer,
			NULL, // RequestToken取得時は不要
			'GET',
			$provider->request_token_url,
			$addional_params);
		
		// 署名
		$request->sign_request($signature_method, $consumer, NULL);
		
		// HTTP通信
		list($header, $body) = OAuthWrapUtil::http($request);
		$header_token = strtok($header, "\r\n");
	
		// HTTP ステータスコードを確認
		$stat = explode(" ", $header_token);
		if ($stat[1] != "200")
		{
			return NULL;
		}
		
		parse_str($body, $results);
		
		return new OAuthToken($results['oauth_token'], $results['oauth_token_secret']);
	}
	
	/**
	 * AccessTokenを取得する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @param OAuthToken $req_token RequestToken/Secret
	 * @param string $verifier oauth_verifier
	 * @return OAuthToken
	 */
	public static function get_access_token($consumer_config, $req_token, $verifier)
	{
		// consumer
		$consumer = OAuthWrapUtil::buildConsumer($consumer_config);
		
		// service provider
		$provider = OAuthWrapUtil::buildProvider($consumer_config);
		
		// 署名方式はHMAC-SHA1
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		// oauth_verifierパラメータをマージ
		$params = array(
			'oauth_verifier' => $verifier
		);
		
		$request = OAuthRequest::from_consumer_and_token(
			$consumer,
			$req_token,
			'GET',
			$provider->access_token_url,
			$params);
		
		// 署名
	   $request->sign_request($signature_method, $consumer, $req_token);
		
		// HTTP通信
		list($header, $body) = OAuthWrapUtil::http($request);
	   $header_token = strtok($header, "\r\n");
	
		// HTTP ステータスコードを確認
		$stat = explode(" ", $header_token);
		if ($stat[1] != "200")
		{
			return NULL;
		}
		
		parse_str($body, $results);
		
		return new OAuthToken($results['oauth_token'], $results['oauth_token_secret']);
	}
	
	/**
	 * リクエストを実行する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @param OAuthToken $access_token AccessToken/Secret
	 * @param string $method POST/GET
	 * @param string $url URL
	 * @param array $params パラメータ
	 * @return HttpResponse
	 */
	public static function invoke($consumer_config, $access_token, $method, $url, $params=array())
	{
		// consumer
		$consumer = OAuthWrapUtil::buildConsumer($consumer_config);
		
		// 署名方式はHMAC-SHA1
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		$redirect_limit = 10;
		$access_url = $url;
		
		while ($redirect_limit > 0)
		{
			$request = OAuthRequest::from_consumer_and_token(
				$consumer,
				$access_token,
				$method,
				$access_url,
				$params);
			
			// 署名
			$request->sign_request($signature_method, $consumer, $access_token);
			
			list($header, $body) = OAuthWrapUtil::http($request, $params);
			
			// HTTP ステータスコードを確認
			$stat = explode(" ", $header);
			if ($stat[1] == "200")
			{
				return $body;
			}
			else if ($stat[1] == "301" || $stat[1] == "302" || $stat[1] = "303" || $stat[1] == "307")
			{
				$redirect_limit = $redirect_limit - 1;
				
				$header_token = strtok($header, "\r\n");
				
				while ($header_token !== false)
				{
					$tmp_line = $header_token;
					if (strpos($tmp_line, "Location:", 0) === 0)
					{
						$access_url = substr($tmp_line, strpos($tmp_line, "http"));
						break;
					}
					$header_token = strtok("\r\n");
				}
			}
			else
			{
				break;
			}
		}
		
		return NULL;
	}
	
	/**
	 * リクエストオブジェクトを作成する。
	 * 
	 * @param string $method
	 * @param string $url
	 * @param array $parameters
	 * @return OAuthRequest
	 */
	public static function new_request_message($consumer_config, $access_token, $method, $url, $parameters=array())
	{
		// consumer
		$consumer = OAuthWrapUtil::buildConsumer($consumer_config);
		
		// 署名方式はHMAC-SHA1
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		
		$request = OAuthRequest::from_consumer_and_token(
			$consumer,
			$access_token,
			$method,
			$url,
			$parameters);
		
		// 署名
		$request->sign_request($signature_method, $consumer, $access_token);
		
		return $request;
	}
	
	/**
	 * ServiceProviderを作成する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @return ServiceProvider OAuth Provider
	 */
	private static function buildProvider($consumer_config)
	{
		// service provider info
		$req_token_url = $consumer_config['REQUEST_TOKEN_URL'];
		$user_auth_url = $consumer_config['USER_AUTHORIZATION_URL'];
		$access_token_url = $consumer_config['ACCESS_TOKEN_URL'];
		
		$provider = new ServiceProvider($req_token_url, $user_auth_url, $access_token_url);
		
		return $provider;
	}
	
	/**
	 * OAuthConsumerを作成する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @return OAuthConsumer
	 */
	private static function buildConsumer($consumer_config)
	{
		// consumer info
		$consumer_key = $consumer_config['CONSUMER_KEY'];
		$consumer_secret = $consumer_config['CONSUMER_SECRET'];
		$consumer_callback = $consumer_config['CONSUMER_CALLBACK_URL'];
		
		$consumer = new OAuthConsumer($consumer_key, $consumer_secret, $consumer_callback);
		
		return $consumer;
	}
	
	/**
	 * User Authorization URLを取得する。
	 * 
	 * @param array $consumer_config Consumer設定
	 * @return User Authorization URL
	 * @throws IOException
	 */
	public static function get_user_authorization_url($consumer_config)
	{
		$provider = OAuthWrapUtil::buildProvider($consumer_config);
		return $provider->user_authorization_url;
	}
	
	/**
	 * HTTP通信を行う。
	 * 
	 * @param OAuthRequest $request リクエストオブジェクト
	 * @param array $body_params POSTのBODYに指定するパラメータ
	 * @return HttpResponse
	 */
	private static function http($request, $body_params=array())
	{
		// cURLリソースの生成
		$ch = curl_init();
		// Locationヘッダは無視
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		// サーバ証明書の検証を行わない
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		// レスポンスを文字列として取得する設定
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// (出力結果に)ヘッダを含める
		curl_setopt($ch, CURLOPT_HEADER, true);
		
		if (strcasecmp($request->get_normalized_http_method(), 'POST') == 0)
		{
			// POST通信
			curl_setopt($ch, CURLOPT_POST, true);
			// URLを指定
			curl_setopt($ch, CURLOPT_URL, $request->get_normalized_http_url());
			// リクエストヘッダを設定
			curl_setopt($ch, CURLOPT_HTTPHEADER, array($request->to_header()));
			// リクエストパラメータを設定
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body_params);
		}
		else
		{
			// URLを指定
			curl_setopt($ch, CURLOPT_URL, $request->to_url());
		}
		
		// 実行
		$result = curl_exec($ch);
		
		// HTTPステータスコードを取得
		$http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		// close curl resource to free up system resources
		curl_close($ch);
		
		return explode("\r\n\r\n", $result, 2);
	}
}
?>
