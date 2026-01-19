<?php

namespace common;
use support\Cache;
class VideoUtils
{
    public static function systemName(): string
    {
        return "神特么影视站";
    }
    public static function systemLogo():string
    {
        return '/favicon.ico';
    }
    public static function channels(): bool|string
    {
        $data = [
            "code" => 1,
            "list" => [
                [
                    "channel_id" => 2,
                    "channel_name" => "非凡",
                    "channel_url" => "http://api.ffzyapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "99",
                    "create_time" => "2025-01-03 18:07:08",
                    "update_time" => "2025-01-03 18:07:08"
                ],
                [
                    "channel_id" => 4,
                    "channel_name" => "无尽",
                    "channel_url" => "https://api.wujinapi.me/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "98",
                    "create_time" => "2025-01-08 17:44:09",
                    "update_time" => "2025-01-08 17:44:09"
                ],
                [
                    "channel_id" => 7,
                    "channel_name" => "360",
                    "channel_url" => "https://360zy.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "97",
                    "create_time" => "2025-01-15 18:05:53",
                    "update_time" => "2025-01-15 18:05:53"
                ],
                [
                    "channel_id" => 12,
                    "channel_name" => "如意",
                    "channel_url" => "https://cj.rycjapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "92",
                    "create_time" => "2025-06-25 12:56:57",
                    "update_time" => "2025-06-25 12:56:57"
                ],
                [
                    "channel_id" => 13,
                    "channel_name" => "爱祁异",
                    "channel_url" => "https://www.iqiyizyapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "92",
                    "create_time" => "2025-06-25 12:58:49",
                    "update_time" => "2025-06-25 12:58:49"
                ],
                [
                    "channel_id" => 14,
                    "channel_name" => "暴疯",
                    "channel_url" => "https://bfzyapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "92",
                    "create_time" => "2025-06-25 12:59:42",
                    "update_time" => "2025-06-25 12:59:42"
                ],
                [
                    "channel_id" => 16,
                    "channel_name" => "U酷",
                    "channel_url" => "https://api.ukuapi88.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "92",
                    "create_time" => "2025-06-25 13:03:51",
                    "update_time" => "2025-06-25 13:03:51"
                ],
                [
                    "channel_id" => 19,
                    "channel_name" => "电影天堂",
                    "channel_url" => "http://caiji.dyttzyapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "92",
                    "create_time" => "2025-06-26 11:48:05",
                    "update_time" => "2025-06-26 11:48:05"
                ],
                [
                    "channel_id" => 10,
                    "channel_name" => "蜂巢",
                    "channel_url" => "https://api.fczy888.me/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "86",
                    "create_time" => "2025-06-19 17:59:29",
                    "update_time" => "2025-06-19 17:59:29"
                ],
                [
                    "channel_id" => 8,
                    "channel_name" => "魔都",
                    "channel_url" => "https://www.mdzyapi.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "80",
                    "create_time" => "2025-06-19 17:57:34",
                    "update_time" => "2025-06-19 17:57:34"
                ],
                [
                    "channel_id" => 5,
                    "channel_name" => "木耳",
                    "channel_url" => "https://json02.heimuer.xyz/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "5",
                    "create_time" => "2025-01-08 17:47:57",
                    "update_time" => "2025-01-08 17:47:57"
                ],
                [
                    "channel_id" => 3,
                    "channel_name" => "华为",
                    "channel_url" => "https://cjhwba.com/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "3",
                    "create_time" => "2025-01-03 18:09:01",
                    "update_time" => "2025-01-03 18:09:01"
                ],
                [
                    "channel_id" => 1,
                    "channel_name" => "旺旺",
                    "channel_url" => "https://api.wwzy.tv/api.php/provide/vod/",
                    "channel_status" => "1",
                    "channel_sort" => "1",
                    "create_time" => "2025-01-03 18:05:51",
                    "update_time" => "2025-01-03 18:05:51"
                ],
            ],
            "msg" => "success"
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public static function getAvailableChannel(): ?array
    {
        // 先查缓存
        $cacheKey = 'useChannel';
        $cacheNavKey = 'useNav';
        $channel = Cache::get($cacheKey);
        $data = Cache::get($cacheNavKey);
        if ($channel&&$data) {
//            VideoLogUtils::info($channel,'渠道');
//            VideoLogUtils::info($data,'分类');
            return ['channel'=>$channel,'data'=>$data];
        }

        // 没缓存就去循环请求
        $channels = json_decode(self::channels(), true);

        foreach ($channels['list'] as $channel) {
            $url = rtrim($channel['channel_url'], '/') . '?ac=list&page=1';
            $options = [
                'http' => [
                    'method'  => 'GET',
                    'header'  => [
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
                        "Accept: application/json",
                        "Authorization: Bearer your_token_here"
                    ],
                    'timeout' => 10, // 超时秒数
                ]
            ];
            $context = stream_context_create($options);
            $resp = @file_get_contents($url,false,$context);

            if ($resp === false) {
                continue;
            }

            $data = json_decode($resp, true);
            if (is_array($data) && isset($data['code']) && $data['code'] == 1) {
                // 写缓存，有效期 10 分钟
                Cache::set($cacheKey, $channel, 6000);
                Cache::set($cacheNavKey, $data, 6000);
                return ['channel'=>$channel,'data'=>$data];
            }
        }

        return null;
    }
    public static function getNav($vodData): array
    {
        $blacklist = VideoUtils::blacklist();
        $vodArray = is_string($vodData) ? json_decode($vodData, true) : $vodData;
        $classList = $vodArray['class'] ?? [];
        $classList = array_reverse(array_values(array_filter($classList, function ($item) use ($blacklist) {
            return !in_array($item['type_name'] ?? '', $blacklist, true);
        })));
        $navItemShow = array_slice($classList, 0, 5)??[];      // 前 5 条
        $navItemMore = array_slice($classList, 5)??[];         // 剩余
        return ['navItemShow'=>$navItemShow,'navItemMore'=>$navItemMore];
    }
    public static function blacklist(): array
    {
        return ['伦理片', '限制级', '少儿不宜','伦理','限制','不宜'];
    }
    public static function getVodList($tid): ?array
    {
        $cacheKey = 'useChannel';
        $channel = Cache::get($cacheKey);
        $url = rtrim($channel['channel_url'], '/') . '?ac=detail&t='.$tid;
        $options = [
            'http' => [
                'method'  => 'GET',
                'header'  => [
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
                    "Accept: application/json",
                    "Authorization: Bearer your_token_here"
                ],
                'timeout' => 10, // 超时秒数
            ]
        ];
        $context = stream_context_create($options);
        $resp = @file_get_contents($url,false,$context);

        $data = json_decode($resp, true);
        if (is_array($data) && isset($data['code']) && $data['code'] == 1) {
            VideoLogUtils::info($data['list'],'视频列表\n');
            VideoLogUtils::info($data['code'],'视频列表Code /n');
            VideoLogUtils::info($data['msg'],'视频列表Msg /n');
            return $data;
        }
        return null;
    }
    public static function getVodDetail($channelUrl,$ids): ?array
    {
        $url = rtrim($channelUrl, '/') . '?ac=detail&ids='.$ids;
        $options = [
            'http' => [
                'method'  => 'GET',
                'header'  => [
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
                    "Accept: application/json",
                    "Authorization: Bearer your_token_here"
                ],
                'timeout' => 10, // 超时秒数
            ]
        ];
        $context = stream_context_create($options);
        $resp = @file_get_contents($url,false,$context);

        $data = json_decode($resp, true);
        if (is_array($data) && isset($data['code']) && $data['code'] == 1) {
            VideoLogUtils::info($data['list'],'视频列表');
            return $data;
        }
        return null;
    }
}