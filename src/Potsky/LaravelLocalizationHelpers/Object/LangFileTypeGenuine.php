<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileTypeGenuine extends LangFileTypeAbstract
{
	/**
	 * LangFileTypeGenuine constructor.
	 *
	 * @param string $lang
	 */
	public function __construct( $lang )
	{
		parent::__construct( $lang );

		$this->setTypeJson( false )->setTypeVendor( false );
	}

}