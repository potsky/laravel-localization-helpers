<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

use MicrosoftTranslator\Client;

class TranslatorMicrosoft implements TranslatorInterface
{
    protected $msTranslator;

    protected $default_language;

    /**
     * @param array $config
     *
     * @throws \Potsky\LaravelLocalizationHelpers\Factory\Exception
     */
    public function __construct($config)
    {
        if ((isset($config['client_key'])) && (! is_null($config['client_key']))) {
            $client_key = $config['client_key'];
        } else {
            $env = (isset($config['env_name_client_key'])) ? $config['env_name_client_key'] : 'LLH_MICROSOFT_TRANSLATOR_CLIENT_KEY';

            if (($client_key = getenv($env)) === false) {
                throw new Exception('Please provide a client_key for Microsoft Translator service');
            }
        }

        if ((isset($config['default_language'])) && (! is_null($config['default_language']))) {
            $this->default_language = $config['default_language'];
        }

        $this->msTranslator = new Client([
            //'log_level'         => Logger::LEVEL_DEBUG ,
            'api_client_key' => $client_key,
        ]);
    }

    /**
     * @param string $word   Sentence or word to translate
     * @param string $toLang Target language
     * @param null $fromLang Source language (if set to null, translator will try to guess)
     *
     * @return null|string The translated sentence or null if an error occurs
     * @throws \MicrosoftTranslator\Exception
     */
    public function translate($word, $toLang, $fromLang = null)
    {
        try {
            if ((is_null($fromLang)) && (! is_null($this->default_language))) {
                $fromLang = $this->default_language;
            }

            $translation = $this->msTranslator->translate($word, $toLang, $fromLang);

            return $translation->getBody();
        } catch (\MicrosoftTranslator\Exception $e) {
            if (! (strpos($e->getMessage(), 'Unable to generate a new access token') === false)) {
                throw $e;
            }
        }

        return null;
    }
}


