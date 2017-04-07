<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileGenuine extends LangFileAbstract
{
	/**
	 * LangFileGenuine constructor.
	 *
	 * @param string $dir
	 * @param string $lang
	 * @param string $family
	 */
	public function __construct( $dir , $lang , $family )
	{
		parent::__construct( $dir , $lang );

		$this->setFilePath( $dir . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php' )
			 ->setFamily( $family )
			 ->setTypeJson( false )
			 ->setTypeVendor( false );
	}

}