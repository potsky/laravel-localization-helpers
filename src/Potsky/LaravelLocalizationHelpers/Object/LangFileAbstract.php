<?php

namespace Potsky\LaravelLocalizationHelpers\Object;

abstract class LangFileAbstract
{
	protected $typeVendor;

	protected $typeJson;

	protected $filePath;

	protected $shortFilePath;

	protected $lang;

	protected $dir;

	protected $family;

	protected $package;

	/**
	 * LangFileAbstract constructor.
	 *
	 * @param string $dir
	 * @param string $lang
	 */
	public function __construct( $dir , $lang )
	{
		$this->setDir( $dir )->setLang( $lang );
	}

	/**
	 * @param boolean $typeVendor
	 *
	 * @return LangFileAbstract
	 */
	public function setTypeVendor( $typeVendor )
	{
		$this->typeVendor = $typeVendor;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getTypeVendor()
	{
		return $this->typeVendor;
	}

	/**
	 * @param boolean $typeJson
	 *
	 * @return LangFileAbstract
	 */
	public function setTypeJson( $typeJson )
	{
		$this->typeJson = $typeJson;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getTypeJson()
	{
		return $this->typeJson;
	}

	/**
	 * @param string $filePath
	 *
	 * @return LangFileAbstract
	 */
	public function setFilePath( $filePath )
	{
		$this->filePath      = $filePath;
		$this->shortFilePath = str_replace( base_path() , '' , $filePath );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}


	/**
	 * @return string
	 */
	public function getShortFilePath()
	{
		return $this->shortFilePath;
	}

	/**
	 * @param mixed $lang
	 *
	 * @return LangFileAbstract
	 */
	public function setLang( $lang )
	{
		$this->lang = $lang;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @return mixed
	 */
	public function getDir()
	{
		return $this->dir;
	}

	/**
	 * @param mixed $dir
	 *
	 * @return LangFileAbstract
	 */
	public function setDir( $dir )
	{
		$this->dir = $dir;

		return $this;
	}

	/**
	 * @param mixed $family
	 *
	 * @return LangFileAbstract
	 */
	public function setFamily( $family )
	{
		$this->family = $family;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFamily()
	{
		return $this->family;
	}

	/**
	 * @param mixed $package
	 *
	 * @return LangFileAbstract
	 */
	public function setPackage( $package )
	{
		$this->package = $package;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * @return string
	 */
	public function getFileFolderPath()
	{
		return dirname( $this->filePath );
	}


	/**
	 * @return bool
	 */
	public function isFolderWritable()
	{
		return is_writable( dirname( $this->getFilePath() ) );
	}

	/**
	 * @return bool
	 */
	public function ensureFolder()
	{
		$dir = dirname( $this->getFilePath() );

		if ( is_dir( $dir ) )
		{
			return true;
		}

		return mkdir( $dir );
	}

	/**
	 * @return bool
	 */
	public function fileExists()
	{
		return file_exists( $this->getFilePath() );
	}

	/**
	 * @return bool
	 */
	public function touch()
	{
		return touch( $this->getFilePath() );
	}

	/**
	 * @return bool
	 */
	public function isReadable()
	{
		return is_readable( $this->getFilePath() );
	}

	/**
	 * @return bool
	 */
	public function isWritable()
	{
		return is_writable( $this->getFilePath() );
	}

	/**
	 * @return mixed
	 */
	public function load()
	{
		if ( $this->fileExists() )
		{
			/** @noinspection PhpIncludeInspection */
			return include( $this->filePath );
		}
		else
		{
			return null;
		}
	}

}
