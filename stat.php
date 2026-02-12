<?php
header('Content-Type: text/html; charset=utf-8');

$direction = isset($_GET['napr']) ? trim((string)$_GET['napr']) : '';

if ($direction === '') {
    http_response_code(400);
    echo '<h2>Не задано напрям навчання.</h2>';
    echo '<p><a href="napr.php">Повернутися до вибору</a></p>';
    exit;
}

$dataPath = __DIR__ . DIRECTORY_SEPARATOR . 'data.txt';
if (!file_exists($dataPath)) {
    http_response_code(500);
    echo "<h2>Помилка: файл data.txt не знайдено.</h2>";
    echo '<p><a href="napr.php">Повернутися до вибору</a></p>';
    exit;
}

$rawLines = file($dataPath, FILE_IGNORE_NEW_LINES);

$lines = [];
foreach ($rawLines as $line) {
    $line = rtrim($line, "\r\n");
    $converted = @iconv('windows-1251', 'utf-8//IGNORE', $line);
    $lines[] = ($converted !== false && $converted !== '') ? $converted : $line;
}

$startIndex = -1;
for ($i = 0; $i < count($lines); $i++) {
    if (trim($lines[$i]) === $direction) {
        $startIndex = $i;
        break;
    }
}

if ($startIndex === -1) {
    http_response_code(404);
    $safeDir = htmlspecialchars($direction, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<h2>Напрям «{$safeDir}» не знайдено у файлі data.txt.</h2>";
    echo '<p><a href="napr.php">Повернутися до вибору</a></p>';
    exit;
}

$nIndex = $startIndex + 1;
if ($nIndex >= count($lines)) {
    http_response_code(500);
    echo '<h2>Помилка формату data.txt: відсутнє число ВНЗ.</h2>';
    echo '<p><a href="napr.php">Повернутися до вибору</a></p>';
    exit;
}

$n = (int)trim($lines[$nIndex]);
if ($n <= 0) {
    http_response_code(500);
    echo '<h2>Помилка формату data.txt: некоректне число ВНЗ.</h2>';
    echo '<p><a href="napr.php">Повернутися до вибору</a></p>';
    exit;
}

$rows = [];
$p = $nIndex + 1;
for ($k = 0; $k < $n; $k++) {
    if ($p + 3 >= count($lines)) break;

    $avg = trim($lines[$p]);
    $budget = trim($lines[$p + 1]);
    $contract = trim($lines[$p + 2]);
    $univ = trim($lines[$p + 3]);
    $p += 4;

    $rows[] = [
        'avg' => $avg,
        'budget' => $budget,
        'contract' => $contract,
        'univ' => $univ,
    ];
}

$safeDir = htmlspecialchars($direction, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ЛР4 — Статистика</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;line-height:1.4;margin:24px;}
    .wrap{max-width:1100px;margin:0 auto;}
    a{color:#0b57d0;text-decoration:none;}
    a:hover{text-decoration:underline;}
    h1{font-size:22px;margin:0 0 8px;}
    .sub{color:#333;margin:0 0 14px;}
    .toolbar{display:flex;gap:12px;align-items:center;margin:12px 0 18px;flex-wrap:wrap;}
    .badge{display:inline-block;padding:6px 10px;border:1px solid #ddd;border-radius:999px;background:#fafafa;font-size:12px;}
    table{border-collapse:collapse;width:100%;}
    th,td{border:1px solid #ddd;padding:8px 10px;vertical-align:top;}
    th{background:#f3f3f3;text-align:left;}
    td.num{white-space:nowrap;text-align:right;}
    .neg{font-weight:600;}
    .foot{margin-top:12px;color:#666;font-size:12px;}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="toolbar">
      <a href="napr.php">← Повернутися до вибору напряму</a>
      <span class="badge">ВНЗ у вибірці: <b><?php echo (int)count($rows); ?></b></span>
      <span class="badge">Напрям: <b><?php echo $safeDir; ?></b></span>
    </div>

    <h1>Статистика за напрямом</h1>
    <p class="sub"><?php echo $safeDir; ?></p>

    <table>
      <thead>
        <tr>
          <th style="width:56px;">№</th>
          <th>ВНЗ</th>
          <th style="width:170px;">Середній бал (бюджет)</th>
          <th style="width:150px;">К-ть вступників (бюджет)</th>
          <th style="width:170px;">К-ть вступників (контракт)</th>
        </tr>
      </thead>
      <tbody>
        <?php
        for ($i = 0; $i < count($rows); $i++) {
            $r = $rows[$i];
            $univ = htmlspecialchars($r['univ'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $avg = htmlspecialchars($r['avg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $budget = htmlspecialchars($r['budget'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $contractRaw = $r['contract'];

            $contractNum = (int)trim($contractRaw);
            $contractSafe = htmlspecialchars($contractRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $contractClass = ($contractNum < 0) ? 'neg' : '';
            echo "<tr>";
            echo "<td class='num'>" . ($i + 1) . "</td>";
            echo "<td>{$univ}</td>";
            echo "<td class='num'>{$avg}</td>";
            echo "<td class='num'>{$budget}</td>";
            echo "<td class='num {$contractClass}'>{$contractSafe}</td>";
            echo "</tr>";
        }
        ?>
      </tbody>
    </table>

    <div class="foot">
      Примітка: якщо значення в колонці «контракт» від’ємне — це означає недобір на бюджетні місця (за умовою лабораторної).
    </div>
  </div>
</body>
</html>
