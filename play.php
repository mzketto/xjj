<?php
require_once(__DIR__ . '/config.php'); // 引入配置文件

// 获取查询参数
$apiName = $_GET['api'] ?? ''; // 获取API名称
$vodId = intval($_GET['id'] ?? 0); // 获取视频ID，并确保其为整数

if (!$apiName || !$vodId) {
    exit("参数错误"); // 如果API名称或视频ID缺失，退出并显示错误
}

// 查询视频 API 的 URL
$stmt = $conn->prepare("SELECT api_url FROM video_apis WHERE name = ?"); // 查询数据库中的API地址
$stmt->bind_param("s", $apiName); // 绑定参数
$stmt->execute(); // 执行查询
$res = $stmt->get_result(); // 获取查询结果
if (!$res || $res->num_rows == 0) {
    exit("未找到对应的 API URL"); // 如果没有找到对应的API地址，退出
}

$apiRow = $res->fetch_assoc(); // 获取查询结果的第一行
$apiUrl = $apiRow['api_url'] ?? ''; // 获取API的URL

if (!$apiUrl) {
    exit("API URL 未配置"); // 如果API地址为空，退出
}

// 获取视频集数信息
$apiRequestUrl = $apiUrl . '&ids=' . $vodId; // 拼接视频API请求URL
$videoDataJson = file_get_contents($apiRequestUrl); // 获取视频数据
$videoData = json_decode($videoDataJson, true); // 解析JSON数据

if (!$videoData || $videoData['code'] != 1 || empty($videoData['list'])) {
    exit("视频数据获取失败"); // 如果视频数据为空或错误，退出
}

$video = $videoData['list'][0]; // 获取第一个视频数据
$title = $video['vod_name'] ?? '无标题'; // 获取视频名称
$playUrlRaw = $video['vod_play_url'] ?? ''; // 获取播放地址原始字符串

// PHP解析播放地址字符串
function parsePlayUrls($raw) {
    $result = [];
    if (!$raw) return $result; // 如果没有播放地址，返回空数组
    $episodes = explode('#', $raw); // 按#分隔集数
    foreach ($episodes as $ep) {
        $parts = explode('$', $ep, 2); // 按$分隔集数信息和播放地址
        if (count($parts) == 2) {
            $result[] = ['label' => $parts[0], 'url' => $parts[1]]; // 返回集数和对应的播放地址
        }
    }
    return $result;
}

$playUrls = parsePlayUrls($playUrlRaw); // 解析播放地址
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($title) ?></title> <!-- 动态标题显示 -->
  <link href="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.css" rel="stylesheet"> <!-- 引入DPlayer样式 -->
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      background: #f5f5f7;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h1 {
      font-size: 1.5rem;
      color: #222;
      margin-bottom: 15px;
    }
    #episodeList {
      margin: 15px 0;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      max-width: 720px;
      justify-content: center;
    }
    #episodeList button {
      padding: 8px 14px;
      border-radius: 14px;
      border: 1.5px solid #ddd;
      background: #f6f6f8;
      font-weight: 600;
      cursor: pointer;
      user-select: none;
      transition: all 0.3s ease;
    }
    #episodeList button.active {
      background: #007aff;
      border-color: #007aff;
      color: white;
      box-shadow: 0 3px 8px rgba(0, 122, 255, 0.4);
    }
    .msg {
      margin-top: 20px;
      color: #999;
    }
  </style>
</head>
<body>
  <h1 id="title"><?= htmlspecialchars($title) ?></h1>

  <!-- DPlayer 播放器容器 -->
  <div id="dp_container" style="width: 100%; max-width: 720px; margin: 0 auto;"></div>

  <div id="episodeList">
    <?php foreach ($playUrls as $index => $item): ?>
      <button data-index="<?= $index ?>" data-url="<?= htmlspecialchars($item['url']) ?>">
        <?= htmlspecialchars($item['label']) ?: ('第' . ($index + 1) . '集') ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="msg" id="msg"></div>

  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script> <!-- 引入HLS.js库 -->
  <script src="https://cdn.jsdelivr.net/npm/dplayer"></script> <!-- 引入DPlayer播放器 -->

  <script>
    const parseUrl = <?= json_encode($parseUrl) ?>; // 解析基础URL
    const episodeButtons = document.querySelectorAll('#episodeList button'); // 获取集数按钮
    const msg = document.getElementById('msg'); // 获取消息容器
    let dp = null; // DPlayer实例

    // 播放视频地址
    function playUrl(url) {
      let finalUrl = url;
      if (!url.endsWith('.m3u8') && !url.startsWith('http')) {
        finalUrl = parseUrl + url; // 如果播放地址不是m3u8或http开头，拼接基础URL
      }

      if (dp) {
        dp.destroy(); // 销毁之前的播放器实例
      }

      dp = new DPlayer({
        container: document.getElementById('dp_container'), // 播放器容器
        video: {
          url: finalUrl, // 设置播放地址
          type: finalUrl.endsWith('.m3u8') ? 'hls' : 'normal' // 根据视频格式设置类型
        }
      });

      dp.play(); // 播放视频
    }

    // 设置按钮的激活状态
    function setActive(index) {
      episodeButtons.forEach((btn, idx) => {
        btn.classList.toggle('active', idx === index); // 切换按钮的active状态
      });
    }

    episodeButtons.forEach((btn, index) => {
      btn.addEventListener('click', () => {
        setActive(index); // 点击按钮时设置为激活状态
        playUrl(btn.dataset.url); // 播放视频
      });
    });

    // 默认播放第1集
    if (episodeButtons.length > 0) {
      episodeButtons[0].click(); // 点击第一个按钮开始播放
      setActive(0); // 设置第一个按钮为激活状态
      msg.textContent = ""; // 清空消息
    } else {
      msg.textContent = "无有效播放地址"; // 如果没有有效的播放地址，显示提示
    }
  </script>
</body>
</html>
