<?php

$a = trans( 'message.lemma' );
$b = trans( 'message.lemma.child' );
$c = trans( 'message.lemma.child 1' , array( 'name' => 'potsky' ) );
$d = trans( 'message.lemma.child 2' , [ 'name' => 'potsky' ] );
$e = trans_choice( 'message.lemma.child 3' , 0 , array( 'name' => 'potsky' , 'count' => 0 ) );
$f = trans_choice( 'message.lemma.child 4' , 2 , [ 'name' => 'potsky', 'count' => 2 ] );

$la = Lang::get( 'message.lemma l' );
$lb = Lang::get( 'message.lemma l.child' );
$lc = Lang::get( 'message.lemma.child l1' , array( 'name' => 'potsky' ) );
$ld = Lang::get( 'message.lemma.child l2' , [ 'name' => 'potsky' ] );
$le = Lang::choice( 'message.lemma.child l3' , 0 , array( 'name' => 'potsky' , 'count' => 0 ) );
$lf = Lang::choice( 'message.lemma.child l4' , 2 , [ 'name' => 'potsky', 'count' => 2 ] );