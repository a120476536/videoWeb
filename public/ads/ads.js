(function () {
    console.log('[ADS] loaded');

    // DOM 没准备好就等
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('[ADS] init');

        if (document.getElementById('__ADS_TEST__')) return;

        const div = document.createElement('div');
        div.id = '__ADS_TEST__';
        div.innerText = '广告组件已生效';

        div.style.position = 'fixed';
        div.style.bottom = '20px';
        div.style.right = '20px';
        div.style.width = '300px';
        div.style.height = '100px';
        div.style.background = 'red';
        div.style.color = '#fff';
        div.style.zIndex = '999999';
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.style.justifyContent = 'center';
        div.style.fontSize = '16px';

        document.body.appendChild(div);
    }
})();
