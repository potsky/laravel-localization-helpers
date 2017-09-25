<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class OutputTests extends TestCase
{
    /**
     * Setup the test environment.
     *
     * - Remove all previous lang files before each test
     * - Set custom configuration paths
     */
    public function setUp()
    {
        parent::setUp();

        Tools::unlinkGlobFiles(self::LANG_DIR_PATH.'/*/message*.php');

        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'lang_folder_path', self::LANG_DIR_PATH);
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'folders', self::MOCK_DIR_PATH_GLOBAL);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--output-flat'    => true,
            '--new-value'      => '%LEMMA POTSKY',
        ]);
    }

    /**
     *
     */
    public function test()
    {
        $messageBag = new MessageBag();
        $manager    = new Localization($messageBag);

        /** @noinspection PhpIncludeInspection */
        $this->assertContains('array (', file_get_contents(self::LANG_DIR_PATH.'/fr/message.php'));
        $this->assertNotContains('[', file_get_contents(self::LANG_DIR_PATH.'/fr/message.php'));

        $this->assertContains('Fixed all files in', $manager->fixCodeStyle(self::LANG_DIR_PATH.'/fr/message.php', [
            'array_syntax' => ['syntax' => 'short'],
            '@PSR2'        => true,
        ]));

        /** @noinspection PhpIncludeInspection */
        $this->assertContains('[', file_get_contents(self::LANG_DIR_PATH.'/fr/message.php'));
        $this->assertNotContains('array (', file_get_contents(self::LANG_DIR_PATH.'/fr/message.php'));
    }

    /**
     *
     */
    public function testPathDoesNotExist()
    {
        $messageBag = new MessageBag();
        $manager    = new Localization($messageBag);

        $this->expectException('\\Potsky\\LaravelLocalizationHelpers\\Factory\\Exception');

        $manager->fixCodeStyle(self::LANG_DIR_PATH.'/file_does_not_exist', [
            'array_syntax' => ['syntax' => 'short'],
            '@PSR2'        => true,
        ]);
    }
}
