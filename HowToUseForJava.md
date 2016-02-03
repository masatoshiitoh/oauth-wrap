# Overvivew for Java.

# You need #

Download source code and libraries from repository(svn/trunk/java).


# Details #

## Overview ##

Please read svn/trunk/java/src/com/tomokey/oauth/HowToUse.java.

This library could help you to implement OAuth related code with Java. "HowToUse.java" show you what you can do and you need.

## How to Get Request Token ##

First of all, you need write consumer.properties file that contains information of your application(consumer) and OAuth service provider(like Google).

The sample consumer.properties file is provided (you can find it at svn/trunk/java/conf/consumer.properties). Note that : the properties file must be in classpath.

Now, you are ready to get request token. It is very easy to implement as below.

```
// define name indicates which consumer is used
final String tgConsumer = "tomokey";

//
// Build optional parameter value.
// For example, to use google data api you must set a parameter named "scope"
// that limits what kind of API you can use.
//
// ref)http://code.google.com/intl/ja/apis/accounts/docs/OAuth_ref.html#RequestToken
// ref)http://code.google.com/intl/ja/apis/gdata/faq.html#AuthScopes
//
List<Parameter> additionalParam = OAuth.newList("scope", "https://www.google.com/calendar/feeds/");
		
//
// execute
//
Token requestToken = OAuthWrapUtil.getRequestToken(tgConsumer, additionalParam);
		
System.out.println(requestToken);
```

For more detail, please read svn/trunk/java/src/com/tomokey/oauth/HowToUse.java.