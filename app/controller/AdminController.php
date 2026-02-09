<?php
namespace app\controller;

use support\Request;
use support\Cache;
use common\VideoLogUtils;
use common\VideoUtils;

class AdminController
{
    private $username = 'admin';
    private $password = '123456';

    // 登录页
    public function loginPage(Request $request)
    {
        return view('admin/login', ['theme' => $this->getTheme()]);
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
        $adsFile = runtime_path() . '/ads.json';
        $ads = file_exists($adsFile)
            ? json_decode(file_get_contents($adsFile), true)
            : [];
        $ads = $this->normalizeAdsConfig($ads);

        $enabledPositions = [];
        foreach ($ads as $key => $cfg) {
            if (!empty($cfg['enabled'])) {
                $enabledPositions[] = $key;
            }
        }

        $channelsData = json_decode(VideoUtils::channels(), true);
        $channels = $channelsData['list'] ?? [];
        $enabledChannels = array_values(array_filter($channels, function ($c) {
            return ($c['channel_status'] ?? '1') === '1';
        }));

        $currentChannel = Cache::get('useChannel');
        $history = Cache::get('useChannelHistory', []);
        if (!is_array($history)) {
            $history = [];
        }
        $searchHits = Cache::get('searchSuccessChannels', []);
        if (!is_array($searchHits)) {
            $searchHits = [];
        }

        return view('admin/dashboard', [
            'currentPath' => $request->path(),
            'adsEnabledCount' => count($enabledPositions),
            'adsEnabledPositions' => $enabledPositions,
            'channelsEnabledCount' => count($enabledChannels),
            'channelsTotalCount' => count($channels),
            'currentChannel' => $currentChannel,
            'recentChannels' => $history,
            'searchHits' => $searchHits,
            'theme' => $this->getTheme(),
        ]);
    }

    // 广告配置页
    public function adsPage(Request $request)
    {
        $this->checkLogin($request);
        $adsFile = runtime_path() . '/ads.json';
        $ads = file_exists($adsFile)
            ? json_decode(file_get_contents($adsFile), true)
            : [];
        $ads = $this->normalizeAdsConfig($ads);
        return view('admin/ads', [
            'ads' => $ads,
            'currentPath' => $request->path(),
            'theme' => $this->getTheme(),
        ]);
    }

    // 渠道管理页
    public function channelsPage(Request $request)
    {
        $this->checkLogin($request);
        $channelsData = json_decode(VideoUtils::channels(), true);
        $channels = $channelsData['list'] ?? [];
        return view('admin/channels', [
            'channels' => $channels,
            'currentPath' => $request->path(),
            'theme' => $this->getTheme(),
        ]);
    }

