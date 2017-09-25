<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class CommandMissingTests extends TestCase
{
    private static $langFile;

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

        self::$langFile = self::LANG_DIR_PATH.'/en/message.php';

        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'lang_folder_path', self::LANG_DIR_PATH);
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'folders', self::MOCK_DIR_PATH_GLOBAL);
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'code_style.fixers', [
            'align_double_arrow',
            'short_array_syntax',
        ]);

        // Remove all saved access token for translation API
        $translator = new \MicrosoftTranslator\Client([
            'api_client_key' => true,
        ]);
        $translator->getAuth()
                   ->getGuard()
                   ->deleteAllAccessTokens();
    }

    /**
     * - lang files have been created
     * - lemma without a family is rejected
     * - the default lang file array is structured
     * - the default translation is prefixed by TO DO
     */
    public function testLangFileDoesNotExist()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', ['--no-interaction' => true]);
        $result = Artisan::output();

        $this->assertEquals(0, $return);
        $this->assertContains('File has been created', $result);
        $this->assertNotContains('OUPS', $result);

        /** @noinspection PhpIncludeInspection */
        $lemmas = include(self::LANG_DIR_PATH.'/fr/message.php');
        $this->assertEquals('TODO: child', $lemmas['lemma']['child']);
    }

    /**
     * - Set a non existing lang folder
     */
    public function testLangFolderDoesNotExist()
    {
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'lang_folder_path', self::LANG_DIR_PATH.'doesnotexist');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', ['--no-interaction' => true]);

        $this->assertEquals(1, $return);
        $this->assertContains('No lang folder found in your custom path:', Artisan::output());
    }

    /**
     * - Default lang folders are used when custom land folder path as not been set by user
     *
     * In Laravel 5.x, orchestra/testbench has not empty lang en directory, so return code is 0 and not 1
     */
    public function testDefaultLangFolderDoesNotExist()
    {
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'lang_folder_path', null);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', ['--no-interaction' => true]);

        $this->assertEquals(0, $return);
    }

    /**
     * - Default lang folders are used when custom land folder path as not been set by user
     */
    public function testDefaultLangFolderExists()
    {
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'lang_folder_path', null);

        @mkdir(self::ORCHESTRA_LANG_DIR_PATH);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', ['--no-interaction' => true]);
        $this->assertFileNotExists(self::ORCHESTRA_LANG_DIR_PATH.'/en/message.php');

        $this->assertEquals(0, $return);
        $this->assertContains('Drink a Piña colada and/or smoke Super Skunk, you have nothing to do!', Artisan::output());
    }

    /**
     * - create dumb lang files to verify backups are done
     */
    public function testLangFileExistsWithBackup()
    {
        touch(self::LANG_DIR_PATH.'/en/message.php');
        touch(self::LANG_DIR_PATH.'/fr/message.php');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', ['--no-interaction' => true]);

        $this->assertEquals(0, $return);
        $this->assertContains('Backup files', Artisan::output());
    }

    /**
     * - the default lang file array is structured
     * - the new-value option works
     */
    public function testFlatOutput()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--output-flat'    => true,
            '--new-value'      => '%LEMMA POTSKY',
        ]);

        $this->assertEquals(0, $return);

        /** @noinspection PhpIncludeInspection */
        $lemmas = include(self::LANG_DIR_PATH.'/fr/message.php');
        $this->assertEquals('child POTSKY', $lemmas['lemma.child']);
    }

    /**
     * - new-value set to null converts translation to null value to provide translation fallback
     *
     * https://github.com/potsky/laravel-localization-helpers/issues/38
     */
    public function testTranslationFallback()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--output-flat'    => true,
            '--new-value'      => 'nUll',
        ]);

        $this->assertEquals(0, $return);

        /** @noinspection PhpIncludeInspection */
        $lemmas = include(self::LANG_DIR_PATH.'/fr/message.php');
        $this->assertNull($lemmas['lemma.child']);
    }

    /**
     * - check a word is correctly translated
     */
    public function testTranslations()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--output-flat'    => true,
            '--translation'    => true,
        ]);

        $this->assertEquals(0, $return);

        /** @noinspection PhpIncludeInspection */
        $lemmas = include(self::LANG_DIR_PATH.'/fr/message.php');
        $this->assertEquals('TODO: chien', $lemmas['dog']);
        $this->assertEquals('TODO: chien', $lemmas['child.dog']);
    }

    /**
     * - the default lang file array is structured
     * - the new-value option works
     */
    public function testVerbose()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--verbose'        => true,
        ]);

        $this->assertEquals(0, $return);
        $this->assertContains('Lemmas will be searched in the following directories:', Artisan::output());

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--verbose'        => true,
        ]);

        $this->assertEquals(0, $return);
        $this->assertContains('Nothing to do for this file', Artisan::output());
    }

    /**
     *
     */
    public function testNothingToDo()
    {
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'folders', self::MOCK_DIR_PATH_WO_LEMMA);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--verbose'        => true,
        ]);

        $this->assertEquals(0, $return);
        $this->assertContains('No lemma has been found in code.', Artisan::output());
    }

    /**
     *
     */
    public function testObsoleteLemma()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--no-backup'      => true,
        ]);

        $this->assertEquals(0, $return);

        $lemmas = require(self::$langFile);
        $this->assertArrayHasKey('child', $lemmas);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction'     => true,
            '--verbose'            => true,
            '--php-file-extension' => 'copy',
            '--no-backup'          => true,
        ]);

        $this->assertEquals(0, $return);

        $this->assertContains('11 obsolete strings', Artisan::output());

        $lemmas = require(self::$langFile);
        $this->assertArrayNotHasKey('child', $lemmas);
        $this->assertArrayHasKey('child', $lemmas['LLH:obsolete']);
    }

    /**
     *
     */
    public function testSilent()
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $return = Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--silent'         => true,
        ]);

        // Exit code is 1 because there are new lemma to translate
        $this->assertEquals(1, $return);
        $this->assertEmpty(Artisan::output());
    }

    /**
     *
     */
    public function testTranslationsNotConfigured()
    {
        Config::set(Localization::PREFIX_LARAVEL_CONFIG.'translators.Microsoft.client_key', 'dumb');

        $this->expectException('\\MicrosoftTranslator\\Exception');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        Artisan::call('localization:missing', [
            '--no-interaction' => true,
            '--output-flat'    => true,
            '--translation'    => true,
        ]);
    }
}
