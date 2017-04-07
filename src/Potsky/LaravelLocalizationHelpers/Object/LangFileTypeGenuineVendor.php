<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileTypeGenuineVendor extends LangFileTypeGenuine
{
	/**
	 * LangFileTypeGenuineVendor constructor.
	 *
	 * @param string $lang
	 */
	public function __construct( $lang )
	{
		parent::__construct( $lang );

		$this->setTypeVendor( true );
	}

}