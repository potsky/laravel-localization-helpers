<?php

use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;

class MessageBagTests extends TestCase
{
	public function testMessageBag()
	{
		$messageBag = new MessageBag();
		$messageBag->writeInfo('  <blah>this is a line</blah>  ');
		$messageBag->writeLine('  <blah>this is a line</blah>  ');
		$messageBag->writeError('  <blah>this is a line</blah>  ');
		$messageBag->writeComment('  <blah>this is a line</blah>  ');
		$messageBag->writeQuestion('  <blah>this is a line</blah>  ');

		$messages = $messageBag->getMessages();

		$this->assertTrue( $messageBag->hasMessages() );
		$this->assertInternalType( 'array' , $messages );

		$message = current( $messages );
		$this->assertEquals( MessageBag::INFO , $messageBag->getMessageType( $message ) );
		$this->assertEquals( 'this is a line' , $messageBag->getMessage( $message ) );

		$message = next( $messages );
		$this->assertEquals( MessageBag::LINE , $messageBag->getMessageType( $message ) );
		$this->assertEquals( 'this is a line' , $messageBag->getMessage( $message ) );

		$message = next( $messages );
		$this->assertEquals( MessageBag::ERROR , $messageBag->getMessageType( $message ) );
		$this->assertEquals( 'this is a line' , $messageBag->getMessage( $message ) );

		$message = next( $messages );
		$this->assertEquals( MessageBag::COMMENT , $messageBag->getMessageType( $message ) );
		$this->assertEquals( 'this is a line' , $messageBag->getMessage( $message ) );

		$message = next( $messages );
		$this->assertEquals( MessageBag::QUESTION , $messageBag->getMessageType( $message ) );
		$this->assertEquals( 'this is a line' , $messageBag->getMessage( $message ) );

		$messageBag->deleteMessages();
		$this->assertFalse( $messageBag->hasMessages() );
	}
}
