<?php
$c = require __DIR__ . '/../src/content.php';
$e = static function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$p = static function ($html) { return str_replace('<p>', '<p class="dd-p">', $html); };
$tags = ['red-tag', 'blue-tag', 'yellow-tag', 'red-tag', 'blue-tag', 'yellow-tag', 'red-tag', 'blue-tag', 'yellow-tag'];
$stageClasses = ['stage-attacker', 'stage-crash', 'stage-engine', 'stage-engine', 'stage-rce'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($c['title']) ?></title>
    <meta name="description" content="<?= $e($c['subtitle']) ?>">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="site-header">
        <div class="logo">
            <span class="logo-icon">&#9672;</span>
            <span>sshole &middot; Explained</span>
        </div>
        <nav class="nav">
            <a href="#top" class="active">Overview</a>
            <a href="#affected">Affected</a>
            <a href="#fix">Fix</a>
            <a href="#glossary">Glossary</a>
        </nav>
    </header>

    <main class="container" id="top">
<section class="hero">
  <h1>*sshole</h1>
  <p class="subtitle"><?= $e($c['subtitle']) ?></p>
</section>

<div class="report-list">
  <div class="report-entry report-entry-red walkthrough" style="flex-direction: column;">

    <?php foreach ($c['sections'] as $i => $s): ?>
      <div id="<?= $e($s['id']) ?>"></div>

      <?php if ($s['type'] === 'callout'): ?>
        <div class="card-tag" style="background: var(--muted); color: #fff; margin-bottom: 16px;">OPENSSH &middot; MUSL &middot; NO PAM</div>
        <div class="dd-primer">
          <div class="primer-head"><?= $e($s['heading']) ?></div>
          <?= $p($s['html']) ?>
        </div>

      <?php else: ?>
        <div class="card-tag <?= $e($tags[$i % count($tags)]) ?>" style="margin: 28px 0 16px;">PART <?= (int) $i ?></div>
        <h2><?= $e($s['heading']) ?></h2>

        <?php if ($s['type'] === 'prose'): ?>
          <?= $p($s['html']) ?>

        <?php elseif ($s['type'] === 'chain'): ?>
          <div class="stage-track">
            <?php foreach ($s['items'] as $n => $step): ?>
              <div class="stage <?= $e($stageClasses[$n % count($stageClasses)]) ?>">
                <span class="stage-num"><?= $n + 1 ?></span>
                <p><?= $step ?></p>
              </div>
            <?php endforeach; ?>
          </div>

        <?php elseif ($s['type'] === 'list'): ?>
          <ul class="dd-list">
            <?php foreach ($s['items'] as $item): ?><li><?= $item ?></li><?php endforeach; ?>
          </ul>

        <?php elseif ($s['type'] === 'checklist'): ?>
          <p class="dd-p"><?= $s['intro'] ?></p>
          <ul class="dd-list">
            <?php foreach ($s['bad'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
          </ul>
          <p class="dd-p"><?= $s['outro'] ?></p>
          <h3 style="margin: 24px 0 12px;"><?= $e($s['good_heading']) ?></h3>
          <ul class="dd-list">
            <?php foreach ($s['good'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>

    <hr class="deepdive-divider">
    <h2 class="deepdive-title"><?= $e($c['timeline_heading']) ?></h2>
    <p class="dd-p"><?= $e($c['timeline_intro']) ?></p>
    <div class="dd-diagram">
      <table class="ledger">
        <caption>Timeline</caption>
        <thead><tr><th>When</th><th>What happened</th><th>Source</th></tr></thead>
        <tbody>
          <?php foreach ($c['timeline'] as $item): ?>
            <tr>
              <td class="mono"><?= $e($item['year']) ?></td>
              <td><strong><?= $e($item['heading']) ?>.</strong> <?= $item['body'] ?></td>
              <td><a href="<?= $e($item['link']) ?>" rel="noopener noreferrer"><?= $e($item['note']) ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div id="glossary"></div>
    <div class="dd-primer">
      <div class="primer-head">Glossary</div>
      <dl>
        <?php foreach ($c['glossary'] as $term => $def): ?>
          <dt><?= $e($term) ?></dt>
          <dd><?= $e($def) ?></dd>
        <?php endforeach; ?>
      </dl>
    </div>

  </div>
</div>
    </main>

    <footer class="site-footer">
        <p><?= $e($c['footer']) ?></p>
    </footer>
</body>
</html>
