<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

use MicrosoftTranslator\Client;

class TranslatorMicrosoft implements TranslatorInterface
{
	protected $bingTranslator;

	protected $default_language;

	/**
	 * @param array $config
	 *
	 * @throws \Potsky\LaravelLocalizationHelpers\Factory\Exception
	 */
	public function __construct( $config )
	{
		if ( ( isset( $config[ 'client_id' ] ) ) && ( ! is_null( $config[ 'client_id' ] ) ) )
		{
			$client_id = $config[ 'client_id' ];
		}
		else
		{
			$env = ( isset( $config[ 'env_name_client_id' ] ) ) ? $config[ 'env_name_client_id' ] : 'LLH_MICROSOFT_TRANSLATOR_CLIENT_ID';

			if ( ( $client_id = getenv( $env ) ) === false )
			{
				throw new Exception( 'Please provide a client_id for Microsoft Bing Translator service' );
			}
		}

		if ( ( isset( $config[ 'client_secret' ] ) ) && ( ! is_null( $config[ 'client_secret' ] ) ) )
		{
			$client_secret = $config[ 'client_secret' ];
		}
		else
		{
			$env = ( isset( $config[ 'env_name_client_secret' ] ) ) ? $config[ 'env_name_client_secret' ] : 'LLH_MICROSOFT_TRANSLATOR_CLIENT_SECRET';

			if ( ( $client_secret = getenv( $env ) ) === false )
			{
				throw new Exception( 'Please provide a client_secret for Microsoft Bing Translator service' );
			}
		}

		if ( ( isset( $config[ 'default_language' ] ) ) && ( ! is_null( $config[ 'default_language' ] ) ) )
		{
			$this->default_language = $config[ 'default_language' ];
		}

		$this->bingTranslator = new Client( array(
			//'log_level'         => Logger::LEVEL_DEBUG ,
			'api_client_id'     => $client_id ,
			'api_client_secret' => $client_secret ,
		) );
	}

	/**
	 * @param string $word     Sentence or word to translate
	 * @param string $toLang   Target language
	 * @param null   $fromLang Source language (if set to null, translator will try to guess)
	 *
	 * @return null|string The translated sentence or null if an error occurs
	 * @throws \MicrosoftTranslator\Exception
	 */
	public function translate( $word , $toLang , $fromLang = null )
	{
		try
		{
			if ( ( is_null( $fromLang ) ) && ( ! is_null( $this->default_language ) ) )
			{
				$fromLang = $this->default_language;
			}

			$translation = $this->bingTranslator->translate( $word , $toLang , $fromLang );

			return $translation->getBody();
		}
		catch ( \MicrosoftTranslator\Exception $e )
		{
			if ( ! ( strpos( $e->getMessage() , 'Unable to generate a new access token' ) === false ) )
			{
				throw $e;
			}
		}

		return null;
	}
}


