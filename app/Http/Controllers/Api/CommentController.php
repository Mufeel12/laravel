<?php

namespace App\Http\Controllers\Api;

use App\Comments;
use App\Http\Controllers\Controller;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Spark\Repositories\TeamRepository;
use Laravel\Spark\Spark;

class CommentController extends Controller
{
    /**
     * List all comments for a video by video id
     *
     * @param   int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index($id, Request $request)
    {
        $video_file = Video::findOrfail($id);
        $comments = $video_file->comments;

        /**
         * toTree() builds comment hierarchy: parents and children
         */
        $comments = Comments::clearComments($comments->toTree());

        return $comments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param   Request $request
     * @param   int $id
     * @return  Response
     */
    public function store(Request $request, $id)
    {
        $video_file = Video::findOrfail($id);

        /**
         * In this case I fetch all data from request... need test for `parent_id`
         */
        $data = $request->all(); // Need do it better. If only request have 'is_readed'. need update just read
        //-------------------------------------------------------------------------
        $parent_id = $request->input('parent_id');
        $parent = $video_file->comments->find($parent_id); // Must return null if no parent found
        $user = Auth::user();

        /**
         * Commenting
         */
        # remove meta-values
        if (isset($data['returnHtml']))
            unset($data['returnHtml']);
        # 91919191919191919191919191 special value: means don't save video time at all
        if ($data['video_time'] == 91919191919191919191919191)
            $data['video_time'] = null;
        if (!isset($data['creator_id']) || empty($data['creator_id']))
            $data['creator_id'] = $user->id;

        $comment = $video_file->comment($data, $user, $parent);

        $comment = Comments::clearComments($comment);

        $teamId = $video_file->team;
        $team = Spark::interact(TeamRepository::class . '@find', [$teamId]);
        $usersInvolved = $team->users();
        $creator = $user;

        if (($usersInvolved)) {
            $usersInvolved->each(function ($user) use ($comment, $video_file, $creator) {
                // For all except current user
                if ($user->id != $comment->creator_id) {
                    $user->notify(new \App\Notifications\Comment($video_file, $creator, $user, $comment));
                }
            });

        }
	
		$comment->commented_at = date('M j, Y', strtotime($comment->created_at));
		$comment->showReplyInput = false;
		$comment->showReply = false;

        return response()->json($comment);
    }

    /**
     * Display comment for video file by video file and comment id's
     *
     * @param   int $id
     * @param   int $comment_id
     * @return  Response
     */
    public function show($id, $comment_id)
    {

        $comment = Video::findOrFail($id)->comments->find($comment_id);
        $comment = $this->clearComments($comment);

        return $comment;
    }

    /**
     * Update comment
     *
     * @param   Request $request
     * @param   int $id
     * @param   int $comment_id
     * @return  Response
     */
    public function update(Request $request, $id, $comment_id)
    {
        $video_file = Video::findOrfail($id);

        if ($request->input('mark_as_resolved'))
            $data = ['is_resolved' => 1];
        else
            /**
             * In this case I fetch all data from request... need test for `parent_id`
             */
            $data = $request->all(); // Need do it better. If only request have 'is_readed'. need update just read
        //-------------------------------------------------------------------------

        /**
         * Updating comment
         */
        $comment = $video_file->updateComment($comment_id, $data);
        $comment = Comments::clearComments($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id, $comment_id)
    {
        $video_file = Video::findOrfail($id);
        $video_file->deleteComment($comment_id);
    }
}
