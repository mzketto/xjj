<?php
require '../config.php';

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 读取解析地址
$p = $conn->query("SELECT value FROM config WHERE key_name='player_parse_url'");
$row = $p ? $p->fetch_assoc() : null;
$parseUrl = htmlspecialchars($row['value'] ?? '', ENT_QUOTES);

// ========== 无效ID日志分页，从数据库读取 ==========
$linesPerPage = 50;
$invalidPage = isset($_GET['invalid_page']) ? max(1, intval($_GET['invalid_page'])) : 1;
$invalidOffset = ($invalidPage - 1) * $linesPerPage;

$totalInvalidRes = $conn->query("SELECT COUNT(*) AS total FROM invalid_logs");
$invalidTotal = $totalInvalidRes ? (int)($totalInvalidRes->fetch_assoc()['total'] ?? 0) : 0;
$invalidTotalPages = max(1, ceil($invalidTotal / $linesPerPage));

$invalidPageRows = [];
$invalidRes = $conn->query("SELECT * FROM invalid_logs ORDER BY id DESC LIMIT {$invalidOffset}, {$linesPerPage}");
if ($invalidRes) {
    while ($row = $invalidRes->fetch_assoc()) {
        $invalidPageRows[] = $row;
    }
}

// 统计无效ID数量（API名称 => 数量）
$apiCount = [];
$allInvalidRes = $conn->query("SELECT api_name FROM invalid_logs");
if ($allInvalidRes) {
    while ($row = $allInvalidRes->fetch_assoc()) {
        $apiName = $row['api_name'] ?? '未知API';
        $apiCount[$apiName] = ($apiCount[$apiName] ?? 0) + 1;
    }
}
$apiNames = json_encode(array_keys($apiCount), JSON_UNESCAPED_UNICODE);
$apiValues = json_encode(array_values($apiCount));

// ========== 访问日志分页，从数据库读取 ==========
$visitLinesPerPage = 50;
$visitPage = isset($_GET['visit_page']) ? max(1, intval($_GET['visit_page'])) : 1;
$visitOffset = ($visitPage - 1) * $visitLinesPerPage;

$totalVisitRes = $conn->query("SELECT COUNT(*) AS total FROM visit_logs");
$visitTotal = $totalVisitRes ? (int)($totalVisitRes->fetch_assoc()['total'] ?? 0) : 0;
$visitTotalPages = max(1, ceil($visitTotal / $visitLinesPerPage));

$visitPageRows = [];
$visitRes = $conn->query("SELECT * FROM visit_logs ORDER BY id DESC LIMIT {$visitOffset}, {$visitLinesPerPage}");
if ($visitRes) {
    while ($row = $visitRes->fetch_assoc()) {
        $visitPageRows[] = $row;
    }
}

// 历史访问IP数（去重）
$ipRes = $conn->query("SELECT COUNT(DISTINCT ip) AS total FROM visit_logs");
$uniqueIpCount = $ipRes ? (int)($ipRes->fetch_assoc()['total'] ?? 0) : 0;

