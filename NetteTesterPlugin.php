<?php
/**
 * Created by PhpStorm.
 * User: magerio
 * Date: 21.1.2015
 * Time: 0:36
 */

namespace ECH\PhpCIPlugins;

use PHPCI\Plugin;


use PHPCI\Builder;
use PHPCI\Model\Build;

/**
 * Atoum plugin, runs Atoum tests within a project.
 * @package PHPCI\Plugin
 */
class NetteTesterPlugin implements Plugin {
    private $args;
    private $config;
    private $directory;
    /**
     * Set up the plugin, configure options, etc.
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;
        if (isset($options['executable'])) {
            $this->executable = $this->phpci->buildPath . DIRECTORY_SEPARATOR.$options['executable'];
        } else {
            $this->executable = $this->phpci->findBinary('tester');
        }

        if (isset($options['path'])) {
            $this->path = $options['path'];
        }

        if (isset($options['args'])) {
            $this->args = $options['args']; // todo: remove?
        }
        if (isset($options['php_ini'])) {
            $this->phpIni = $options['php_ini'];
        }

        if (isset($options['show_skipped_test']) && $options['show_skipped_test']) {
            $this->skippedTests = ' -s ';
        }

        if (isset($options['coverage'])) {
            $this->coverage = $options['coverage'];
        }

    }
    /**
     * Run the Atoum plugin.
     * @return bool
     */
    public function execute()
    {
        //sample path vendor/bin/tester -c tests/php.ini tests

        $cmd = $this->executable;
        if ($this->args !== null) {
            $cmd .= " {$this->args}";
        }
        if ($this->phpIni !== null) {
            $cmd .= " -c {$this->phpIni}";
        }
        if ($this->skippedTests !== null) {
            $cmd .= $this->skippedTests;
        }
        if ($this->coverage !== null) {
            $cmd .= ' --coverage coverage.html';
        }
        if ($this->path !== null) {
            //$dirPath = $this->phpci->buildPath . DIRECTORY_SEPARATOR . $this->directory;
            $cmd .= ' '.$this->directory;
        }
        chdir($this->phpci->buildPath);
        $output = '';
        $status = true;
        exec($cmd, $output);
        if (count(preg_grep("/Success \(/", $output)) == 0) {
            $status = false;
            $this->phpci->log($output);
        }
        if (count($output) == 0) {
            $status = false;
            $this->phpci->log(Lang::get('no_tests_performed'));
        }
        return $status;
    }

}