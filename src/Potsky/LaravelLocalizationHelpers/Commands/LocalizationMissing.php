<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputOption;

class LocalizationMissing extends LocalizationAbstract
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
     * Create a new command instance.
     *
     * @param \Illuminate\Config\Repository $configRepository
     *
     * @return void
     */
    public function __construct(Repository $configRepository)
    {
        parent::__construct( $configRepository );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        define( 'SUCCESS' , 0 );
        define( 'ERROR'   , 1 );

        $folders       = $this->get_path( $this->folders );
        $this->display = ! $this->option( 'silent' );

        //////////////////////////////////////////////////
        // Display where translations are searched in //
        //////////////////////////////////////////////////
        if ( $this->option( 'verbose' ) ) {
            $this->line("Lemmas will be searched in the following directories:");
            foreach ($folders as $path) {
                $this->line( '    <info>' . $path . '</info>' );
            }
            $this->line( '' );
        }

        ////////////////////////////////
        // Parse all lemmas from code //
        ////////////////////////////////
        $lemmas = array();
        foreach ($folders as $path) {
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
                $this->line( "    " . $path );
            }
            $this->line("for these functions/methods:");
            foreach ($this->trans_methods as $k=>$v) {
                $this->line( "    " . $k );
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
        $dir_lang      = $this->get_lang_path();
        $job           = array();
        $there_are_new = false;

        $this->line( 'Scan files:' );
        foreach ( scandir( $dir_lang ) as $lang ) {

            if ( ! in_array( $lang , array( "." , ".." ) ) ) {

                if ( is_dir( $dir_lang . DIRECTORY_SEPARATOR . $lang ) ) {

                    foreach ($lemmas_structured as $family => $array) {

                        if ( in_array( $family , $this->ignore_lang_files ) ) {
                            if ( $this->option( 'verbose' ) ) {
                                $this->line( '' );
                                $this->info( "    ! Skip lang file '$family' !" );
                            }
                            continue;
                        }

                        $file_lang_path = $dir_lang . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php';

                        if ( $this->option( 'verbose' ) ) {
                            $this->line( '' );
                        }
                        $this->line( '    ' . $this->get_short_path( $file_lang_path ) );

                        if ( ! is_writable( dirname( $file_lang_path ) ) ) {
                            $this->error( "    > Unable to write file in directory " . dirname( $file_lang_path ) );
                            die();
                        }

                        if ( ! file_exists( $file_lang_path ) ) {
                            $this->info( "    > File has been created" );
                        }

                        if ( ! touch( $file_lang_path ) ) {
                            $this->error( "    > Unable to touch file $file_lang_path" );
                            die();
                        }

                        if ( ! is_readable( $file_lang_path ) ) {
                            $this->error( "    > Unable to read file $file_lang_path" );
                            die();
                        }

                        if ( ! is_writable( $file_lang_path ) ) {
                            $this->error( "    > Unable to write in file $file_lang_path" );
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

                        //////////////////////////
                        // Deal with new lemmas //
                        //////////////////////////
                        if ( count( $welcome_lemmas ) > 0 ) {
                            $display_already_comment = true;
                            $something_to_do         = true;
                            $there_are_new           = true;
                            $this->info( "        " . count( $welcome_lemmas ) . " new strings to translate");
                            $final_lemmas[ "POTSKY___NEW___POTSKY" ] = "POTSKY___NEW___POTSKY";
                            foreach ($welcome_lemmas as $key => $value) {
                                if ( $this->option( 'verbose' ) ) {
                                    $this->line( "            <info>" . $key . "</info> in " . $this->get_short_path( $value ) );
                                }
                                if ( ! $this->option( 'no-comment' ) ) {
                                    $final_lemmas[ 'POTSKY___COMMENT___POTSKY' . $i ] = "Defined in file $value";
                                    $i = $i + 1;
                                }
                                array_set( $final_lemmas , $key , str_replace( '%LEMMA' , $key , $this->option('new-value') ) );
                            }
                        }

                        ///////////////////////////////
                        // Deal with existing lemmas //
                        ///////////////////////////////
                        if ( count( $already_lemmas ) > 0 ) {
                            if ( $this->option( 'verbose' ) ) {
                                $this->line( "        " . count( $already_lemmas ) . " already translated strings");
                            }
                            $final_lemmas[ "POTSKY___OLD___POTSKY" ] = "POTSKY___OLD___POTSKY";
                            foreach ($already_lemmas as $key => $value) {
                                array_set( $final_lemmas , $key , $value );
                            }
                        }

                        ///////////////////////////////
                        // Deal with obsolete lemmas //
                        ///////////////////////////////
                        if ( count( $obsolete_lemmas ) > 0 ) {
                            // Remove all dynamic fields
                            foreach ($obsolete_lemmas as $key => $value) {
                                foreach ($this->never_obsolete_keys as $remove) {
                                    if ( strpos( $key , '.' . $remove . '.' ) !== false ) {
                                        unset( $obsolete_lemmas[$key] );
                                    }
                                }
                            }
                        }

                        if ( count( $obsolete_lemmas ) > 0 ) {
                            $display_already_comment  = true;
                            $display_obsolete_comment = ( $this->option( 'no-obsolete' ) ) ? false : true;
                            $something_to_do          = true;
                            $this->comment( $this->option( 'no-obsolete' )
                                ? "        " . count( $obsolete_lemmas ) . " obsolete strings (will be deleted)"
                                : "        " . count( $obsolete_lemmas ) . " obsolete strings (can be deleted manually in the generated file)"
                            );
                            $final_lemmas[ "POTSKY___OBSOLETE___POTSKY" ] = "POTSKY___OBSOLETE___POTSKY";
                            foreach ($obsolete_lemmas as $key => $value) {
                                if ( $this->option( 'verbose' ) ) {
                                    $this->line( "            <comment>" . $key . "</comment>" );
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
                                $this->line( "        > <comment>Nothing to do for this file</comment>" );
                            }
                        }
                    }
                }
            }
        }


        ///////////////////////////////////////////
        // Silent mode                           //
        // only return an exit code on new lemma //
        ///////////////////////////////////////////
        if ( $this->option( 'silent' ) ) {
            if ( $there_are_new === true ) {
                return ERROR;
            } else {
                return SUCCESS;
            }
        }

        ///////////////////////////////////////////
        // Normal mode                           //
        ///////////////////////////////////////////
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
                        if ( ! $this->option( 'dry-run' ) ) {
                            rename( $file_lang_path , $backup_path );
                        }
                        $this->line( "    <info>" . $this->get_short_path( $file_lang_path ). "</info> -> <info>" . $this->get_short_path( $backup_path ) . "</info>");
                    }
                    $this->line( '' );
                }

                $this->line( 'Save files:' );
                $open_files = '';
                foreach ($job as $file_lang_path => $file_content) {
                    if ( ! $this->option( 'dry-run' ) ) {
                        file_put_contents( $file_lang_path , $file_content );
                    }
                    $this->line( "    <info>" . $this->get_short_path( $file_lang_path ) );
                    if ( $this->option('editor') ) {
                        $open_files.= ' ' . escapeshellarg( $file_lang_path );
                    }
                }
                $this->line( '' );

                $this->info( 'Process done!' );

                if ( $this->option('editor') ) {
                    exec( $this->editor . $open_files );
                }

            } else {
                $this->line( '' );
                $this->comment( 'Process aborted. No file have been changed.' );
            }
        } else {
            if ( $this->option( 'silent' ) ) {
                return SUCCESS;
            }

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
            array( 'dry-run'     , 'r' , InputOption::VALUE_NONE     , 'Dry run : run process but do not write anything' ),
            array( 'editor'      , 'e' , InputOption::VALUE_NONE     , 'Open files which need to be edited at the end of the process' ),
            array( 'force'       , 'f' , InputOption::VALUE_NONE     , 'Force file rewrite even if there is nothing to do' ),
            array( 'new-value'   , 'l' , InputOption::VALUE_OPTIONAL , 'Value of new found lemmas (use %LEMMA for the lemma value)' , '%LEMMA' ),
            array( 'no-backup'   , 'b' , InputOption::VALUE_NONE     , 'Do not backup lang file (be careful, I am not a good coder)' ),
            array( 'no-comment'  , 'c' , InputOption::VALUE_NONE     , 'Do not add comments in lang files for lemma definition' ),
            array( 'no-date'     , 'd' , InputOption::VALUE_NONE     , 'Do not add the date of execution in the lang files' ),
            array( 'no-obsolete' , 'o' , InputOption::VALUE_NONE     , 'Do not write obsolete lemma' ),
            array( 'silent'      , 's' , InputOption::VALUE_NONE     , 'Use this option to only return the exit code (use $? in shell to know whether there are missing lemma)' ),
        );
    }

}
