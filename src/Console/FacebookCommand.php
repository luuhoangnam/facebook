<?php

namespace Namest\Facebook\Console;

use Illuminate\Support\Debug\Dumper;
use Namest\Facebook\Object as FacebookObject;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class FacebookCommand
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Cycle\Console\Commands\Facebook
 *
 */
class FacebookCommand extends Command
{
    /**
     * @var string
     */
    protected $object;

    /**
     * Create a new command instance.
     *
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
        $object = $this->makeObject();

        if ($this->option('get')) {
            $this->dump($object->get()->toArray());

            $this->info("\nGet [{$object->id}] completed!");
        }

        if ($this->option('fetch')) {
            $this->dump($object->fetch());

            $this->info("\nFetch [{$object->id}] completed!");
        }

        if ($this->option('sync')) {
            $this->dump($object->sync()->toArray());

            $this->info("\nSync [{$object->id}] completed!");
        }

        $this->comment("\nAll operations completed\n");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['id', InputArgument::REQUIRED, 'Object ID.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['get', 'g', InputOption::VALUE_NONE, 'Get information from internal database', null],
            ['fetch', 'f', InputOption::VALUE_NONE, 'Fetch information from facebook', null],
            ['sync', 's', InputOption::VALUE_NONE, 'Sync information from facebook to local database', null],
        ];
    }

    /**
     * @return FacebookObject
     */
    protected function makeObject()
    {
        if ( ! is_string($class = $this->object))
            throw new \LogicException("Object class must be set");

        return new $class(['id' => $this->argument('id')]);
    }

    protected function dump()
    {
        array_map(function ($x) {

            $dumper = new Dumper;
            $dumper->dump($x);

        }, func_get_args());
    }
}
