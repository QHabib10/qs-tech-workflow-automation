<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../services/JiraService.php';

$mode = isset($_GET['range']) && $_GET['range'] === 'next7' ? 'next7' : 'week';
$today = new DateTime('now');

if ($mode === 'week') {
    $dayOfWeek = (int)$today->format('N'); 
    $monday = (clone $today)->modify('-' . ($dayOfWeek - 1) . ' days');
    $sunday = (clone $monday)->modify('+6 days');
    $startDate = $monday->format('Y-m-d');
    $endDate = $sunday->format('Y-m-d');
} else {
    $startDate = $today->format('Y-m-d');
    $endDate = (clone $today)->modify('+6 days')->format('Y-m-d'); 
}

$jiraService = new JiraService($conn);
$issues = $jiraService->searchIssuesByDueDateRange($startDate, $endDate) ?: [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Calendar - QS Tech</title>
    <link rel="stylesheet" href="/assets/sidebar.css" />
    <link rel="stylesheet" href="/assets/lead_form.css" />
    <link rel="stylesheet" href="/assets/calendar.css" />
</head>
<body>
  <div class="layout">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="content">
      <div class="calendar-wrap">
        <div class="calendar-header">
          <h2 class="page-title" style="margin:0;">
            Jira Issues — <?= $mode === 'week' ? 'Due This Week' : 'Due Within Next 7 Days' ?>
          </h2>
        </div>
        <?php 
          $base = '/src/pages/calendar.php';
          $linkWeek = $base . '?range=week';
          $linkNext = $base . '?range=next7';
        ?>
        <div class="calendar-controls">
          <div class="calendar-filters">
            <a class="btn-filter <?= $mode==='week' ? 'is-active' : '' ?>" href="<?= $linkWeek ?>">This Week (Mon–Sun)</a>
            <a class="btn-filter <?= $mode==='next7' ? 'is-active' : '' ?>" href="<?= $linkNext ?>">Next 7 Days</a>
          </div>
          <div class="calendar-range">
            <?= htmlspecialchars($startDate) ?> – <?= htmlspecialchars($endDate) ?>
          </div>
        </div>
        <div class="card-row">
          <?php if (empty($issues)): ?>
            <div class="alert alert-info empty">No due items this week.</div>
          <?php else: ?>
            <?php foreach ($issues as $issue): 
              $issueKey = $issue['key'];
              $summary = $issue['summary'] ?: '(No summary)';
              $duedate = $issue['duedate'] ?: '-';
              $link = rtrim(getenv('JIRA_URL'), '/') . "/browse/" . urlencode($issueKey);
            ?>
              <div class="event-card">
                <div class="event-top">
                  <span class="summary" title="<?= htmlspecialchars($summary) ?>"><?= htmlspecialchars($summary) ?></span>
                  <a class="open-link" href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener">Open</a>
                </div>
                <div class="event-bottom">
                  <span class="issue-key"><?= htmlspecialchars($issueKey) ?></span>
                  <span class="duedate">Due: <?= htmlspecialchars($duedate) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>


