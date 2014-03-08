<?php

use Lio\Core\CommandBus;
use Lio\Forum\Replies\ReplyQueryStringGenerator;
use Lio\Forum\Replies\ReplyRepository;
use Lio\Forum\Replies\Commands;
use Lio\Forum\Threads\ThreadRepository;

class ForumRepliesController extends \BaseController
{
    private $replies;
    private $threads;
    private $queryStringGenerator;
    private $bus;

    private $repliesPerPage = 20;

    function __construct(ReplyRepository $replies, ThreadRepository $threads, CommandBus $bus, ReplyQueryStringGenerator $queryStringGenerator)
    {
        $this->replies = $replies;
        $this->bus = $bus;
        $this->queryStringGenerator = $queryStringGenerator;
        $this->threads = $threads;
    }

    public function getReplyRedirect($threadSlug, $replyId)
    {
        $reply = $this->replies->requireById($replyId);
        $queryString = $this->queryStringGenerator->generate($reply, $this->repliesPerPage);

        return $this->redirectTo(action('ForumThreadsController@getShowThread', [$threadSlug]) . $queryString);
    }

    public function postCreate($threadSlug)
    {
        $thread = $this->threads->requireBySlug($threadSlug);

        $command = new Commands\CreateReplyCommand($thread, Input::get('body'), Auth::user());
        $reply = $this->bus->execute($command);
        return $this->redirectAction('ForumRepliesController@getReplyRedirect', [$thread->slug, $reply->id]);
    }

    public function getUpdate($replyId)
    {
        $reply = $this->replies->requireById($replyId);

        $this->title = "Update Forum Reply";
        $this->view('forum.replies.update', compact('reply'));
    }

    public function postUpdate($replyId)
    {
        $reply = $this->replies->requireById($replyId);

        $command = new Commands\UpdateReplyCommand($reply, Input::get('body'));
        $reply = $this->bus->execute($command);
        return $this->redirectAction('ForumRepliesController@getReplyRedirect', [$reply->thread->slug, $reply->id]);

    }

    public function getDelete($replyId)
    {

    }

    public function postDelete($replyId)
    {

    }
} 