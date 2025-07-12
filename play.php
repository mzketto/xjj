<?php
require_once(__DIR__ . '/config.php');

$apiName = $_GET['api'] ?? '';
$vodId = intval($_GET['id'] ?? 0);

if (!$apiName || !$vodId) {
    exit("参数错误");
}

// 查询接口地址
$stmt = $conn->prepare("SELECT api_url FROM video_apis WHERE name = ?");
$stmt->bind_param("s", $apiName);
$stmt->execute();
$res = $stmt->get_result();
$apiRow = $res->fetch_assoc();
$apiUrl = $apiRow['api_url'] ?? '';

// 查询解析地址
$row = $conn->query("SELECT value FROM config WHERE key_name='player_parse_url'")->fetch_assoc();
$parseUrl = $row['value'] ?? '';

if (!$apiUrl || !$parseUrl) {
    exit("接口地址或解析地址未配置");
}

// 通过接口请求视频详情数据
$apiRequestUrl = $apiUrl . '&ids=' . $vodId;
$videoDataJson = file_get_contents($apiRequestUrl);
$videoData = json_decode($videoDataJson, true);

if (!$videoData || $videoData['code'] != 1 || empty($videoData['list'])) {
    exit("视频数据获取失败");
}

$video = $videoData['list'][0];
$title = $video['vod_name'] ?? '无标题';
$playUrlRaw = $video['vod_play_url'] ?? '';

/**
 * PHP解析播放地址字符串
 * 格式：label$url#label$url#...
 * 返回二维数组 [['label'=>..., 'url'=>...], ...]
 */
function parsePlayUrls($raw) {
    $result = [];
    if (!$raw) return $result;
    $episodes = explode('#', $raw);
    foreach ($episodes as $ep) {
        $parts = explode('$', $ep, 2);
        if (count($parts) == 2) {
            $result[] = ['label' => $parts[0], 'url' => $parts[1]];
        }
    }
    return $result;
}

$playUrls = parsePlayUrls($playUrlRaw);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    /* ... 省略样式，保持和前面一致，注意给集数按钮加样式 */
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f7; padding: 30px; display: flex; flex-direction: column; align-items: center; }
    h1 { font-size: 1.5rem; color: #222; margin-bottom: 15px; }
    video { width: 100%; max-width: 720px; border-radius: 14px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15); background: #000; }
    #episodeList { margin: 15px 0; display: flex; flex-wrap: wrap; gap: 10px; max-width: 720px; justify-content: center; }
    #episodeList button { padding: 8px 14px; border-radius: 14px; border: 1.5px solid #ddd; background: #f6f6f8; font-weight: 600; cursor: pointer; user-select: none; transition: all 0.3s ease; }
    #episodeList button.active { background: #007aff; border-color: #007aff; color: white; box-shadow: 0 3px 8px rgba(0, 122, 255, 0.4); }
    .msg { margin-top: 20px; color: #999; }
  </style>
</head>
<body>
  <h1 id="title"><?= htmlspecialchars($title) ?></h1>
  <video id="player" controls></video>

  <div id="episodeList">
    <?php foreach ($playUrls as $index => $item): ?>
      <button data-index="<?= $index ?>" data-url="<?= htmlspecialchars($item['url']) ?>">
        <?= htmlspecialchars($item['label']) ?: ('第' . ($index + 1) . '集') ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="msg" id="msg"></div>

  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <script>
    const parseUrl = <?= json_encode($parseUrl) ?>;
    const player = document.getElementById('player');
    const episodeButtons = document.querySelectorAll('#episodeList button');
    const msg = document.getElementById('msg');

    let currentHls = null;

    function playUrl(url) {
      if (currentHls) {
        currentHls.destroy();
        currentHls = null;
      }
      player.pause();
      player.removeAttribute('src');
      player.load();

      let finalUrl = url;
      if (!url.endsWith('.m3u8') && !url.startsWith('http')) {
        finalUrl = parseUrl + url;
      }

      if (finalUrl.endsWith('.m3u8')) {
        if (Hls.isSupported()) {
          currentHls = new Hls();
          currentHls.loadSource(finalUrl);
          currentHls.attachMedia(player);
          currentHls.on(Hls.Events.MANIFEST_PARSED, () => {
            player.play();
          });
        } else if (player.canPlayType('application/vnd.apple.mpegurl')) {
          player.src = finalUrl;
          player.play();
        } else {
          alert('您的浏览器不支持播放该视频格式');
        }
      } else {
        player.src = finalUrl;
        // 非m3u8格式不自动播放，等待用户点击播放
      }
    }

    function setActive(index) {
      episodeButtons.forEach((btn, idx) => {
        btn.classList.toggle('active', idx === index);
      });
    }

    episodeButtons.forEach((btn, index) => {
      btn.addEventListener('click', () => {
        setActive(index);
        playUrl(btn.dataset.url);
      });
    });

    // 默认播放第1集
    if (episodeButtons.length > 0) {
      episodeButtons[0].click();
      setActive(0);
      msg.textContent = "";
    } else {
      msg.textContent = "无有效播放地址";
    }
  </script>
</body>
</html>
