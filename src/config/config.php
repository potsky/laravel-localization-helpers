<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Folders where to search for lemmas
	|--------------------------------------------------------------------------
	|
	| Localization::Missing will search recursively for lemmas in all php files
	| included in these folders. You can use these keywords :
	| - %APP     : the laravel app folder of your project
	| - %BASE    : the laravel base folder of your project
	| - %PUBLIC  : the laravel public folder of your project
	| - %STORAGE : the laravel storage folder of your project
	| No error or exception is thrown when a folder does not exist.
	|
	*/
	'folders' => array(
		'%APP/Potsky' ,
		'%APP/views' ,
		'%APP/controllers' ,
	) ,


	/*
	|--------------------------------------------------------------------------
	| Lang file to ignore
	|--------------------------------------------------------------------------
	|
	| These lang files will not be written
	|
	*/
	'ignore_lang_files' => array(
		'validation' ,
	) ,


	/*
	|--------------------------------------------------------------------------
	| Lang folder
	|--------------------------------------------------------------------------
	|
	| You can overwrite where is located your lang folder
	| If null or missing, Localization::Missing will search :
    | - first in app_path() . DIRECTORY_SEPARATOR . 'lang',
    | - then  in base_path() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang',
	|
	*/
	'lang_folder_path' => null ,


	/*
	|--------------------------------------------------------------------------
	| Methods or functions to search for
	|--------------------------------------------------------------------------
	|
	| Localization::Missing will search lemmas by using these regular expressions
	| Several regular expressions can be used for a single method or function.
	|
	*/
	'trans_methods' => array(
		'trans'        => array(
			'@trans\(\s*(\'.*\')\s*(,.*)*\)@U' ,
			'@trans\(\s*(".*")\s*(,.*)*\)@U' ,
		) ,
		'Lang::Get'    => array(
			'@Lang::Get\(\s*(\'.*\')\s*(,.*)*\)@U' ,
			'@Lang::Get\(\s*(".*")\s*(,.*)*\)@U' ,
			'@Lang::get\(\s*(\'.*\')\s*(,.*)*\)@U' ,
			'@Lang::get\(\s*(".*")\s*(,.*)*\)@U' ,
		) ,
		'trans_choice' => array(
			'@trans_choice\(\s*(\'.*\')\s*,.*\)@U' ,
			'@trans_choice\(\s*(".*")\s*,.*\)@U' ,
		) ,
		'Lang::choice' => array(
			'@Lang::choice\(\s*(\'.*\')\s*,.*\)@U' ,
			'@Lang::choice\(\s*(".*")\s*,.*\)@U' ,
		) ,
		'@lang'        => array(
			'@\@lang\(\s*(\'.*\')\s*(,.*)*\)@U' ,
			'@\@lang\(\s*(".*")\s*(,.*)*\)@U' ,
		) ,
		'@choice'      => array(
			'@\@choice\(\s*(\'.*\')\s*,.*\)@U' ,
			'@\@choice\(\s*(".*")\s*,.*\)@U' ,
		) ,
	) ,


	/*
	|--------------------------------------------------------------------------
	| Keywords for obsolete check
	|--------------------------------------------------------------------------
	|
	| Localization::Missing will search lemmas in existing lang files.
	| Then it searches in all PHP source files.
	| When using dynamic or auto-generated lemmas, you must tell Localization::Missing
	| that there are dynamic because it cannot guess them.
	|
	| Example :
	|   - in PHP blade code : <span>{{{ trans( "message.user.dynamo.$s" ) }}}</span>
	|   - in lang/en.message.php :
	|     - 'user' => array(
	|         'dynamo' => array(
	|           'lastname'  => 'Family name',
	|           'firstname' => 'Name',
	|           'email'     => 'Email address',
	|           ...
	|   Then you can define in this parameter value dynamo for example so that
	|   Localization::Missing will not exclude lastname, firstname and email from
	|   translation files.
	|
	*/
	'never_obsolete_keys' => array(
		'dynamic' ,
		'fields' ,
	) ,


	/*
	|--------------------------------------------------------------------------
	| Editor
	|--------------------------------------------------------------------------
	|
	| when using option editor, package will use this command to open your files
	|
	*/
	'editor_command_line' => '/Applications/Sublime\\ Text.app/Contents/SharedSupport/bin/subl' ,


	/*
	|--------------------------------------------------------------------------
	| Translator
	|--------------------------------------------------------------------------
	|
	| Use the Microsoft translator by default. This is the only translator available now
	|
	*/
	'translator' => 'Microsoft' ,


	/*
	|--------------------------------------------------------------------------
	| Translators configuration
	|--------------------------------------------------------------------------
	|
	| Microsoft
	|
	| #### default_language
	|
	| Set the default language used in your PHP code. If set to null, the translator
	| will try to guess it. The default language in your code is the language you use
	| in this PHP line for example :
	|
	| trans( 'message.This is a message in english' );
	|
	| Supported languages are : ar, bg, ca, cs, da, de, el, en, es, et, fa, fi, fr,
	| he, hi, ht, hu, id, it, ja, ko, lt, lv, ms, mww, nl, no, pl, pt, ro, ru, sk,
	| sl, sv, th, tr, uk, ur, vi, zh-CHS, zh-CHT
	|
	| #### client_id ans client_secret
	|
	| Package can automatically translate your lemma. Please create :
	| - You need to create an account on Microsoft Translation service
	|	https://datamarket.azure.com/dataset/bing/microsofttranslator
	| - Then you need to create an application to get a `client_id` and a `client_secret`
	|	https://datamarket.azure.com/developer/applications
	|
	| If you don't want to set these credentials here, set both to null and set both
	| environment parameters on your computer/server:
	| - LLH_MICROSOFT_TRANSLATOR_CLIENT_ID
	| - LLH_MICROSOFT_TRANSLATOR_CLIENT_SECRET
	|
	*/
	'translators' => array(
		'Microsoft' => array(
			'default_language' => null ,
			'client_id'        => null ,
			'client_secret'    => null ,
		),
	) ,

);
