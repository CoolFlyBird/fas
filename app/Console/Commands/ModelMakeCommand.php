<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2019/3/5
 * Time: 15:38
 */
namespace App\Console\Commands;

class ModelMakeCommand extends \Illuminate\Foundation\Console\ModelMakeCommand
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if (!$this->option('pivot')) {
            return __DIR__.'/stubs/model.stub';
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Models';
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $array = explode('\\', $name);
        $tableArr = preg_split("/(?=[A-Z])/", $array[count($array) - 1]);
        array_shift($tableArr);
        if (strpos($tableArr[count($tableArr) - 1], 'Model') !== false) {
            array_pop($tableArr);
        }
        $tableNameArr = array_map('strtolower', $tableArr);
        $replace['TableName'] = implode('_', $tableNameArr);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }
}