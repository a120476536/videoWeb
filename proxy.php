<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/vnd.apple.mpegurl');

// 获取要代理的URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    http_response_code(400);
    die('URL参数不能为空');
}

// 验证URL格式
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('无效的URL格式');
}

// 设置请求头
$options = [
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: */*',
            'Accept-Language: zh-CN,zh;q=0.9',
            'Referer: http://127.0.0.1:8787/',
            'Origin: http://127.0.0.1:8787'
        ]),
        'timeout' => 30
    ]
];

$context = stream_context_create($options);

try {
    $content = file_get_contents($url, false, $context);

    if ($content === false) {
        http_response_code(500);
        die('无法获取远程内容');
    }

    // 如果是M3U8文件，处理其中的相对路径
    if (strpos($url, '.m3u8') !== false) {
        $baseUrl = dirname($url) . '/';
        $content = preg_replace_callback('/(.*\.(ts|m3u8|key))(\?.*)?$/m', function($matches) use ($baseUrl) {
            $filename = $matches[1];
            $query = isset($matches[3]) ? $matches[3] : '';

            // 如果是绝对路径或完整URL，直接返回
            if (strpos($filename, '://') !== false || substr($filename, 0, 2) == '//') {
                return $filename . $query;
            }

            // 如果是相对路径，转换为绝对路径
            return '/proxy.php?url=' . urlencode($baseUrl . $filename) . $query;
        }, $content);
    }

    echo $content;

} catch (Exception $e) {
    http_response_code(500);
    die('代理请求失败: ' . $e->getMessage());
}
?>