<?php
// Template only. Content is in ../src/content.php.
$c = require __DIR__ . '/../src/content.php';
$e = static function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($c['title']) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="wrap">

  <header>
    <h1><span class="star">*</span>sshole</h1>
    <p class="sub"><?= $e($c['subtitle']) ?></p>
  </header>

  <p><?= $c['intro'] ?></p>

  <h2>the sequence</h2>
  <?php foreach ($c['timeline'] as $item): ?>
    <div class="entry">
      <div class="year"><?= $e($item['year']) ?></div>
      <h3><?= $item['heading'] ?></h3>
      <p><?= $item['body'] ?></p>
      <?php if ($item['note']): ?>
        <div class="note">
          <?= $e($item['note']) ?>
          <?php if ($item['link']): ?>
            : <a href="<?= $e($item['link']) ?>" rel="noopener noreferrer">source</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <h2>the chain, step by step</h2>
  <ol class="chain">
    <?php foreach ($c['chain'] as $step): ?>
      <li><?= $step ?></li>
    <?php endforeach; ?>
  </ol>

  <h2>are you affected?</h2>
  <ul class="check bad">
    <?php foreach ($c['affected'] as $item): ?>
      <li><?= $e($item) ?></li>
    <?php endforeach; ?>
  </ul>
  <p><?= $c['affected_note'] ?></p>

  <h2>not affected</h2>
  <ul class="check good">
    <?php foreach ($c['not_affected'] as $item): ?>
      <li><?= $e($item) ?></li>
    <?php endforeach; ?>
  </ul>

  <h2>what now</h2>
  <ul>
    <?php foreach ($c['remediation'] as $item): ?>
      <li><?= $item ?></li>
    <?php endforeach; ?>
  </ul>

  <footer>
    <p><?= $e($c['footer']) ?></p>
    <span class="bye"><?= $e($c['subtitle']) ?></span>
  </footer>

</div>
</body>
</html>
