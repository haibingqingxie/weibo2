<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        // 中间件auth过滤请求，except->除了*意外，都需要验证
        // 我觉得只有登录后才能查看用户列表和用户个人页面，show,index不应该放在例外中
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);
        // 中间件guest过滤请求，only->只允许*访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    // 注册用户
    public function create()
    {
        return view('users.create');
    }

    // 展示用户
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // 保存注册信息
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user->id]);
    }

    // 修改用户页面
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    // 更新修改信息
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    // 删除其他用户
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();

        session()->flash('success', '删除用户成功！');
        return back();
    }
}
