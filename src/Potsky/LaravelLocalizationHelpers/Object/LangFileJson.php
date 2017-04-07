<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileJson extends LangFileAbstract
{
	/**
	 * LangFileJson constructor.
	 *
	 * @param        $dir
	 * @param string $lang
	 */
	public function __construct( $dir , $lang )
	{
		parent::__construct( $dir , $lang );

		$this->setFilePath( $dir . DIRECTORY_SEPARATOR . $lang . '.json' )
			 ->setTypeJson( true )
			 ->setTypeVendor( false );
	}

}