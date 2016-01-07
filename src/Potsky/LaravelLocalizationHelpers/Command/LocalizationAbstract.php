<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;

abstract class LocalizationAbstract extends Command
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
		$this->manager = new Localization();

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
