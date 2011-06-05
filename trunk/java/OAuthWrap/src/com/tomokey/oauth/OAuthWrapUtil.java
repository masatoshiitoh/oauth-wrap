package com.tomokey.oauth;

import java.io.IOException;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.Properties;

import net.oauth.ConsumerProperties;
import net.oauth.OAuth;
import net.oauth.OAuthAccessor;
import net.oauth.OAuthConsumer;
import net.oauth.OAuthException;
import net.oauth.OAuthMessage;
import net.oauth.OAuthProblemException;
import net.oauth.ParameterStyle;
import net.oauth.OAuth.Parameter;
import net.oauth.client.OAuthClient;
import net.oauth.client.httpclient4.HttpClient4;

public class OAuthWrapUtil
{
	/** インスタンス(Singleton) */
	private static OAuthWrapUtil instance;
	
	/** OAuth Consumer Pool */
	private ConsumerProperties consumers;
	
	
	/**
	 * コンストラクタ(Singleton)
	 * @throws IOException
	 */
	private OAuthWrapUtil() throws IOException
	{
		ClassLoader loader = ClassLoader.getSystemClassLoader();
		URL url = loader.getResource("consumer.properties");
		Properties prop = ConsumerProperties.getProperties(url);
		consumers = new ConsumerProperties(prop);
	}
	
	/**
	 * OAuthAccessorを作成する。
	 * 
	 * @param consumerName OAuth Consumerの設定名
	 * @return OAuthAccessor
	 * @throws IOException
	 */
	public static OAuthAccessor createAccessor(String consumerName) throws IOException
	{
		if (instance == null)
		{
			instance = new OAuthWrapUtil();
		}
		
		OAuthConsumer consumer = instance.consumers.getConsumer(consumerName);
		
		// OAuth accessor
		OAuthAccessor accessor = new OAuthAccessor(consumer);
		
		return accessor;
	}
	
	/**
	 * RequestTokenを取得する。
	 * 
	 * @param aConsumerName OAuth Consumerの設定名
	 * @param addtionalParams 追加パラメータ
	 * @return RequestToken, RequestTokenSecret
	 * @throws IOException
	 */
	public static Token getRequestToken(String aConsumerName, List<Parameter> addtionalParams) throws IOException
	{
		OAuthClient client = new OAuthClient(new HttpClient4());
		OAuthAccessor accessor = OAuthWrapUtil.createAccessor(aConsumerName);

		try
		{
			// callback urlを追加
			List<Parameter> params = new ArrayList<Parameter>(addtionalParams);
			params.add(new Parameter(OAuth.OAUTH_CALLBACK, accessor.consumer.callbackURL));
			
			client.getRequestToken(accessor, "GET", params);
			
			return new Token(accessor.requestToken, accessor.tokenSecret);
		}
		catch (Exception ignore)
		{
			return null;
		}
	}
	
	/**
	 * AccessTokenを取得する。
	 * 
	 * @param aConsumerName OAuth Consumerの設定名
	 * @param aRequestToken RequestToken
	 * @param aRequestTokenSecret RequestTokenSecret
	 * @param aVerifier Verifier
	 * @return AccessToken, AccessTokenSecret
	 * @throws IOException
	 */
	public static Token getAccessToken(String aConsumerName, String aRequestToken, String aRequestTokenSecret, String aVerifier) throws IOException
	{
		OAuthClient client = new OAuthClient(new HttpClient4());
		OAuthAccessor accessor = OAuthWrapUtil.createAccessor(aConsumerName);
		
		// 署名のキーに利用するためRequestTokenSecretを設定
		accessor.tokenSecret = aRequestTokenSecret;
		
		try
		{
			client.getAccessToken(accessor, null, OAuth.newList(OAuth.OAUTH_TOKEN, aRequestToken, OAuth.OAUTH_VERIFIER, aVerifier));
			
			return new Token(accessor.accessToken, accessor.tokenSecret);
		}
		catch (Exception ignore)
		{
			return null;
		}
	}
	
	/**
	 * リクエストを実行する。
	 * 
	 * @param aConsumerName OAuth Consumerの設定名
	 * @param anAccessToken AccessToken/Secret
	 * @param aMethod POST/GET
	 * @param anUrl URL
	 * @param aParams パラメータ
	 * @return HTTPレスポンス
	 * @throws IOException 
	 */
	public static String invoke(String aConsumerName, Token anAccessToken, String aMethod, String anUrl, List<Parameter> aParams) throws IOException
	{
		OAuthClient client = new OAuthClient(new HttpClient4());
		OAuthAccessor accessor = OAuthWrapUtil.createAccessor(aConsumerName);
		
		accessor.accessToken = anAccessToken.token;
		accessor.tokenSecret = anAccessToken.tokenSecret;
		
		int redirectLimit = 10;
		String url = anUrl;
		
		while (redirectLimit > 0)
		{
			try
			{
				OAuthMessage request = accessor.newRequestMessage(aMethod, url, aParams);
				OAuthMessage response = client.invoke(request, ParameterStyle.AUTHORIZATION_HEADER);
				
				return response.readBodyAsString();
			}
			catch (OAuthProblemException e)
			{
				if (e.getHttpStatusCode() == 301 || e.getHttpStatusCode() == 302 || e.getHttpStatusCode() == 303 || e.getHttpStatusCode() == 307)
				{
					url = (String)e.getParameters().get(OAuthProblemException.HTTP_LOCATION);
					redirectLimit--;
				}
				else
				{
					break;
				}
			}
			catch (Exception ignore)
			{
				break;
			}
		}
		
		return null;
	}
	
	/**
	 * User Authorization URLを取得する。
	 * 
	 * @param aConsumerName OAuth Consumerの設定名
	 * @return User Authorization URL
	 * @throws IOException
	 */
	public static String getUserAuthorizationURL(String aConsumerName) throws IOException
	{
		OAuthAccessor accessor = OAuthWrapUtil.createAccessor(aConsumerName);
		return accessor.consumer.serviceProvider.userAuthorizationURL;
	}
}
