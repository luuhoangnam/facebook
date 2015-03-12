<?php

namespace Namest\Facebook\Console;

use Namest\Facebook\Comment as CommentObject;

/**
 * Class Comment
 * @package Cycle\Console\Commands\Facebook
 */
class Comment extends FacebookCommand
{
    /**
     * @var string
     */
    protected $object = CommentObject::class;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'facebook:comment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive with facebook page';

}
