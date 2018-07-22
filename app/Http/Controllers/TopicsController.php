<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use Auth;
use App\Handlers\ImageUploadHandler;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic)
	{
		$topics = $topic->withOrder($request->order)->paginate(30);

		return view('topics.index', compact('topics'));
	}

    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
	    $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	//新建文章
	public function store(TopicRequest $request, Topic $topic)
	{
	    $topic->fill($request->all());
	    $topic->user_id = Auth::id();
	    $topic->save();

		return redirect()->route('topics.show', $topic->id)->with('success', '成功创建话题！');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
		return view('topics.create_and_edit', compact('topic'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('success', '更新成功！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', '删除成功');
	}

	//上传图片
    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        //初始化数据，默认失败
        $data = [
            'success' => false,
            'msg'     => '上传失败',
            'file_path' => ''
        ];

        //判断是否上传图片，并赋值给$file;
        if ($file = $request->upload_file) {
            //保存图片到本地
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(),1024);

            //图片保存成功
            if ($result) {
                $data['success'] = true;
                $data['msg']     = '上传成功';
                $data['file_path'] = $result['path'];
            }
        }

        return $data;
    }
}