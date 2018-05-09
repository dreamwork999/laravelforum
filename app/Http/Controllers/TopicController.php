<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Like;
use App\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class TopicController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('topics.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,
            [
            'title' => 'required|unique:topics|min:20',
            'details' => 'required|min:100',
             //'g-recaptcha-response' => 'required|captcha'
            ],
            ['title.unique' => 'This Topic Is Already Posted.']);
        $topic = auth()->user()->topic()->create($request->all());
        return redirect(route('topic.show',$topic->id))->withMessage(__('Topic Has Been Created Successfully!'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sureDelete = __('Are you sure want to Delete?');

        $comments = Comment::where('commentable_id',$id)->orderBy('id', 'asc')->paginate(5);
        $topic = Topic::findOrFail($id);
        $topic->addPageView();
        $topicsCount = Topic::where('user_id', $topic->user->id)->get();
        if($topic->likes()->count() > 0){
            $like = Like::where('likeable_id', $topic->id)->first();
            $liked_user = $like->user_id;
        }else{
            $liked_user = 0;
        }

        return view('topics.show',compact('topic','sureDelete','topicsCount','comments','liked_user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $topic = Topic::findOrFail($id);
        if(Auth::user()->id != $topic->user->id){
            return redirect('/');
        }
        return view('topics.edit',compact('topic'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        if(Auth::user()->id != $topic->user->id){
            return redirect('/');
        }

        $this->validate($request,
            [
                'title' => 'required|min:20|unique:topics,title,'.$id,
                'details' => 'required|min:20',
                //'g-recaptcha-response' => 'required|captcha'
            ],
            ['title.unique' => 'This Topic Is Already Posted.']
        );
        $topic->update($request->all());
        return redirect(route('topic.show',$id))->withMessage(__('Topic Has Been Updated Successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $topic = Topic::findOrFail($id);
        if(Auth::user()->id != $topic->user->id){
            return redirect('/');
        }
        $topic->delete();
        return redirect('/')->withMessage(__('Topic Has Been Deleted!'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bestAnswer()
    {
        $commentId = Input::get('commentId');
        $topicId = Input::get('topicId');
        $topic = Topic::find($topicId);
        $topic->best_answer = $commentId;
        if ($topic->save()) {
            if (request()->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'marked as best answer.']);
            }
        }
        return back()->withMessage('Marked as Best Answer.');
    }


}
