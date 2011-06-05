package com.tomokey.oauth;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.Collections;
import java.util.List;

import net.oauth.OAuth;
import net.oauth.OAuthException;
import net.oauth.OAuth.Parameter;

public class HowToUse
{
	public static void main(String[] args) throws IOException, OAuthException, URISyntaxException
	{
		HowToUse instance = new HowToUse();
		
		instance.howToGetRequestToken();
		
//		instance.howToGetAccessToken(reqToken, reqTokenSecret, verifier);
		
//		instance.howToAccessApi(accessToken, accessTokenSecret);
	}
	
	/**
	 * リクエストトークンの取得方法サンプル
	 * @throws IOException 
	 */
	private void howToGetRequestToken() throws IOException
	{
		// 対象consumer設定名
		final String tgConsumer = "tomokey";
		
		//
		// Google Data APIを利用する場合には"scope"パラメータが必須
		// 参考：http://code.google.com/intl/ja/apis/accounts/docs/OAuth_ref.html#RequestToken
		// 参考：http://code.google.com/intl/ja/apis/gdata/faq.html#AuthScopes
		//
		List<Parameter> additionalParam = OAuth.newList("scope", "https://www.google.com/calendar/feeds/");
		
		//
		// request tokenを取得
		//
		Token requestToken = OAuthWrapUtil.getRequestToken(tgConsumer, additionalParam);
		
		System.out.println(requestToken);
		
		//
		// user authorizatio url
		//
		String authUrl = OAuthWrapUtil.getUserAuthorizationURL(tgConsumer);
		
		System.out.println(OAuth.addParameters(authUrl, OAuth.OAUTH_TOKEN, requestToken.token));
	}
	
	/**
	 * アクセストークンの取得方法サンプル
	 * @param reqToken リクエストトークン
	 * @param reqTokenSecret リクエストトークンシークレット
	 * @param verifier ベリファイア
	 * @throws IOException
	 */
	private void howToGetAccessToken(String reqToken, String reqTokenSecret, String verifier) throws IOException
	{
		// 対象consumer設定名
		final String tgConsumer = "tomokey";
		
		//
		// access tokenを取得
		//
		Token accessToken = OAuthWrapUtil.getAccessToken(tgConsumer, reqToken, reqTokenSecret, verifier);
		
		System.out.println(accessToken);
	}
	
	/**
	 * APIアクセスのサンプル
	 * @param accessToken アクセストークン
	 * @param accessTokenSecret アクセストークンシークレット
	 * @throws IOException
	 */
	private void howToAccessApi(String accessToken, String accessTokenSecret) throws IOException
	{
		// 対象consumer設定名
		final String tgConsumer = "tomokey";
		
		// APIのURL
		String url = "https://www.google.com/calendar/feeds/default/allcalendars/full";
		
		// パラメータ
		List<Parameter> params = Collections.<Parameter>emptyList();
		
		//
		// アクセス
		//
		Token token = new Token(accessToken, accessTokenSecret);
		String response = OAuthWrapUtil.invoke(tgConsumer, token, "GET", url, params);
		
		System.out.println(response);
	}
}
