/**
 * 通用广告加载脚本（iframe 渲染）
 * 读取 /ads.json 并渲染到指定容器
 */
function ensureAdStyles() {
    if (document.getElementById('ads-style')) return;
    const link = document.createElement('link');
    link.id = 'ads-style';
    link.rel = 'stylesheet';
    link.href = '/ads/ads.css?v=2';
    document.head.appendChild(link);
}

function buildIframe(content, options) {
    const iframe = document.createElement('iframe');
    iframe.className = 'ad-iframe';
    iframe.setAttribute('scrolling', 'no');
    iframe.setAttribute('loading', 'lazy');
    iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms allow-popups');
    iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
    iframe.style.width = '100%';
    iframe.style.height = options.height;
    iframe.style.border = '0';

    const trimmed = content.trim();
    const isUrl = /^https?:\/\//i.test(trimmed);
    const isImageUrl = /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(trimmed)
        || /(?:fmt=|image|img|jpg|jpeg|png|webp|gif)/i.test(trimmed);
    if (isUrl) {
        if (isImageUrl) {
            iframe.srcdoc = `<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;display:flex;align-items:center;justify-content:center;background:#fff;">
<img src="${trimmed}" alt="ad" style="max-width:100%;max-height:100%;width:100%;height:100%;object-fit:contain;">
</body>
</html>`;
        } else {
            iframe.src = trimmed;
        }
    } else {
        const hasHtml = /<[^>]+>/.test(trimmed);
        const safeText = trimmed.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const body = hasHtml
            ? trimmed
            : `<div class="ad-text">${safeText}</div>`;
        iframe.srcdoc = `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<base target="_blank">
<style>
*{box-sizing:border-box;}
body{margin:0;font-family:"Microsoft YaHei",Arial,sans-serif;}
.ad-text{
    width:100%;
    height:100%;
    min-height:60px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    font-size:18px;
    font-weight:700;
    color:#0f172a;
    background:linear-gradient(135deg,#e0f2fe 0%, #bae6fd 40%, #7dd3fc 100%);
    letter-spacing:.5px;
}
</style>
</head>
<body>${body}</body>
</html>`;
    }
    return iframe;
}

function renderPlaceholder(container, label, options) {
    container.classList.add('ad-slot');
    if (options.className) {
        options.className.split(/\s+/).filter(Boolean).forEach(cls => container.classList.add(cls));
    }
    container.style.height = options.height;
    container.innerHTML = '';
    const placeholder = document.createElement('div');
    placeholder.className = 'ad-placeholder';
    placeholder.innerHTML = `<div class="ad-placeholder-title">${label}</div><div class="ad-placeholder-size">${options.size}</div>`;
    container.appendChild(placeholder);
}

function insertAd(containerId, content, label, options) {
    const container = document.getElementById(containerId);
    if (!container) return;
    ensureAdStyles();

    if (!options.enabled) {
        container.style.display = 'none';
        container.innerHTML = '';
        return;
    }
    container.style.display = '';

    if (!content || !content.trim()) {
        renderPlaceholder(container, label, options);
        return;
    }

    container.classList.add('ad-slot');
    if (options.className) {
        options.className.split(/\s+/).filter(Boolean).forEach(cls => container.classList.add(cls));
    }
    container.style.height = options.height;
    if (options.width) {
        const widthVal = parseFloat(options.width);
        const isPercentWidth = !Number.isNaN(widthVal)
            && widthVal > 0
            && widthVal <= 100
            && /ad-(top|bottom|video)/.test(options.className);
        container.style.width = isPercentWidth ? `${widthVal}%` : options.width;
        container.style.marginLeft = 'auto';
        container.style.marginRight = 'auto';
    }
    container.innerHTML = '';
    container.appendChild(buildIframe(content, options));
}

