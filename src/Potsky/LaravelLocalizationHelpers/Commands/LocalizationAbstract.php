<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBagInterface;

abstract class LocalizationAbstract extends Command implements MessageBagInterface
{
	const SUCCESS = 0;
	const ERROR   = 1;

	/**
	 * Config repository.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $configRepository;

	/**
	 * The localization manager
	 *
	 * @var Localization
	 */
	protected $manager;

	/**
	 * Should commands display something
	 *
	 * @var  boolean
	 */
	protected $display = true;

	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		// Inject this command just to have access to writeLine, writeError, etc... methods
		$this->manager = new Localization( $this );

		parent::__construct();
	}

	/**
	 * Display console message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeLine( $s )
	{
		if ( $this->display )
		{
			parent::line( $s );
		}
	}

	/**
	 * Display console message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeInfo( $s )
	{
		if ( $this->display )
		{
			parent::info( $s );
		}
	}

	/**
	 * Display console message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeComment( $s )
	{
		if ( $this->display )
		{
			parent::comment( $s );
		}
	}

	/**
	 * Display console message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function writeQuestion( $s )
	{
		if ( $this->display )
		{
			parent::question( $s );
		}
	}

	/**
	 * Display console message
	 *
	 * @param   string $s the message to display
	 *
	 * @return  void
	 */
	public function writeError( $s )
	{
		if ( $this->display )
		{
			parent::error( $s );
		}
	}
}
