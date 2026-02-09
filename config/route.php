<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;
use app\controller\AdminController;





Route::get('/', [app\controller\IndexController::class, 'index']);
Route::get('/index/nav', [app\controller\IndexController::class, 'nav']);
Route::post('/index/search-hit', [app\controller\IndexController::class, 'searchHit']);


// 登录页面
Route::get('/admin/login', [AdminController::class, 'loginPage']);
Route::post('/admin/login', [AdminController::class, 'doLogin']);

// 仪表盘
Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

// 广告配置
Route::get('/admin/ads', [AdminController::class, 'adsPage']);
Route::post('/admin/ads', [AdminController::class, 'saveAds']);

// 渠道管理
Route::get('/admin/channels', [AdminController::class, 'channelsPage']);
Route::post('/admin/channels', [AdminController::class, 'saveChannels']);

// 前端读取广告
Route::get('/ads.json', [app\controller\IndexController::class, 'getAds']);

// 登出
Route::get('/admin/logout', [AdminController::class, 'logout']);
// 主题切换
Route::post('/admin/theme', [AdminController::class, 'saveTheme']);
