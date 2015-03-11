<?php

namespace Namest\Facebook\Realtime;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Writer;
use Namest\Facebook\Comment;
use Namest\Facebook\Page;
use Namest\Facebook\Post;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Receiver
 *
 * @author  Nam Hoang Luu <nam@mbearvn.com>
 * @package Namest\Facebook\Realtime
 *
 */
class Receiver
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Writer
     */
    private $log;

    /**
     * @param Request $request
     * @param Writer  $log
     */
    public function __construct(Request $request, Writer $log)
    {
        $this->request = $request;
        $this->log     = $log;
    }

    /**
     * @param string $verifyToken
     *
     * @return Response
     */
    public function verify($verifyToken = null)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        $verifyToken = $verifyToken ?: env('FACEBOOK_VERIFY_TOKEN');

        /** @noinspection PhpUnusedLocalVariableInspection */
        $mode         = $this->request->get('hub_mode');
        $challenge    = $this->request->get('hub_challenge');
        $verify_token = $this->request->get('hub_verify_token');

        if ($verify_token != $verifyToken)
            throw new HttpException(401, 'Not Authorized!!!');

        return new Response($challenge, 200);
    }

    /**
     * @throws \Exception
     */
    public function receive()
    {
        $content = file_get_contents('php://input');

        $updates = json_decode($content);

        $object = object_get($updates, 'object');

        if ($object != 'page')
            return null; // Do not handle other situations

        $this->processEntries(object_get($updates, 'entry'));
    }

    /**
     * @param \StdClass $value
     *
     * @throws \Exception
     */
    private function processCommentChanges($value)
    {
        $commentId = $value->comment_id;
        $postId    = $value->parent_id;

        switch ($value->verb) {
            case 'add':
                $post    = new Post(['id' => $postId]);
                $comment = (new Comment(['id' => $commentId]))->sync();

                $post->comments()->save($comment);

                $this->log->info('New comment has been added', [
                    'post'    => $postId,
                    'comment' => $commentId,
                    'message' => $comment->message
                ]);
                break;
            case 'edited':
                $comment = new Comment(['id' => $commentId]);
                $comment->sync();

                $this->log->info('Comment has been edited', [
                    'comment' => $commentId,
                    'message' => $comment->message
                ]);
                break;
            case 'remove':
                $comment = new Comment(['id' => $commentId]);
                $comment->getNode()->delete();

                $this->log->info('Comment has been removed', [
                    'comment' => $commentId,
                ]);
                break;
            default:
                throw new \Exception("Unhandle comment [{$commentId}] change [{$value->verb}]");
        }
    }

    /**
     * @param mixed  $value
     * @param string $pageId Page ID
     *
     * @throws \Exception
     */
    private function processStatusChanges($value, $pageId)
    {

        switch ($value->verb) {
            case 'add':
                $postId = $value->post_id;
                $page   = (new Page(['id' => $pageId]))->sync();
                $post   = (new Post(['id' => $postId]))->sync();

                $page->posts()->save($post);

                $this->log->info('New status has been added', [
                    'page'    => $pageId,
                    'post'    => $postId,
                    'message' => $post->message,
                ]);
                break;
            default:
                throw new \Exception("Unhandle comment [{$value->post_id}] change [{$value->verb}]");
        }
    }

    /**
     * @param $value
     *
     * @throws \Exception
     */
    private function processPostChanges($value)
    {
        switch ($value->verb) {
            case 'remove':
                $id   = $value->post_id;
                $post = new Post(['id' => $id]);
                $post->deleteNode();

                $this->log->info('Post has been removed', [
                    'post' => $id,
                ]);
                break;
            default:
                throw new \Exception("Unhandle comment [{$value->post_id}] change [{$value->verb}]");
        }
    }

    /**
     * @param $change
     * @param $id
     *
     * @throws \Exception
     */
    protected function handleItemChanges($change, $id)
    {
        switch ($change->value->item) {
            case 'comment':
                $this->processCommentChanges($change->value);
                break;
            case 'status':
                $this->processStatusChanges($change->value, $id);
                break;
            case 'post':
                $this->processPostChanges($change->value);
                break;
            default:
                throw new \Exception("Unhandle change item [{$change->value->item}]");
        }
    }

    /**
     * @param $entry
     *
     * @throws \Exception
     */
    protected function processEntry($entry)
    {
        $id = object_get($entry, 'id'); // Page ID

        $changes = object_get($entry, 'changes');

        foreach ($changes as $change) {
            if ($change->field != 'feed')
                return; // Do not handle other situations

            $this->handleItemChanges($change, $id);
        }
    }

    /**
     * @param $entries
     */
    protected function processEntries($entries)
    {
        foreach ($entries as $entry) {
            $this->processEntry($entry);
        }
    }
}
