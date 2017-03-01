<?php

// This lemma will not be included because it has no family as message, validation, etc... for example
trans( 'OUPS lemma without family OUPS' );

// This lemma will not be included because it has $ or :: in lemma
trans( 'message.Char $ can be used for auto-generated lemma' );
trans( 'message.Double char :: is reserved for packages' );

// This family is ignored
trans( 'validation.lemma' );

// This lemma will never be set as obsolete
trans( 'message.fields.lemma' );

// function style
$b = trans( 'message.lemma.child' );
$c = trans( 'message.lemma.child 1' , array( 'name' => 'potsky' ) );
$d = trans( 'message.lemma.child 2' , [ 'name' => 'potsky' ] );
$e = trans_choice( 'message.lemma.child 3' , 0 , array( 'name' => 'potsky' , 'count' => 0 ) );
$f = trans_choice( 'message.lemma.child 4' , 2 , [ 'name' => 'potsky' , 'count' => 2 ] );

// method style
$la = Lang::get( 'message.lemma l' );
$lb = Lang::get( 'message.lemma l.child' );
$lc = Lang::get( 'message.lemma.child l1' , array( 'name' => 'potsky' ) );
$ld = Lang::get( 'message.lemma.child l2' , [ 'name' => 'potsky' ] );
$le = Lang::choice( 'message.lemma.child l3' , 0 , array( 'name' => 'potsky' , 'count' => 0 ) );
$lf = Lang::choice( 'message.lemma.child l4' , 2 , [ 'name' => 'potsky' , 'count' => 2 ] );

// check translation
trans( 'message.dog' );
trans( 'message.child.dog' );
