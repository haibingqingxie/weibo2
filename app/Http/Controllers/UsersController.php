<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        // 中间件auth过滤请求，except->除了*意外，都需要验证
        // 我觉得只有登录后才能查看用户列表和用户个人页面，show,index不应该放在例外中
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
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

        // Auth::login($user);
        // session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        // return redirect()->route('users.show', [$user->id]);

        // 邮箱激活账号
        $this->sendEmailValidationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
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

    // 发送邮件
    protected function sendEmailValidationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'jasonli@seasidecrab.com';
        $name = 'Jason Li';
        $to = $user->email;
        $subject = '感谢注册 Weibo App 应用，请确认你的邮箱。';
        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    // 确认邮件
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', $user->id);
    }
}
