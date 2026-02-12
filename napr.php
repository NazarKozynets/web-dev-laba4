<?php
header('Content-Type: text/html; charset=utf-8');

$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'napr.txt';

if (!file_exists($filePath)) {
    http_response_code(500);
    echo "<h2>Помилка: файл napr.txt не знайдено.</h2>";
    exit;
}

$rawLines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$napr = [];

foreach ($rawLines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    $converted = @iconv('windows-1251', 'utf-8//IGNORE', $line);
    $napr[] = ($converted !== false && $converted !== '') ? $converted : $line;
}

sort($napr, SORT_STRING);

?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ЛР4 — Вибір напряму</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;line-height:1.4;margin:24px;}
    .card{max-width:920px;margin:0 auto;border:1px solid #ddd;border-radius:12px;padding:18px 18px 10px;}
    h1{font-size:22px;margin:0 0 8px;}
    p{margin:0 0 14px;color:#333;}
    .list{max-height:420px;overflow:auto;border:1px solid #eee;border-radius:10px;padding:12px;}
    label{display:block;padding:6px 8px;border-radius:8px;cursor:pointer;}
    label:hover{background:#f7f7f7;}
    input[type=radio]{margin-right:10px;}
    .actions{display:flex;gap:10px;align-items:center;margin-top:14px;}
    button{padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer;}
    button:hover{opacity:.9;}
    .hint{font-size:12px;color:#666;}
  </style>
</head>
<body>
  <div class="card">
    <h1>Лабораторна робота №4 — Основи PHP</h1>
    <p>Оберіть напрям навчання зі списку та натисніть «Показати статистику».</p>

    <form method="get" action="stat.php">
      <div class="list">
        <?php
        for ($i = 0; $i < count($napr); $i++) {
            $value = htmlspecialchars($napr[$i], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $id = "napr_" . $i;
            $checked = ($i === 0) ? 'checked' : '';
            echo "<label for=\"$id\"><input $checked type=\"radio\" id=\"$id\" name=\"napr\" value=\"$value\" />$value</label>";
        }
        ?>
      </div>

      <div class="actions">
        <button type="submit">Показати статистику</button>
        <span class="hint">Дані беруться з файлу <b>data.txt</b>.</span>
      </div>
    </form>
  </div>
</body>
</html>
