/**
 * 通用广告加载脚本
 * 读取 /ads.json 并渲染到指定容器
 */
function insertAd(containerId, content){
    const container = document.getElementById(containerId);
    if(!container || !content) return;
    content = content.trim();

    // 如果内容包含 script 标签，需要特殊处理以执行脚本
    if(content.toLowerCase().includes('<script')){
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        
        // 清空容器
        container.innerHTML = '';

        // 遍历并移动所有节点
        Array.from(tempDiv.childNodes).forEach(node => {
            if (node.tagName === 'SCRIPT') {
                const newScript = document.createElement('script');
                // 复制属性
                Array.from(node.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                // 复制内容
                if(node.innerHTML){
                    newScript.text = node.innerHTML;
                }
                container.appendChild(newScript);
            } else {
                container.appendChild(node.cloneNode(true));
            }
        });
    } else {
        container.innerHTML = content;
    }

    // 针对视频顶部广告添加关闭按钮
    if (containerId === 'ad-video-top') {
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.className = 'ad-close-btn';
        closeBtn.title = '关闭广告';
        closeBtn.onclick = function(e) {
            e.stopPropagation(); // 防止冒泡
            container.style.display = 'none';
        };
        // 确保容器有定位上下文
        if (getComputedStyle(container).position === 'static') {
            container.style.position = 'relative';
        }
        container.appendChild(closeBtn);
    }
}

// 页面加载完后拉取 ads.json
document.addEventListener('DOMContentLoaded', function(){
    fetch('/ads.json?t=' + new Date().getTime())
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(ads => {
            insertAd('ad-top', ads.top);
            insertAd('ad-bottom', ads.bottom);
            insertAd('ad-left', ads.left);
            insertAd('ad-right', ads.right);
            insertAd('ad-video-top', ads.video_top);
            insertAd('ad-video-bottom', ads.video_bottom);
        })
        .catch(err => console.log('广告配置未加载:', err.message));
});
