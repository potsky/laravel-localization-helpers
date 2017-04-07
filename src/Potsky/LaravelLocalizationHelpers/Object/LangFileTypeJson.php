<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileTypeJson extends LangFileTypeAbstract
{
	/**
	 * LangFileTypeJson constructor.
	 *
	 * @param string $lang
	 */
	public function __construct( $lang )
	{
		parent::__construct( $lang );

		$this->setTypeJson( true )->setTypeVendor( false );
	}

}