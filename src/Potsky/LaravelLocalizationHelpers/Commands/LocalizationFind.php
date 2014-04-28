<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LocalizationFind extends LocalizationMissing
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'localization:find';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all files where the argument is used as a lemma';

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Config\Repository $configRepository
     *
     * @return void
     */
    public function __construct( Repository $configRepository )
    {
        $this->trans_methods = \Config::get('laravel-localization-helpers::config.trans_methods');
        $this->folders       = \Config::get('laravel-localization-helpers::config.folders');
        parent::__construct( $configRepository );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        var_dump( $this->folders );

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
            array( 'short' , 's' , InputOption::VALUE_NONE , 'Short path relative to the laravel project' ),
            array( 're'    , 'r' , InputOption::VALUE_NONE , 'Argument is a regular expression' ),
        );
    }

}
