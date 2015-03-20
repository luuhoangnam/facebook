<?php

namespace Namest\Facebook;

use Namest\Facebook\Traits\HasPicture;

/**
 * Class Profile
 *
 * @property string avatar
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Profile extends Object
{
    const USER = 'user';
    const PAGE = 'page';
    const GROUP = 'group';
    const EVENT = 'event';
    const APPLICATION = 'application';

    use HasPicture;
}
