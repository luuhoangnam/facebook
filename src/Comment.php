<?php

namespace Namest\Facebook;

/**
 * Class Comment
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook
 *
 */
class Comment extends Object
{
    protected $fields = [
        'id',
        'from',
        'message',
        'created_time',
        'can_hide',
        'can_like',
        'can_remove',
    ];

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
     * Alias method
     *
     * @return EdgeOut
     */
    public function replies()
    {
        return $this->comments();
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function reply($message)
    {
        return $this->comment($message);
    }

    /**
     * @param string $mesage New message
     */
    public function edit($mesage)
    {
        $this->update(['message' => $mesage]);
    }

    public function hide()
    {
        $this->update(['is_hidden' => true]);
    }

    public function unhide()
    {
        $this->update(['is_hidden' => false]);
    }

    /**
     * @return array
     */
    public function getFetchRepliesParameters()
    {
        return [
            'limit' => 100,
        ];
    }

    /**
     * @return array
     */
    public function getFetchRepliesFields()
    {
        return [
            'id',
            'from',
            'message',
            'created_time',
            'can_remove',
            'can_hide',
            'can_like',
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

        $profile = $profile->setId($data->id)->sync();
    }
}
