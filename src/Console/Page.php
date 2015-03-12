<?php

namespace Namest\Facebook\Console;

use Namest\Facebook\Page as PageObject;

/**
 * Class Page
 * @package Cycle\Console\Commands\Facebook
 */
class Page extends FacebookCommand
{
    /**
     * @var string
     */
    protected $object = PageObject::class;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'facebook:page';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive with facebook page';

}
