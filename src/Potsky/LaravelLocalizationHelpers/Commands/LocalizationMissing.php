<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LocalizationMissing extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'localization:missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse all translations in app directory and build all lang files';

    /**
     * Folders to parse for missing translations
     *
     * @var  array
     */
    private $folders = array(
        '%APP/helpers',
        '%APP/views' ,
        '%APP/controllers',
    );

    /**
     * functions and method to catch translations
     *
     * @var  array
     */
    private $trans_methods = array(
        'trans' => array(
            '@trans\(\s*(\'.*\')\s*\)@U',
            '@trans\(\s*(".*")\s*\)@U',
            ),
        'Lang::Get' => array(
            '@Lang::Get\(\s*(\'.*\')\s*\)@U',
            '@Lang::Get\(\s*(".*")\s*\)@U',
            ),
        'trans_choice' => array(
            '@trans_choice\(\s*(\'.*\')\s*,.*\)@U',
            '@trans_choice\(\s*(".*")\s*,.*\)@U',
            ),
        'Lang::choice' => array(
            '@Lang::choice\(\s*(\'.*\')\s*,.*\)@U',
            '@Lang::choice\(\s*(".*")\s*,.*\)@U',
            ),
        );

    /**
     * Get the lang directory path
     *
     * @return string the path
     */
    private function get_lang_path()
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
    private function get_path($path)
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
    private function get_short_path($path)
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
    private function get_php_files($path)
    {
        if ( is_dir( $path ) ) {
            return new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator( $path , RecursiveDirectoryIterator::SKIP_DOTS ),
                    RecursiveIteratorIterator::SELF_FIRST,
                    RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
                    ),
                '/^.+\.php$/i',
                RecursiveRegexIterator::GET_MATCH
                );
        } else {
            return array();
        }
    }

    /**
     * Extract all translations from the provided file
     *
     * @param string $path the file path
     *
     * @return array an array dot of found translations
     */
    private function extract_translation_from_php_file($path)
    {
        $result = array();
        $string = file_get_contents( $path );
        foreach ( array_flatten( $this->trans_methods ) as $method) {
            preg_match_all( $method , $string , $matches );
            $result = array_merge( $result , array_flip( $matches[1] ) );
        }

        return $result;
    }

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        ////////////////////////////////
        // Parse all lemmas from code //
        ////////////////////////////////
        $lemmas = array();
        foreach ( $this->get_path( $this->folders ) as $path ) {
            foreach ( $this->get_php_files( $path ) as $php_file_path => $dumb ) {
                $lemma = array();
                foreach ( $this->extract_translation_from_php_file( $php_file_path ) as $k => $v) {
                    $real_value           = eval( "return $k;" );
                    $lemma[ $real_value ] = $php_file_path;
                }
                $lemmas = array_merge( $lemmas , $lemma );
            }
        }
        if ( count( $lemmas ) === 0 ) {
            $this->comment("No lemma have been found in code.");
            $this->line("I have searched recursively in PHP files in these directories:");
            foreach ( $this->get_path( $this->folders ) as $path ) {
                $this->line( "  - " . $path );
            }
            $this->line("for these functions/methods:");
            foreach ($this->trans_methods as $k=>$v) {
                $this->line( "  - " . $k );
            }
            die();
        }

        $this->line( ( count( $lemmas ) > 1 ) ? count( $lemmas ) . " lemmas have been found in code" : "1 lemma has been found in code" );
        if ( $this->option( 'verbose' ) ) {
            foreach ($lemmas as $key => $value) {
                if ( strpos( $key , '.' ) !== false ) {
                    $this->line( '    <info>' . $key . '</info> in file <comment>' . $this->get_short_path( $value ) . '</comment>' );
                }
            }
        }

        /////////////////////////////////////////////
        // Convert dot lemmas to structured lemmas //
        /////////////////////////////////////////////
        $lemmas_structured = array();
        foreach ($lemmas as $key => $value) {
            if ( strpos( $key , '.' ) === false ) {
                $this->line( '    <error>' . $key . '</error> in file <comment>' . $this->get_short_path( $value ) . '</comment> <error>will not be included because it has no parent</error>' );
            } else {
                array_set( $lemmas_structured , $key , $value );
            }
        }

        $this->line( '' );

        /////////////////////////////////////
        // Generate lang files :           //
        // - add missing lemmas on top     //
        // - keep already defined lemmas   //
        // - add obsolete lemmas on bottom //
        /////////////////////////////////////
        $dir_lang = $this->get_lang_path();
        $job      = array();

        $this->line( 'Scan files:' );
        foreach ( scandir( $dir_lang ) as $lang ) {

            if ( ! in_array( $lang , array( "." , ".." ) ) ) {

                if ( is_dir( $dir_lang . DIRECTORY_SEPARATOR . $lang ) ) {

                    foreach ($lemmas_structured as $family => $array) {

                        $file_lang_path = $dir_lang . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php';

                        if ( $this->option( 'verbose' ) ) {
                            $this->line( '' );
                        }
                        $this->line( '  - ' . $this->get_short_path( $file_lang_path ) );

                        if ( ! is_writable( dirname( $file_lang_path ) ) ) {
                            $this->error( "    Unable to write file in directory " . dirname( $file_lang_path ) );
                            die();
                        }

                        if ( ! file_exists( $file_lang_path ) ) {
                            $this->info( "    > File has been created" );
                        }

                        if ( ! touch( $file_lang_path ) ) {
                            $this->error( "    Unable to touch file $file_lang_path" );
                            die();
                        }

                        if ( ! is_readable( $file_lang_path ) ) {
                            $this->error( "    Unable to read file $file_lang_path" );
                            die();
                        }

                        if ( ! is_writable( $file_lang_path ) ) {
                            $this->error( "    Unable to write in file $file_lang_path" );
                            die();
                        }

                        $a                        = include( $file_lang_path );
                        $old_lemmas               = ( is_array( $a ) ) ? array_dot( $a ) : array();
                        $new_lemmas               = array_dot( $array );
                        $final_lemmas             = array();
                        $display_already_comment  = false;
                        $display_obsolete_comment = false;
                        $something_to_do          = false;
                        $i                        = 0;
                        $obsolete_lemmas          = array_diff_key( $old_lemmas , $new_lemmas );
                        $welcome_lemmas           = array_diff_key( $new_lemmas , $old_lemmas );
                        $already_lemmas           = array_intersect_key( $old_lemmas , $new_lemmas );
                        ksort( $obsolete_lemmas );
                        ksort( $welcome_lemmas  );
                        ksort( $already_lemmas  );

                        if ( count( $welcome_lemmas ) > 0 ) {
                            $display_already_comment = true;
                            $something_to_do         = true;
                            $this->info( "    - " . count( $welcome_lemmas ) . " new strings to translate");
                            $final_lemmas[ "POTSKY___NEW___POTSKY" ] = "POTSKY___NEW___POTSKY";
                            foreach ($welcome_lemmas as $key => $value) {
                                if ( $this->option( 'verbose' ) ) {
                                    $this->line( "          <info>" . $key . "</info> in " . $this->get_short_path( $value ) );
                                }
                                if ( ! $this->option( 'no-comment' ) ) {
                                    $final_lemmas[ 'POTSKY___COMMENT___POTSKY' . $i ] = "Defined in file $value";
                                    $i = $i + 1;
                                }
                                array_set( $final_lemmas , $key , $key );
                            }
                        }

                        if ( count( $already_lemmas ) > 0 ) {
                            if ( $this->option( 'verbose' ) ) {
                                $this->line( "    - " . count( $already_lemmas ) . " already translated strings");
                            }
                            $final_lemmas[ "POTSKY___OLD___POTSKY" ] = "POTSKY___OLD___POTSKY";
                            foreach ($already_lemmas as $key => $value) {
                                array_set( $final_lemmas , $key , $value );
                            }
                        }

                        if ( count( $obsolete_lemmas ) > 0 ) {
                            $display_already_comment  = true;
                            $display_obsolete_comment = ( $this->option( 'no-obsolete' ) ) ? false : true;
                            $something_to_do          = true;
                            $this->comment( $this->option( 'no-obsolete' )
                                ? "    - " . count( $obsolete_lemmas ) . " obsolete strings (will be deleted)"
                                : "    - " . count( $obsolete_lemmas ) . " obsolete strings (can be deleted manually in the generated file)"
                            );
                            $final_lemmas[ "POTSKY___OBSOLETE___POTSKY" ] = "POTSKY___OBSOLETE___POTSKY";
                            foreach ($obsolete_lemmas as $key => $value) {
                                if ( $this->option( 'verbose' ) ) {
                                    $this->line( "          <comment>" . $key . "</comment>" );
                                }
                                if ( ! $this->option( 'no-obsolete' ) ) {
                                    array_set( $final_lemmas , $key , $value );
                                }
                            }
                        }

                        if ( ( $something_to_do === true ) || ( $this->option( 'force' ) ) ) {
                            $content = var_export( $final_lemmas , true );
                            $content = preg_replace( "@'POTSKY___COMMENT___POTSKY[0-9]*' => '(.*)',@" , '// $1' , $content);
                            $content = str_replace(
                                array(
                                    "'POTSKY___NEW___POTSKY' => 'POTSKY___NEW___POTSKY',",
                                    "'POTSKY___OLD___POTSKY' => 'POTSKY___OLD___POTSKY',",
                                    "'POTSKY___OBSOLETE___POTSKY' => 'POTSKY___OBSOLETE___POTSKY',",
                                    ),
                                array(
                                    '//============================== New strings to translate ==============================//',
                                    ( $display_already_comment === true ) ? '//==================================== Translations ====================================//' : '',
                                    '//================================== Obsolete strings ==================================//',
                                    ),
                                $content
                                );

                            $file_content = "<?php\n";
                            if ( ! $this->option( 'no-date' ) ) {
                                $a = " Generated via \"php artisan " . $this->argument('command') . "\" at " . date("Y/m/d H:i:s") . " ";
                                $file_content .= "/" . str_repeat ( '*' , strlen( $a ) ) . "\n" . $a . "\n" . str_repeat ( '*' , strlen( $a ) ) . "/\n";
                            }
                            $file_content.= "\nreturn " . $content . ";";
                            $job[ $file_lang_path ] = $file_content;
                        } else {
                            if ( $this->option( 'verbose' ) ) {
                                $this->line( "    > <comment>Nothing to do for this file</comment>" );
                            }
                        }
                    }
                }
            }
        }

        if ( count( $job ) > 0 ) {

            if ( $this->option( 'no-interaction' ) ) {
                $do = true;
            } else {
                $this->line( '' );
                $do = ( $this->ask( 'Do you wish to apply these changes now? [yes|no]' ) === 'yes' );
                $this->line( '' );
            }

            if ($do === true) {

                if ( ! $this->option( 'no-backup' ) ) {
                    $this->line( 'Backup files:' );
                    foreach ($job as $file_lang_path => $file_content) {
                        $backup_path = preg_replace('/\..+$/', '.' . date("Ymd_His") . '.php' , $file_lang_path);
                        rename( $file_lang_path , $backup_path );
                        $this->line( "  - <info>" . $this->get_short_path( $file_lang_path ). "</info> -> <info>" . $this->get_short_path( $backup_path ) . "</info>");
                    }
                    $this->line( '' );
                }

                $this->line( 'Save files:' );
                foreach ($job as $file_lang_path => $file_content) {
                    file_put_contents( $file_lang_path , $file_content );
                    $this->line( "  - <info>" . $this->get_short_path( $file_lang_path ) );
                }
                $this->line( '' );

                $this->info( 'Process done!' );
            } else {
                $this->line( '' );
                $this->comment( 'Process aborted. No file have been changed.' );
            }
        } else {
            $this->line( '' );
            $this->info( 'Drink a Piña colada and/or smoke Super Skunk, you have nothing to do!' );
        }
        $this->line( '' );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array( 'force'       , 'f' , InputOption::VALUE_NONE , 'Force file rewrite even if there is nothing to do' ),
            array( 'no-comment'  , 'c' , InputOption::VALUE_NONE , 'Do not add comments in lang files for lemma definition' ),
            array( 'no-date'     , 'd' , InputOption::VALUE_NONE , 'Do not add the date of execution in the lang files' ),
            array( 'no-backup'   , 'b' , InputOption::VALUE_NONE , 'Do not backup lang file (be careful, I am not a good coder)' ),
            array( 'no-obsolete' , 'o' , InputOption::VALUE_NONE , 'Do not write obsolete lemma' ),
        );
    }

}
