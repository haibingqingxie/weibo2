<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        // 中间件guest过滤请求，only->只允许*访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 登录页面
    public function create()
    {
        return view('sessions.create');
    }

    // 提交登录信息
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            session()->flash('success', '欢迎回来！');
            // intended()方法访问将页面重定向到上一次请求尝试访问的页面上，
            // 并接收一个默认跳转地址参数($fallback)，当上一次请求记录为空时，跳转到默认地址上
            $fallback = route('users.show', [Auth::user()]);
            return redirect()->intended($fallback);
        } else {
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    // 注销登录
    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
