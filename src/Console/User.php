<?php

namespace Namest\Facebook\Console;

use Namest\Facebook\User as UserObject;

/**
 * Class User
 * @package Cycle\Console\Commands\Facebook
 */
class User extends FacebookCommand
{
    /**
     * @var string
     */
    protected $object = UserObject::class;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'facebook:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive with facebook user';

}
