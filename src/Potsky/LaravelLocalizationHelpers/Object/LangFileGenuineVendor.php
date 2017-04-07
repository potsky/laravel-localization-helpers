<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

class LangFileGenuineVendor extends LangFileGenuine
{
	/**
	 * LangFileGenuineVendor constructor.
	 *
	 * @param string $dir
	 * @param string $lang
	 * @param string $family
	 */
	public function __construct( $dir , $lang , $family , $package )
	{
		parent::__construct( $dir , $lang , $family );

		$this->setFilePath( $dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php' )
			 ->setPackage( $package )
			 ->setTypeVendor( true );
	}

}

