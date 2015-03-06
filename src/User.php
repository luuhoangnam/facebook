<?php

namespace Namest\Facebook;

/**
 * Class User
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class User extends Profile
{
    /**
     * @return EdgeOut
     */
    public function accounts()
    {
        // (u:User)-[r:MANAGE]->(p:Page)
        return $this->hasMany(Page::class, 'MANAGE');
    }
}
