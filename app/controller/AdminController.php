<?php
namespace app\controller;

use support\Request;

class AdminController
{
    private $username = 'admin';
    private $password = '123456';

    // 登录页
    public function loginPage(Request $request)
    {
        return view('admin/login');
    }

    // 登录处理
    public function doLogin(Request $request)
    {
        $user = $request->post('username');
        $pass = $request->post('password');

        if ($user === $this->username && $pass === $this->password) {
            $request->session()->set('admin', true);
            return redirect('/admin/dashboard'); // Webman redirect
        } else {
            return view('admin/login', ['error' => '账号或密码错误']);
        }
    }

    // 仪表盘
    public function dashboard(Request $request)
    {
        $this->checkLogin($request);
        return view('admin/dashboard', ['currentPath' => $request->path()]);
    }

    // 广告配置页
    public function adsPage(Request $request)
    {
        $this->checkLogin($request);
        $ads = file_exists(runtime_path() . '/ads.json')
            ? json_decode(file_get_contents(runtime_path() . '/ads.json'), true)
            : [];
        return view('admin/ads', ['ads' => $ads, 'currentPath' => $request->path()]);
    }

    // 保存广告
    public function saveAds(Request $request)
    {
        $this->checkLogin($request);

        $ads = [
            'top' => $request->post('top', ''),
            'bottom' => $request->post('bottom', ''),
            'left' => $request->post('left', ''),
            'right' => $request->post('right', ''),
            'video_bottom' => $request->post('video_bottom', '')
        ];
        file_put_contents(runtime_path() . '/ads.json', json_encode($ads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return redirect('/admin/ads');
    }

    // 退出登录
    public function logout(Request $request)
    {
        $request->session()->delete('admin');
        return redirect('/admin/login');
    }

    // 登录检查
    private function checkLogin(Request $request)
    {
        if (!$request->session()->get('admin')) {
            return redirect('/admin/login');
        }
    }
}