// 页面加载完后拉取 ads.json
function initAds() {
    const map = {
        'ad-top': { label: '顶部横幅广告', className: 'ad-top', height: '90px', size: '100% × 90' },
        'ad-bottom': { label: '底部横幅广告', className: 'ad-bottom', height: '90px', size: '100% × 90' },
        'ad-left': { label: '左侧悬浮广告', className: 'ad-side ad-left', height: '260px', size: '120 × 260' },
        'ad-right': { label: '右侧悬浮广告', className: 'ad-side ad-right', height: '260px', size: '120 × 260' },
        'ad-video-top': { label: '播放器顶部广告', className: 'ad-inline ad-video-top', height: '80px', size: '100% × 80' },
        'ad-video-bottom': { label: '播放器底部广告', className: 'ad-inline ad-video-bottom', height: '120px', size: '100% × 120' },
    };

    if (document.getElementById('ad-top')) document.body.classList.add('has-ad-top');
    if (document.getElementById('ad-bottom')) document.body.classList.add('has-ad-bottom');

    fetch('/ads.json?t=' + new Date().getTime())
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(ads => {
            const norm = (key, fallback) => {
                const raw = ads && ads[key] !== undefined ? ads[key] : '';
                if (typeof raw === 'string') {
                    return {
                        enabled: raw.trim() !== '',
                        content: raw,
                        width: fallback.width || '',
                        height: fallback.height || '',
                    };
                }
                return {
                    enabled: raw.enabled !== undefined ? !!raw.enabled : (raw.content || raw.html || '') !== '',
                    content: raw.content || raw.html || '',
                    width: raw.width || fallback.width || '',
                    height: raw.height || fallback.height || '',
                };
            };

            const top = norm('top', map['ad-top']);
            const bottom = norm('bottom', map['ad-bottom']);
            const left = norm('left', map['ad-left']);
            const right = norm('right', map['ad-right']);
            const videoTop = norm('video_top', map['ad-video-top']);
            const videoBottom = norm('video_bottom', map['ad-video-bottom']);

            map['ad-top'].height = (top.height ? `${top.height}px` : map['ad-top'].height);
            map['ad-bottom'].height = (bottom.height ? `${bottom.height}px` : map['ad-bottom'].height);
            map['ad-left'].height = (left.height ? `${left.height}px` : map['ad-left'].height);
            map['ad-right'].height = (right.height ? `${right.height}px` : map['ad-right'].height);
            map['ad-video-top'].height = (videoTop.height ? `${videoTop.height}px` : map['ad-video-top'].height);
            map['ad-video-bottom'].height = (videoBottom.height ? `${videoBottom.height}px` : map['ad-video-bottom'].height);

            map['ad-top'].width = top.width ? `${top.width}px` : '';
            map['ad-bottom'].width = bottom.width ? `${bottom.width}px` : '';
            map['ad-left'].width = left.width ? `${left.width}px` : '';
            map['ad-right'].width = right.width ? `${right.width}px` : '';
            map['ad-video-top'].width = videoTop.width ? `${videoTop.width}px` : '';
            map['ad-video-bottom'].width = videoBottom.width ? `${videoBottom.width}px` : '';

            insertAd('ad-top', top.content, map['ad-top'].label, { ...map['ad-top'], enabled: top.enabled });
            insertAd('ad-bottom', bottom.content, map['ad-bottom'].label, { ...map['ad-bottom'], enabled: bottom.enabled });
            insertAd('ad-left', left.content, map['ad-left'].label, { ...map['ad-left'], enabled: left.enabled });
            insertAd('ad-right', right.content, map['ad-right'].label, { ...map['ad-right'], enabled: right.enabled });
            insertAd('ad-video-top', videoTop.content, map['ad-video-top'].label, { ...map['ad-video-top'], enabled: videoTop.enabled });
            insertAd('ad-video-bottom', videoBottom.content, map['ad-video-bottom'].label, { ...map['ad-video-bottom'], enabled: videoBottom.enabled });

            // 兜底：若渲染后仍为空，尝试再次渲染
            ['ad-video-top', 'ad-video-bottom'].forEach(id => {
                const el = document.getElementById(id);
                if (el && el.innerHTML.trim() === '') {
                    const data = id === 'ad-video-top' ? videoTop : videoBottom;
                    insertAd(id, data.content, map[id].label, { ...map[id], enabled: data.enabled });
                }
            });

            if (!top.enabled) document.body.classList.remove('has-ad-top');
            if (!bottom.enabled) document.body.classList.remove('has-ad-bottom');
        })
        .catch(err => console.log('广告配置未加载:', err.message));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAds);
} else {
    initAds();
}

window.addEventListener('pageshow', () => setTimeout(initAds, 0));
setTimeout(initAds, 300);