    // 保存后台主题
    public function saveTheme(Request $request)
    {
        $this->checkLogin($request);
        $theme = trim((string)$request->post('theme', 'green'));
        $allowed = ['green', 'winter', 'finance', 'tech'];
        if (!in_array($theme, $allowed, true)) {
            $theme = 'green';
        }
        $file = runtime_path() . '/admin_theme.json';
        file_put_contents($file, json_encode(['theme' => $theme], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return redirect('/admin/dashboard');
    }

    // 保存渠道配置
    public function saveChannels(Request $request)
    {
        $this->checkLogin($request);
        $rows = $request->post('channels', []);
        if (!is_array($rows)) {
            $rows = [];
        }

        $now = date('Y-m-d H:i:s');
        $existing = json_decode(VideoUtils::channels(), true);
        $maxId = 0;
        foreach (($existing['list'] ?? []) as $item) {
            $maxId = max($maxId, (int)($item['channel_id'] ?? 0));
        }

        $list = [];
        foreach ($rows as $row) {
            $name = trim((string)($row['channel_name'] ?? ''));
            $url = trim((string)($row['channel_url'] ?? ''));
            if ($name === '' || $url === '') {
                continue;
            }

            $id = (int)($row['channel_id'] ?? 0);
            if ($id <= 0) {
                $id = ++$maxId;
            }

            $createTime = trim((string)($row['create_time'] ?? ''));
            if ($createTime === '') {
                $createTime = $now;
            }

            $list[] = [
                'channel_id' => $id,
                'channel_name' => $name,
                'channel_url' => $url,
                'channel_status' => (string)((int)($row['channel_status'] ?? 0)),
                'channel_sort' => (string)((int)($row['channel_sort'] ?? 0)),
                'create_time' => $createTime,
                'update_time' => $now,
            ];
        }

        $data = [
            'code' => 1,
            'list' => $list,
            'msg' => 'success'
        ];

        $channelsFile = runtime_path() . '/channels.json';
        $result = file_put_contents($channelsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        VideoLogUtils::info([
            'action' => 'saveChannels',
            'file' => $channelsFile,
            'count' => count($list),
            'result' => $result !== false
        ], 'admin_channels');

        Cache::delete('useChannel');
        Cache::delete('useNav');

        return redirect('/admin/channels');
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
            'top' => [
                'enabled' => (bool)$request->post('top_enabled', false),
                'content' => $top,
                'width' => (int)$request->post('top_width', 0),
                'height' => (int)$request->post('top_height', 90),
            ],
            'bottom' => [
                'enabled' => (bool)$request->post('bottom_enabled', false),
                'content' => $bottom,
                'width' => (int)$request->post('bottom_width', 0),
                'height' => (int)$request->post('bottom_height', 90),
            ],
            'left' => [
                'enabled' => (bool)$request->post('left_enabled', false),
                'content' => $left,
                'width' => (int)$request->post('left_width', 120),
                'height' => (int)$request->post('left_height', 260),
            ],
            'right' => [
                'enabled' => (bool)$request->post('right_enabled', false),
                'content' => $right,
                'width' => (int)$request->post('right_width', 120),
                'height' => (int)$request->post('right_height', 260),
            ],
            'video_top' => [
                'enabled' => (bool)$request->post('video_top_enabled', false),
                'content' => $video_top,
                'width' => (int)$request->post('video_top_width', 0),
                'height' => (int)$request->post('video_top_height', 80),
            ],
            'video_bottom' => [
                'enabled' => (bool)$request->post('video_bottom_enabled', false),
                'content' => $video_bottom,
                'width' => (int)$request->post('video_bottom_width', 0),
                'height' => (int)$request->post('video_bottom_height', 120),
            ],
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

    private function normalizeAdsConfig(array $ads): array
    {
        $defaults = [
            'top' => ['enabled' => true, 'content' => '', 'width' => 0, 'height' => 90],
            'bottom' => ['enabled' => true, 'content' => '', 'width' => 0, 'height' => 90],
            'left' => ['enabled' => true, 'content' => '', 'width' => 120, 'height' => 260],
            'right' => ['enabled' => true, 'content' => '', 'width' => 120, 'height' => 260],
            'video_top' => ['enabled' => true, 'content' => '', 'width' => 0, 'height' => 80],
            'video_bottom' => ['enabled' => true, 'content' => '', 'width' => 0, 'height' => 120],
        ];

        foreach ($defaults as $key => $def) {
            if (!isset($ads[$key])) {
                $ads[$key] = $def;
                continue;
            }
            if (is_string($ads[$key])) {
                $ads[$key] = array_merge($def, [
                    'content' => $ads[$key],
                    'enabled' => trim($ads[$key]) !== '',
                ]);
            } else {
                $ads[$key] = array_merge($def, $ads[$key]);
            }
        }
        return $ads;
    }

    private function getTheme(): string
    {
        $file = runtime_path() . '/admin_theme.json';
        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (is_array($data) && !empty($data['theme'])) {
                return (string)$data['theme'];
            }
        }
        return 'green';
    }
}
