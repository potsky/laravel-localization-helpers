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
        '%APP/helpers',
        '%APP/views' ,
        '%APP/controllers',
    ),


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
	    'trans' => array(
	        '@trans\(\s*(\'.*\')\s*\)@U',
	        '@trans\(\s*(".*")\s*\)@U',
	    ),
	    'Lang::Get' => array(
	        '@Lang::Get\(\s*(\'.*\')\s*\)@U',
	        '@Lang::Get\(\s*(".*")\s*\)@U',
	    ),
	    'trans_choice' => array(
	        '@trans_choice\(\s*(\'.*\')\s*,.*\)@U',
	        '@trans_choice\(\s*(".*")\s*,.*\)@U',
	    ),
	    'Lang::choice' => array(
	        '@Lang::choice\(\s*(\'.*\')\s*,.*\)@U',
	        '@Lang::choice\(\s*(".*")\s*,.*\)@U',
	    ),
	)

);
