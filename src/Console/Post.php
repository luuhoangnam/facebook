<?php

namespace Namest\Facebook\Console;

use Namest\Facebook\Post as PostObject;

/**
 * Class Post
 * @package Cycle\Console\Commands\Facebook
 */
class Post extends FacebookCommand
{
    /**
     * @var string
     */
    protected $object = PostObject::class;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'facebook:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive with facebook page';

}
