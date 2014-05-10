<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

abstract class LocalizationAbstract extends Command
{
    /**
     * Config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $configRepository;

    /**
     * functions and method to catch translations
     *
     * @var  array
     */
    protected $trans_methods = array();

    /**
     * Folders to parse for missing translations
     *
     * @var  array
     */
    protected $folders = array();

    /**
     * Never make lemmas containing these keys obsolete
     *
     * @var  array
     */
    protected $never_obsolete_keys = array();

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Config\Repository $configRepository
     *
     * @return void
     */
    public function __construct( Repository $configRepository )
    {
        $this->trans_methods       = \Config::get('laravel-localization-helpers::config.trans_methods');
        $this->folders             = \Config::get('laravel-localization-helpers::config.folders');
        $this->never_obsolete_keys = \Config::get('laravel-localization-helpers::config.never_obsolete_keys');
        parent::__construct();
    }

    /**
     * Get the lang directory path
     *
     * @return string the path
     */
    protected function get_lang_path()
    {
        return app_path() . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Return an absolute path without predefined variables
     *
     * @param string $path the relative path
     *
     * @return string the absolute path
     */
    protected function get_path($path)
    {
        return str_replace(
            array(
                '%APP',
                '%BASE',
                '%PUBLIC',
                '%STORAGE',
                ),
            array(
                app_path(),
                base_path(),
                public_path(),
                storage_path()
                ),
            $path
            );
    }

    /**
     * Return an relative path to the laravel directory
     *
     * @param string $path the absolute path
     *
     * @return string the relative path
     */
    protected function get_short_path($path)
    {
        return str_replace( base_path() , '' , $path );
    }

    /**
     * return an iterator of php files in the provided paths and subpaths
     *
     * @param string $path a source path
     *
     * @return array a list of php file paths
     */
    protected function get_php_files($path)
    {
        if ( is_dir( $path ) ) {
            return new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator( $path , \RecursiveDirectoryIterator::SKIP_DOTS ),
                    \RecursiveIteratorIterator::SELF_FIRST,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
                    ),
                '/^.+\.php$/i',
                \RecursiveRegexIterator::GET_MATCH
                );
        } else {
            return array();
        }
    }

    /**
     * Extract all translations from the provided file
     * Remove all translations containing :
     * - $  -> auto-generated translation cannot be supported
     * - :: -> package translations are not take in account
     *
     * @param string $path the file path
     *
     * @return array an array dot of found translations
     */
    protected function extract_translation_from_php_file($path)
    {
        $result = array();
        $string = file_get_contents( $path );
        foreach ( array_flatten( $this->trans_methods ) as $method) {
            preg_match_all( $method , $string , $matches );
            $a = array();
            foreach ( $matches[1] as $k => $v ) {
                if ( strpos( $v , '$' ) !== false ) unset( $matches[1][$k] );
                if ( strpos( $v , '::' ) !== false ) unset( $matches[1][$k] );
            }
            $result = array_merge( $result , array_flip( $matches[1] ) );
        }

        return $result;
    }

}
