<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

interface MessageBagInterface
{
	/**
	 * Add a simple message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeLine( $s );

	/**
	 * Add an info message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeInfo( $s );

	/**
	 * Add a comment message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeComment( $s );

	/**
	 * Add a question message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeQuestion( $s );

	/**
	 * Add an error message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeError( $s );
}