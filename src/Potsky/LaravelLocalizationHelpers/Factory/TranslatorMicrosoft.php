<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

class TranslatorMicrosoft implements TranslatorInterface
{
	protected $bingTranslator;

	/**
	 * @param array $config
	 */
	public function __construct( $config )
	{
		if ( isset( $config[ 'api_key' ] ) )
		{
			$apiKey = $config[ 'api_key' ];
		}
		else if ( ( $apiKey = getenv( 'LLH_MICROSOFT_TRANSLATOR_API_KEY' ) ) === false )
		{
			throw new Exception( 'Please provide an API key for Microsoft Bing Translator service' );
		}
	}

	/**
	 * @param string $word     Sentence or word to translate
	 * @param string $toLang   Target language
	 * @param null   $fromLang Source language (if set to null, translator will try to guess)
	 *
	 * @return string|null     The translated sentence or null if an error occurs
	 */
	public function translate( $word , $toLang , $fromLang = null )
	{
		try
		{
			$translation = $this->bingTranslator->translate( $word , $fromLang , $toLang );

			if ( is_string( $translation ) )
			{
				return $translation;
			}

			return null;
		}
		catch ( Exception $e )
		{
			return null;
		}
	}
}


