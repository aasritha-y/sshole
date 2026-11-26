<?php
$c = require __DIR__ . '/../src/content.php';
$e = static function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$p = static function ($html) { return str_replace('<p>', '<p class="dd-p">', $html); };
$edge = ['report-entry-blue', 'report-entry-yellow', 'report-entry-red'];
$tags = ['blue-tag', 'yellow-tag', 'red-tag'];
$stageClasses = ['stage-attacker', 'stage-crash', 'stage-engine', 'stage-engine', 'stage-rce'];
$card = static function ($i) use ($edge) { return 'report-entry walkthrough ' . $edge[$i % count($edge)]; };
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

<?php $part = 0; foreach ($c['sections'] as $i => $s): ?>
  <?php if ($s['type'] === 'callout'): ?>
  <div id="<?= $e($s['id']) ?>" class="report-entry walkthrough report-entry-red" style="flex-direction: column;">
    <div class="card-tag" style="background: var(--muted); color: #fff; margin-bottom: 16px;">OPENSSH &middot; MUSL &middot; NO PAM</div>
    <div class="dd-section">
      <h2><?= $e($s['heading']) ?></h2>
      <?= $p($s['html']) ?>
    </div>
  </div>

  <?php else: $part++; ?>
  <div id="<?= $e($s['id']) ?>" class="<?= $e($card($part)) ?>" style="flex-direction: column;">
    <div class="card-tag <?= $e($tags[$part % count($tags)]) ?>">PART <?= $part ?></div>
    <div class="dd-section">
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
        <h3><?= $e($s['good_heading']) ?></h3>
        <ul class="dd-list">
          <?php foreach ($s['good'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
<?php endforeach; ?>

  <div id="timeline" class="report-entry walkthrough report-entry-blue" style="flex-direction: column;">
    <div class="card-tag blue-tag">TIMELINE</div>
    <div class="dd-section">
      <h2><?= $e($c['timeline_heading']) ?></h2>
      <p class="dd-p"><?= $e($c['timeline_intro']) ?></p>
      <div class="dd-diagram">
        <table class="ledger">
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
    </div>
  </div>

  <div id="glossary" class="report-entry walkthrough report-entry-yellow" style="flex-direction: column;">
    <div class="card-tag yellow-tag">GLOSSARY</div>
    <div class="dd-primer" style="margin-top: 8px;">
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
