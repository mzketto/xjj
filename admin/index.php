<?php
require '../config.php';

// 读取解析地址
$p = $conn->query("SELECT value FROM config WHERE key_name='player_parse_url'");
$row = $p->fetch_assoc();
$parseUrl = htmlspecialchars($row['value'] ?? '', ENT_QUOTES);

// 日志分页
$linesPerPage = 50;
$invalidPage = isset($_GET['invalid_page']) ? max(1, intval($_GET['invalid_page'])) : 1;
$invalidFilename = __DIR__ . '/../api/invalid_ids.txt';

$invalidLines = file_exists($invalidFilename)
  ? file($invalidFilename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
  : [];

$invalidTotal = count($invalidLines);
$invalidTotalPages = ceil($invalidTotal / $linesPerPage);
$invalidStart = ($invalidPage - 1) * $linesPerPage;
$invalidPageLines = array_slice($invalidLines, $invalidStart, $linesPerPage);
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
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
      background: #007aff;
      padding: 12px 20px;
      justify-content: center;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .nav button {
      background: none;
      border: none;
      color: white;
      font-size: 1.1rem;
      margin: 0 15px;
      padding: 6px 12px;
      cursor: pointer;
      font-weight: 600;
      border-bottom: 2px solid transparent;
      transition: all 0.3s ease;
    }

    .nav button.active {
      border-color: white;
    }

    .container {
      max-width: 800px;
      margin: 30px auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 14px 36px rgba(0, 0, 0, 0.12);
      padding: 30px 40px;
    }

    h2 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      border-bottom: 2px solid #007aff;
      padding-bottom: 8px;
    }

    form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px 15px;
      margin-bottom: 30px;
    }

    label {
      flex: 0 0 70px;
      font-weight: 600;
    }

    input[type="text"],
    input[type="url"] {
      flex: 1 1 auto;
      padding: 10px 14px;
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
      padding: 10px 25px;
      font-size: 1rem;
      border-radius: 18px;
      cursor: pointer;
    }

    .api-edit-form {
      border: 1px solid #eee;
      padding: 14px 18px;
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

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
    }

    th {
      background-color: #ff3b30;
      color: white;
    }

    .pagination {
      text-align: center;
      margin-top: 15px;
    }

    .pagination a {
      padding: 6px 12px;
      margin: 0 4px;
      border: 1px solid #ff3b30;
      border-radius: 8px;
      color: #ff3b30;
      text-decoration: none;
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
  </style>
</head>

<body>
  <!-- 菜单栏 -->
  <div class="nav">
    <button class="tab-btn active" data-tab="api">接口管理</button>
    <button class="tab-btn" data-tab="log">无效ID日志</button>
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
      echo "ID: <input name='id' value='{$row['id']}' readonly> ";
      echo "名称: <input name='name' value='{$row['name']}' required> ";
      echo "地址: <input name='api_url' value='{$row['api_url']}' required> ";
      echo "<button type='submit'>保存</button></form>";
      echo "<form class='delete-form' method='POST' action='../api/delete_api.php' onsubmit=\"return confirm('确定删除吗？');\">";
      echo "<input type='hidden' name='id' value='{$row['id']}'>";
      echo "<button type='submit'>删除</button></form><br>";
    }
    ?>

    <h2>设置解析地址</h2>
    <form method="POST" action="../api/update_parse.php">
      <input name="parse_url" type="url" value="<?php echo $parseUrl; ?>" required style="width:100%;">
      <button type="submit" style="margin-top: 10px;">保存</button>
    </form>
  </div>

  <!-- 无效ID日志 -->
  <div class="container section" id="section-log">
    <h2>无效ID日志</h2>
    <?php if (empty($invalidLines)): ?>
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
          <?php foreach ($invalidPageLines as $i => $line):
            $num = $invalidStart + $i + 1;
            $parts = explode(' ', $line, 4);
            $api = htmlspecialchars($parts[0] ?? '');
            $id = htmlspecialchars($parts[1] ?? '');
            $time = htmlspecialchars(($parts[2] ?? '') . ' ' . ($parts[3] ?? ''));
          ?>
            <tr><td><?= $num ?></td><td><?= $api ?></td><td><?= $id ?></td><td><?= $time ?></td></tr>
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

  <!-- 切换脚本 -->
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
  </script>
</body>
</html>
