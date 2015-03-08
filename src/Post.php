<?php

namespace Namest\Facebook;

/**
 * Class Post
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Post extends Object
{
    /**
     * @return EdgeOut
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'ON', 'comments', Edge::IN);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function comment($message)
    {
        return $this->comments()->publish(['message' => $message]);
    }

    /**
     * @return array
     */
    public function getFetchCommentsParameters()
    {
        return [
            'filter' => 'stream',
            'limit'  => 100,
        ];
    }

    /**
     * @return array
     */
    public function getFetchCommentsFields()
    {
        return [
            'id',
            'from',
            'message',
            'can_remove',
            'can_hide',
            'parent' => ['id'],
        ];
    }

    /**
     * @param mixed $data
     */
    public function hydrateFromField($data)
    {
        $client      = $this->getClient();
        $profileType = $client->guestProfileTypeFromData($data);

        switch ($profileType) {
            case Profile::APPLICATION:
                $profile = new Application;
                break;
            case Profile::GROUP:
                $profile = new Group;
                break;
            case Profile::EVENT:
                $profile = new Event;
                break;
            case Profile::PAGE:
                $profile = new Page;
                break;
            case Profile::USER:
            default:
                $profile = new User;
        }

        /** @var Profile $profile */
        $profile = $profile->setId($data->id)->sync();

        $this->saved(function () use ($profile) {
            // Make edge relation

        });
    }
}
