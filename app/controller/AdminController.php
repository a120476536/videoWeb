<?php
namespace app\controller;

use support\Request;
use common\VideoLogUtils;

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
        $adsFile = runtime_path() . '/ads.json';
        $ads = file_exists($adsFile)
            ? json_decode(file_get_contents($adsFile), true)
            : [];
        return view('admin/ads', ['ads' => $ads, 'currentPath' => $request->path()]);
    }

    // 保存广告
    public function saveAds(Request $request)
    {
        $this->checkLogin($request);

        // 获取参数并进行 HTML 实体解码，防止被转义
        $top = htmlspecialchars_decode($request->post('top', ''));
        $bottom = htmlspecialchars_decode($request->post('bottom', ''));
        $left = htmlspecialchars_decode($request->post('left', ''));
        $right = htmlspecialchars_decode($request->post('right', ''));
        $video_top = htmlspecialchars_decode($request->post('video_top', ''));
        $video_bottom = htmlspecialchars_decode($request->post('video_bottom', ''));

        $ads = [
            'top' => $top,
            'bottom' => $bottom,
            'left' => $left,
            'right' => $right,
            'video_top' => $video_top,
            'video_bottom' => $video_bottom
        ];
        
        // 使用 runtime_path 确保路径正确
        $adsFile = runtime_path() . '/ads.json';
        
        // 记录日志
        VideoLogUtils::info([
            'action' => 'saveAds',
            'file' => $adsFile,
            'data' => $ads
        ], 'admin_ads');

        $result = file_put_contents($adsFile, json_encode($ads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        if ($result === false) {
            VideoLogUtils::warning("Failed to write ads to $adsFile", 'admin_ads');
        }

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
