<?php

namespace Namest\Facebook\Realtime;

use Illuminate\Contracts\Events\Dispatcher;
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
     * @var Dispatcher
     */
    private $events;

    /**
     * @param Request    $request
     * @param Dispatcher $events
     * @param Writer     $log
     */
    public function __construct(Request $request, Dispatcher $events, Writer $log)
    {
        $this->request = $request;
        $this->log     = $log;
        $this->events  = $events;
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
        ini_set('always_populate_raw_post_data', - 1);

        $content = file_get_contents('php://input');

        $updates = json_decode($content);

        $object = object_get($updates, 'object');

        if ($object != 'page')
            return null; // Do not handle other situations

        $this->processEntries(object_get($updates, 'entry'));

        return new Response('Success', 200);
    }

    /**
     * @param \StdClass $value
     *
     * @throws \Exception
     */
    private function processCommentChanges($value)
    {
        $commentId = $value->comment_id;

        switch ($value->verb) {
            case 'add':
                $postId = $value->parent_id;

                /** @var Post $post */
                $post    = Post::find($postId)->sync();
                $comment = Comment::findOrSync($commentId);

                $post->comments()->save($comment);

                $this->events->fire('facebook.comment.added', [$commentId, $postId]);

                $this->log->info('New comment has been added', [
                    'post'    => $postId,
                    'comment' => $commentId,
                    'message' => $comment->message
                ]);
                break;
            case 'edited':
                $comment = Comment::findOrSync($commentId);

                $this->log->info('Comment has been edited', [
                    'comment' => $commentId,
                    'message' => $comment->message
                ]);

                $this->events->fire('facebook.comment.edited', [$commentId]);
                break;
            case 'remove':
                if ( ! ($comment = Comment::find($commentId)))
                    return;

                $comment->deleteNode();

                $this->events->fire('facebook.comment.removed', [$commentId]);

                $this->log->info('Comment has been removed', ['comment' => $commentId,]);
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

                /** @var Page $page */
                $page = Page::find($pageId)->sync();
                /** @var Post $post */
                $post = Post::findOrSync($postId);

                $page->posts()->save($post);

                $this->events->fire('facebook.post.added', [$postId, $pageId]);

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
                $id = $value->post_id;

                if ( ! ($post = Post::find($id)))
                    return;

                $post->deleteNode();

                $this->events->fire('facebook.post.removed', [$id]);

                $this->log->info('Post has been removed', ['post' => $id]);
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