// 今日访问IP数（去重）
$todayDate = date('Y-m-d');
$todayRes = $conn->query("SELECT COUNT(DISTINCT ip) AS total FROM visit_logs WHERE date = '{$todayDate}'");
$uniqueTodayIpCount = $todayRes ? (int)($todayRes->fetch_assoc()['total'] ?? 0) : 0;

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>API后台管理</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
      margin: 0;
      background-color: #f5f6fa;
      color: #1c1c1e;
    }
    .nav {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      background: #007aff;
      padding: 12px 8px;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .nav button {
      background: none;
      border: none;
      color: white;
      font-size: 1rem;
      margin: 6px 8px;
      padding: 8px 14px;
      cursor: pointer;
      font-weight: 600;
      border-bottom: 2px solid transparent;
      transition: all 0.3s ease;
      flex-shrink: 0;
    }
    .nav button.active {
      border-color: white;
    }
    .container {
      max-width: 900px;
      margin: 20px auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 14px 36px rgba(0, 0, 0, 0.08);
      padding: 24px;
    }
    h2 {
      font-size: 1.3rem;
      margin-bottom: 20px;
      border-bottom: 2px solid #007aff;
      padding-bottom: 8px;
    }
    form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 30px;
    }
    label {
      flex: 1 0 80px;
      font-weight: 600;
    }
    input[type="text"],
    input[type="url"] {
      flex: 1 1 200px;
      padding: 10px;
      font-size: 1rem;
      border: 1.8px solid #ddd;
      border-radius: 12px;
      background-color: #f9f9fb;
    }
    input:focus {
      border-color: #007aff;
      background-color: #fff;
      outline: none;
    }
    button {
      background-color: #007aff;
      border: none;
      color: #fff;
      font-weight: 700;
      padding: 10px 20px;
      font-size: 1rem;
      border-radius: 18px;
      cursor: pointer;
    }
    .api-edit-form {
      border: 1px solid #eee;
      padding: 14px;
      border-radius: 14px;
      margin-bottom: 12px;
      background-color: #fafafa;
    }
    .api-edit-form input[readonly] {
      background-color: #e5e5ea;
      font-weight: bold;
    }
    .delete-form {
      display: inline-block;
      margin-left: 10px;
    }
    .delete-form button {
      background-color: #ff3b30;
    }
    .table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th,
    td {
      padding: 10px;
      border: 1px solid #ddd;
      word-break: break-word;
      white-space: nowrap;
      min-width: 100px;
      font-size: 0.95rem;
    }
    th {
      background-color: #ff3b30;
      color: white;
    }
    th:nth-child(1),
    td:nth-child(1) {
      min-width: 40px;
    }
    th:nth-child(4),
    td:nth-child(4) {
      min-width: 140px;
    }
    th:nth-child(5),
    td:nth-child(5) {
      min-width: 160px;
    }
    th:nth-child(7),
    td:nth-child(7) {
      min-width: 120px;
    }
    th:nth-child(8),
    td:nth-child(8) {
      min-width: 180px;
    }
    .pagination {
      text-align: center;
      margin-top: 15px;
      flex-wrap: wrap;
    }
    .pagination a,
    .pagination .current {
      display: inline-block;
      padding: 6px 12px;
      margin: 4px;
      border: 1px solid #ff3b30;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .pagination .current {
      background-color: #ff3b30;
      color: white;
    }
    .section {
      display: none;
    }
    .section.active {
      display: block;
    }
    #barChart,
    #pieChart {
      max-width: 100%;
      height: auto;
    }
    @media (max-width: 600px) {
      .nav button {
        font-size: 0.9rem;
        padding: 6px 10px;
      }
      input[type="text"],
      input[type="url"] {
        width: 100%;
      }
      label {
        flex: 0 0 100%;
      }
      .api-edit-form,
      .container {
        padding: 20px 15px;
      }
      th,
      td {
        white-space: normal;
        font-size: 0.85rem;
        padding: 6px 8px;
        min-width: auto;
      }
    }
  </style>
</head>

