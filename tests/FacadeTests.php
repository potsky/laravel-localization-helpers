<?php

use Illuminate\Foundation\AliasLoader;

class FacadeTests extends TestCase
{
    public function testFacade()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('LocalizationHelpers', 'Potsky\LaravelLocalizationHelpers\Facade\LocalizationHelpers');

        $this->expectException('Potsky\LaravelLocalizationHelpers\Factory\Exception');
        LocalizationHelpers::getLangPath('/these_folder_does_not_exist_right?');
    }
}
