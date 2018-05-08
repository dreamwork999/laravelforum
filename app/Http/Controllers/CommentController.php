<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function storeComment(Request $request,Topic $topic)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|min:20',
        ]);
        if ($validator->fails()) {
            Session::flash('commentcreateerror','Comment is Required and minimum 20 characters');
            return redirect(route('topic.show',$topic->id.'#lf_comment_create_form'));
        }

        $comment = new Comment();
        $comment->body = $request->body;
        $comment->user_id = Auth::user()->id;
        $comment = $topic->comments()->save($comment);
        return redirect(route('topic.show',$topic->id.'#commentno'.$comment->id));
    }


    public function storeReply(Request $request,Comment $comment)
    {
        $validator = Validator::make($request->all(), [
            'replybody' => 'required|min:20',
        ]);
        if ($validator->fails()) {
            Session::flash('replybody'.$comment->id,'Reply is Required and minimum 20 characters');
            return redirect(route('topic.show',$comment->commentable_id.'#commentno'.$comment->id));
        }

        $reply = new Comment();
        $reply->body = $request->replybody;
        $reply->user_id = Auth::user()->id;
        $reply->comments()->save($reply);
        return redirect(route('topic.show',$topic->id.'#replyno'.$comment->id));
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        if(Auth::user()->id != $comment->user_id){
            return redirect('/');
        }
        $validator = Validator::make($request->all(), [
            'editcommentbody' => 'required|min:20',
        ]);
        if ($validator->fails()) {
            Session::flash('editcommentbody'.$comment->id,'Comment is Required and minimum 20 characters');
            return redirect(route('topic.show',$comment->commentable_id.'#commentno'.$comment->id));
        }
        $comment->update([
            'body' => $request->editcommentbody,
        ]);
        return redirect(route('topic.show',$comment->commentable_id.'#commentno'.$comment->id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $commentdata = Comment::findOrFail($id);
        if(Auth::user()->id != $commentdata->user_id){
            return redirect('/');
        }
        $commentdata->delete();
        Session::flash('commentmessage', "Comment Deleted");
        return redirect(route('topic.show',$commentdata->commentable_id.'#lf_comments_wrap'));
    }
}
