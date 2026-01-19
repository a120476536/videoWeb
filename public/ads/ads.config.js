// 广告初始化
window.ADS_CONFIG = {
    top: { enable: true, text: '新用户注册即送 VIP' },
    left: { enable: true },
    right: { enable: true },
    bottom: { enable: true, text: '限时 VIP 8 折' }
};

function initAds() {
    if (!window.ADS_CONFIG) return;

    if (!ADS_CONFIG.top.enable) document.getElementById('ad-top').remove();
    else document.getElementById('ad-top').innerText = ADS_CONFIG.top.text;

    if (!ADS_CONFIG.left.enable) document.getElementById('ad-left').remove();
    if (!ADS_CONFIG.right.enable) document.getElementById('ad-right').remove();

    if (!ADS_CONFIG.bottom.enable) document.getElementById('ad-bottom').remove();
    else document.getElementById('ad-bottom').innerText = ADS_CONFIG.bottom.text;
}

// DOM加载完成后初始化
document.addEventListener('DOMContentLoaded', initAds);

// 关闭广告
function closeAd(type) {
    const el = document.getElementById('ad-' + type);
    el && (el.style.display = 'none');
}
