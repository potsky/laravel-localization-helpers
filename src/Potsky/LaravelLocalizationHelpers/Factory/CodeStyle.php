<?php

namespace Potsky\LaravelLocalizationHelpers\Factory;

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CodeStyle
{
    /**
     * Fix Code Style for a file or a directory
     *
     * @param       $filePath
     * @param array $fixers
     * @param null $level
     *
     * @return string
     * @throws \Exception
     * @throws \Potsky\LaravelLocalizationHelpers\Factory\Exception
     */
    public function fix($filePath, array $rules)
    {
        if (empty($rules)) {
            return null;
        }

        if (defined('HHVM_VERSION_ID')) // @codeCoverageIgnoreStart
        {
            throw new Exception("HHVM not supported");
        } // @codeCoverageIgnoreEnd

        elseif (! defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) // @codeCoverageIgnoreStart
        {
            throw new Exception("PHP needs to be a minimum version of PHP 5.6.0");
        }
        // @codeCoverageIgnoreEnd

        if (! file_exists($filePath)) {
            throw new Exception('File "'.$filePath.'" does not exist, cannot fix it');
        }

        $options = [
            '--allow-risky'    => 'yes',
            '--format'         => 'txt',
            '--show-progress'  => 'none',
            '--using-cache'    => 'no',
            '--rules'          => json_encode($rules),
            '--no-interaction' => true,
            'command'          => 'fix',
            'path'             => [$filePath],
        ];

        $input  = new ArrayInput($options);
        $output = new BufferedOutput();
        $app    = new Application();
        $app->doRun($input, $output);

        return $output->fetch();
    }
}