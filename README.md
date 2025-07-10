# 随机多源视频播放 - Apple风格UI

基于 Maccms JSON 接口的随机视频播放前端页面，支持多接口选择和自动随机播放功能。

---

## 功能简介

- 选择不同的 Maccms JSON 视频接口
- 随机播放视频（支持多播放源切换）
- 播放完成后自动随机播放下一个视频
- 无需点击即可连续播放
- 支持 HLS (m3u8) 和普通视频格式播放
- 记录无效视频ID，避免重复请求无效数据

---

## 安装说明

### 环境要求

- PHP 7.4 及以上
- Apache 或 Nginx 服务器
- 可访问外部 Maccms JSON API

### 部署步骤

1. 将前端页面文件上传到服务器 Web 根目录或子目录。
2. 配置并部署后台 API，包含以下接口：
   - `/api/list_apis.php` ：返回可用 Maccms JSON 接口列表
   - `/api/add_invalid_id.php` ：记录无效视频 ID
   - `/api/get_invalid_ids.php` ：获取无效视频 ID 列表
   - `/api/proxy.php` ：代理请求 Maccms JSON 接口，解决跨域问题
3. 在前端代码中，修改接口地址变量为实际后台 API 路径。

### 配置示例（前端代码中）

```js
const ADD_INVALID_ID_API = '/api/add_invalid_id.php';
const GET_INVALID_IDS_API = '/api/get_invalid_ids.php';
const PROXY_API = '/api/proxy.php';
const API_LIST_URL = '/api/list_apis.php';
示例 API 返回格式（list_apis.php）
json
复制
编辑
{
  "parse_url": "https://yourparseurl.com/parse?url=",
  "apis": [
    { "name": "本地Maccms接口", "api_url": "http://your-maccms-site.com/api.php/provide/vod?ac=detail" },
    { "name": "远程Maccms接口", "api_url": "http://remote-maccms.com/api.php/provide/vod?ac=detail" }
  ]
}
使用说明
打开前端页面，选择你想使用的 Maccms JSON 接口。

点击“随机播放”按钮，开始随机视频播放。

视频播放结束后会自动随机播放下一个视频。

可手动切换播放源按钮。

注意事项
目前仅支持 Maccms JSON 视频详情接口。

需确保后台 API 正确实现跨域代理和无效 ID 记录功能。

推荐使用支持 HLS.js 的现代浏览器，如 Chrome、Safari 等。

未来计划
支持更多视频接口类型

增强播放器兼容性和功能

提供更友好的后台管理界面

欢迎 Issues 和 Pull Requests！
感谢使用本项目！

 

 








 
