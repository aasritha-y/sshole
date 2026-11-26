<?php
$c = require __DIR__ . '/../src/content.php';
$e = static function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($c['title']) ?></title>
<meta name="description" content="<?= $e($c['subtitle']) ?>">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="wrap">

  <header>
    <h1><span class="star">*</span>sshole explained</h1>
    <p class="sub"><?= $e($c['subtitle']) ?></p>
  </header>

  <nav class="toc" aria-label="Contents">
    <p class="toc-title">On this page</p>
    <ol>
      <?php foreach ($c['sections'] as $s): ?>
        <li><a href="#<?= $e($s['id']) ?>"><?= $e($s['heading']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="#timeline"><?= $e($c['timeline_heading']) ?></a></li>
      <li><a href="#glossary">Glossary</a></li>
    </ol>
  </nav>

  <?php foreach ($c['sections'] as $s): ?>
    <section id="<?= $e($s['id']) ?>" class="<?= $s['type'] === 'callout' ? 'callout' : '' ?>">
      <h2><?= $e($s['heading']) ?></h2>

      <?php if ($s['type'] === 'prose' || $s['type'] === 'callout'): ?>
        <?= $s['html'] ?>

      <?php elseif ($s['type'] === 'chain'): ?>
        <ol class="chain">
          <?php foreach ($s['items'] as $step): ?><li><?= $step ?></li><?php endforeach; ?>
        </ol>

      <?php elseif ($s['type'] === 'list'): ?>
        <ul class="steps">
          <?php foreach ($s['items'] as $item): ?><li><?= $item ?></li><?php endforeach; ?>
        </ul>

      <?php elseif ($s['type'] === 'checklist'): ?>
        <p><?= $s['intro'] ?></p>
        <ul class="check bad">
          <?php foreach ($s['bad'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
        </ul>
        <p><?= $s['outro'] ?></p>
        <h3><?= $e($s['good_heading']) ?></h3>
        <ul class="check good">
          <?php foreach ($s['good'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <section id="timeline">
    <h2><?= $e($c['timeline_heading']) ?></h2>
    <p><?= $e($c['timeline_intro']) ?></p>
    <?php foreach ($c['timeline'] as $item): ?>
      <div class="entry">
        <div class="year"><?= $e($item['year']) ?></div>
        <h3><?= $e($item['heading']) ?></h3>
        <p><?= $item['body'] ?></p>
        <div class="note">
          <?= $e($item['note']) ?>:
          <a href="<?= $e($item['link']) ?>" rel="noopener noreferrer">source</a>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <section id="glossary">
    <h2>Glossary</h2>
    <dl class="glossary">
      <?php foreach ($c['glossary'] as $term => $def): ?>
        <dt><?= $e($term) ?></dt>
        <dd><?= $e($def) ?></dd>
      <?php endforeach; ?>
    </dl>
  </section>

  <footer>
    <p><?= $e($c['footer']) ?></p>
  </footer>

</div>
</body>
</html>
