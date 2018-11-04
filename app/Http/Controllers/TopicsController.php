<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\TopicRequest;
use Auth;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request)
	{
		$topics = Topic::select('id', 'user_id', 'category_id', 'title', 'reply_count', 'updated_at')
            ->with([
                'category' => function ($query) {
                    return $query->select('id', 'name');
                },
                'user' => function ($query) {
                    return $query->select('id', 'name', 'avatar');
                }
            ])
            ->withOrder($request->order)
            ->paginate(30);

		return view('topics.index', compact('topics'));
	}

    public function show(Request $request, Topic $topic)
    {
        if (!empty($topic->slug) && $request->slug != $topic->slug) {
            return redirect()->to($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
		return view('topics.create_and_edit', compact('topic'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
        $topic->fill($request->all());
	    $topic->user_id = Auth::id();
		$topic->save();
		return redirect()->to($topic->link())->with('message', '成功创建主题！');
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

		return redirect()->to($topic->link())->with('message', '更新成功！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('message', '成功删除！');
	}

	public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认是失败的
        $data = [
            'success'   => false,
            'msg'       => '上传失败!',
            'file_path' => ''
        ];
        // 判断是否有上传文件,　并赋值给　$file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($request->upload_file, 'topics', Auth::id(), 1024);
            // 图片保存成功的话
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg']       = "上传成功!";
                $data['success']   = true;
            }
        }

        return $data;
    }
}