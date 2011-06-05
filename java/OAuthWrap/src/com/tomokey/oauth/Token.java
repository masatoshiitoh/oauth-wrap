package com.tomokey.oauth;

public final class Token
{
	public String token;
	public String tokenSecret;
	
	public Token(String aToken, String aTokenSecret)
	{
		token = aToken;
		tokenSecret = aTokenSecret;
	}
	
	@Override
	public String toString()
	{
		return "[token:" + token + ",secret:" + tokenSecret + "]";
	}
}