<body>
  <!-- 菜单栏 -->
  <div class="nav">
    <button class="tab-btn active" data-tab="api">接口管理</button>
    <button class="tab-btn" data-tab="log">无效ID日志</button>
    <button class="tab-btn" data-tab="stats">统计视图</button>
    <button class="tab-btn" data-tab="visitlog">访问日志</button>
    <button id="logoutBtn" style="margin-left:auto; background:#ff3b30;">登出</button>
  </div>

  <!-- 接口管理 -->
  <div class="container section active" id="section-api">
    <h2>添加新接口</h2>
    <form method="POST" action="../api/add_api.php">
      <label>名称</label><input name="name" type="text" required>
      <label>接口地址</label><input name="api_url" type="url" required>
      <button type="submit">添加</button>
    </form>

    <h2>已有接口</h2>
    <?php
    $res = $conn->query("SELECT * FROM video_apis ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
      echo "<form class='api-edit-form' method='POST' action='../api/edit_api.php'>";
      echo "ID: <input name='id' value='" . htmlspecialchars($row['id']) . "' readonly> ";
      echo "名称: <input name='name' value='" . htmlspecialchars($row['name']) . "' required> ";
      echo "地址: <input name='api_url' value='" . htmlspecialchars($row['api_url']) . "' required> ";
      echo "<button type='submit'>保存</button></form>";
      echo "<form class='delete-form' method='POST' action='../api/delete_api.php' onsubmit=\"return confirm('确定删除吗？');\">";
      echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>";
      echo "<button type='submit'>删除</button></form><br>";
    }
    ?>

    
    </form>
  </div>

  <!-- 无效ID日志 -->
  <div class="container section" id="section-log">
    <h2>无效ID日志</h2>
    <?php if (empty($invalidPageRows)): ?>
      <p>暂无日志记录。</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>API名称</th>
            <th>ID</th>
            <th>时间</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invalidPageRows as $i => $row): 
            $num = $invalidOffset + $i + 1;
            $api = htmlspecialchars($row['api_name']);
            $id = htmlspecialchars($row['invalid_id']);
            $time = htmlspecialchars($row['created_at']);
          ?>
          <tr>
            <td><?= $num ?></td>
            <td><?= $api ?></td>
            <td><?= $id ?></td>
            <td><?= $time ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($invalidTotalPages > 1): ?>
        <div class="pagination">
          <?php for ($p = 1; $p <= $invalidTotalPages; $p++): ?>
            <?php if ($p == $invalidPage): ?>
              <span class="current"><?= $p ?></span>
            <?php else: ?>
              <a href="?invalid_page=<?= $p ?>"><?= $p ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- 统计视图 -->
  <div class="container section" id="section-stats">
    <h2>API 无效ID占比统计</h2>
    <canvas id="barChart"></canvas>
    <canvas id="pieChart" style="margin-top: 40px;"></canvas>
  </div>

  <!-- 访问日志 -->
  <div class="container section" id="section-visitlog">
    <h2>访问日志记录</h2>

    <p><strong>历史访问IP数量：</strong><?= $uniqueIpCount ?> &nbsp;&nbsp; <strong>今日访问IP数量：</strong><?= $uniqueTodayIpCount ?></p>

    <?php if (empty($visitPageRows)): ?>
      <p>暂无访问日志记录。</p>
    <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>日期</th>
              <th>时间</th>
              <th>IP</th>
              <th>物理地址</th>
              <th>设备</th>
              <th>API名称</th>
              <th>标题</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($visitPageRows as $i => $row): 
              $num = $visitOffset + $i + 1;
              ?>
              <tr>
                <td><?= $num ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['time']) ?></td>
                <td><?= htmlspecialchars($row['ip']) ?></td>
                <td><?= htmlspecialchars($row['geo']) ?>（<?= htmlspecialchars($row['isp']) ?>）</td>
                <td><?= htmlspecialchars($row['device']) ?></td>
                <td><?= htmlspecialchars($row['api_name']) ?></td>
                <td><?= htmlspecialchars($row['vod_name']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($visitTotalPages > 1): ?>
        <div class="pagination">
          <?php for ($p = 1; $p <= $visitTotalPages; $p++): ?>
            <?php if ($p == $visitPage): ?>
              <span class="current"><?= $p ?></span>
            <?php else: ?>
              <a href="?visit_page=<?= $p ?>#section-visitlog"><?= $p ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>

  <!-- 脚本 -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const buttons = document.querySelectorAll('.tab-btn');
    const sections = document.querySelectorAll('.section');

    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById('section-' + btn.dataset.tab).classList.add('active');
      });
    });

    function getQueryParam(name) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }

    window.addEventListener('DOMContentLoaded', () => {
      const invalidPage = getQueryParam('invalid_page');
      const visitLog = getQueryParam('visit_page');
      if (invalidPage) {
        document.querySelector('[data-tab="log"]').click();
      } else if (visitLog) {
        document.querySelector('[data-tab="visitlog"]').click();
      }
    });

    const apiNames = <?php echo $apiNames; ?>;
    const apiValues = <?php echo $apiValues; ?>;

    const ctxBar = document.getElementById('barChart').getContext('2d');
    const ctxPie = document.getElementById('pieChart').getContext('2d');

    new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: apiNames,
        datasets: [{
          label: '无效ID数量',
          data: apiValues,
          backgroundColor: 'rgba(0, 122, 255, 0.7)'
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        }
      }
    });

    const pieColors = apiNames.map((_, i) => `hsl(${i * 360 / apiNames.length}, 70%, 60%)`);
    new Chart(ctxPie, {
      type: 'pie',
      data: {
        labels: apiNames,
        datasets: [{
          label: '无效ID占比',
          data: apiValues,
          backgroundColor: pieColors
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
      }
    });

    document.getElementById('logoutBtn').addEventListener('click', () => {
      if (confirm('确定登出吗？')) {
        window.location.href = 'logout.php';
      }
    });
  </script>
</body>

</html>
