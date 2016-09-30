<?php

$i = trans_choice(
	'message.multiline1' ,
	0 ,
	array( 'name' => 'potsky' , 'count' => 0 )
);

$i = Lang::choice(
	'message.multiline2' ,
	0 ,
	array( 'name' => 'potsky' , 'count' => 0 )
);

$i = trans(
	'message.multiline3',
	array( 'dumb' => 'dumber' )
);

$i = trans(
	'message.multiline4'
);

$i = trans(
	'message.multiline5',
);

$i = Lang::get(
	'message.multiline6',
	array( 'dumb' => 'dumber' )
);

$i = Lang::get(
	'message.multiline7'
);

$i = Lang::get(
	'message.multiline8',
);

