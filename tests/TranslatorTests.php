<?php

use Potsky\LaravelLocalizationHelpers\Factory\Translator;

class TranslatorTests extends TestCase
{
    /**
     *
     */
    public function testInjection()
    {
        $translator = new Translator('Microsoft', [
            'client_key' => 'xxx',
        ]);
        $this->assertTrue($translator instanceof \Potsky\LaravelLocalizationHelpers\Factory\Translator);
        $this->assertTrue($translator->getTranslator() instanceof \Potsky\LaravelLocalizationHelpers\Factory\TranslatorMicrosoft);
    }

    /**
     * Microsoft credentials are set in environment on my computer
     * export LLH_MICROSOFT_TRANSLATOR_CLIENT_KEY="..."
     */
    public function testRealCase()
    {
        $translator = new Translator('Microsoft', []);
        $this->assertEquals('Stuhl', $translator->translate('chair', 'de'));
        $this->assertEquals('Fleisch', $translator->translate('chair', 'de', 'fr'));
    }

    /**
     *
     */
    public function testNoTranslation()
    {
        $translator = new Translator('Microsoft', []);
        $this->assertNull($translator->translate('', ''));
    }

    /**
     *
     */
    public function testUnknownLang()
    {
        $translator = new Translator('Microsoft', []);
        $this->assertNull($translator->translate('dog', 'zz'));
    }

    /**
     *
     */
    public function testRealCaseWithDefaultLanguage()
    {
        $translator = new Translator('Microsoft', ['default_language' => 'fr']);
        $this->assertEquals('Fleisch', $translator->translate('chair', 'de'));
    }

    /**
     *
     */
    public function testNoCredentialsClientKey()
    {
        $this->expectException('\\Potsky\\LaravelLocalizationHelpers\\Factory\\Exception');
        new Translator('Microsoft', [
            'env_name_client_key' => 'this_env_does_not_exist',
        ]);
    }

}
