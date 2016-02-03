# Overvivew for Php.

# You need #

Download source code and libraries from repository(svn/trunk/php).


# Details #

## Overview ##

Please read svn/trunk/php/src/tomokey/conf/Consumers.php.

This library could help you to implement OAuth related code with PHP. "HowToUse.php" show you what you can do and you need.

## How to Get Request Token ##

First of all, you need write Consumers.php file that contains information of your application(consumer) and OAuth service provider(like Google).

The sample Consumers.php file is provided (you can find it at svn/trunk/php/src/tomokey/conf/Consumers.php).

Now, you are ready to get request token. It is very easy to implement as below.

```
// define name indicates which consumer is used
$this->target_consumer = $TOMOKEY;

//
// Build optional parameter value.
// For example, to use google data api you must set a parameter named "scope"
// that limits what kind of API you can use.
//
// ref)http://code.google.com/intl/ja/apis/accounts/docs/OAuth_ref.html#RequestToken
// ref)http://code.google.com/intl/ja/apis/gdata/faq.html#AuthScopes
//
$additional_param = array('scope' => 'https://www.google.com/calendar/feeds/');
		
//
// execute
//
$req_token = OAuthWrapUtil::get_request_token($this->target_consumer, $additional_param);
		
print $req_token . "\n";
```

For more detail, please read svn/trunk/php/src/tomokey/script/HowToUse.php.