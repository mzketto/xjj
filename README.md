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




后台   域名/admin


添加API 接口  只添加  域名  https://xxxx.com/


错误添加法  https://xxxx.com/api.php/provide/vod/at/json/?ac=detail



### 部署步骤

1. 上传以后  域名/install.php  安装后删除install.php

2. 后台   域名/admin

3. 添加API 接口  只添加  域名  https://xxxx.com/
错误添加法  https://xxxx.com/api.php/provide/vod/at/json/?ac=detail

4.解析只要能解析播放m3u8  都可以


 








 
