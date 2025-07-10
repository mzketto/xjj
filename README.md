安装说明（仅支持 Maccms JSON 接口）
本项目是一个基于 Apple 风格 UI 的随机多源视频播放前端页面，配合后台 API 实现对 Maccms 影视系统的 JSON 接口的调用和随机视频播放。

1. 环境要求
支持 PHP 的 Web 服务器（推荐 PHP 7.4+）

Apache 或 Nginx

支持访问外部网络，能够访问 Maccms JSON 接口

2. 部署前端页面
将项目中的 HTML 文件上传到服务器的 Web 根目录或子目录，通过浏览器访问即可。

3. 后台 API 说明
本项目需要配合后台 API 实现以下功能，且目前仅兼容 Maccms 的 JSON 接口格式：

接口	说明
/api/list_apis.php	返回可选的 Maccms JSON 接口列表及解析基础地址
/api/add_invalid_id.php	记录无效视频ID，避免重复请求无效数据
/api/get_invalid_ids.php	获取所有已记录的无效视频ID列表
/api/proxy.php	代理转发 Maccms JSON API 请求，解决跨域问题

示例 list_apis.php 返回格式
json
复制
编辑
{
  "parse_url": "https://yourparseurl.com/parse?url=",
  "apis": [
    {"name": "本地Maccms接口", "api_url": "http://your-maccms-site.com/api.php/provide/vod?ac=detail"},
    {"name": "远程Maccms接口", "api_url": "http://remote-maccms.com/api.php/provide/vod?ac=detail"}
  ]
}
4. 配置说明
将前端代码中的接口地址变量替换为你后台 API 的实际地址

例如：

js
复制
编辑
const ADD_INVALID_ID_API = '/api/add_invalid_id.php';
const GET_INVALID_IDS_API = '/api/get_invalid_ids.php';
const PROXY_API = '/api/proxy.php';
const API_LIST_URL = '/api/list_apis.php';
确保你的后台接口能正确读取和响应 Maccms JSON 接口数据格式

5. 使用说明
访问前端页面后，选择你想使用的 Maccms JSON 接口

点击“随机播放”按钮，即可随机加载并播放视频

视频播放结束后，会自动随机播放下一个视频，无需手动操作

6. 注意事项
当前仅支持 Maccms 系统提供的 JSON 视频详情接口，不支持其他格式或非 Maccms 的接口

播放器支持 HLS（m3u8）和普通视频格式，推荐使用支持 HLS.js 的现代浏览器

需确保后台 API 的跨域代理和无效ID记录功能正常，否则可能导致重复请求无效视频

7. 后续扩展
后续可考虑增加支持其他视频API接口或自定义解析规则，欢迎提交反馈和 PR。
