<?php
session_start();

$stats_file = __DIR__ . '/stats.json';

if (!file_exists($stats_file)) {
  file_put_contents($stats_file, json_encode([
    "total_views" => 0,
    "active_sessions" => []
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($stats_file), true);
$now  = time();

// session id ঠিকভাবে নাও
$session_id = session_id();
if (!$session_id) {
  $session_id = 'anon_' . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

// ভিউ বাড়াবে শুধু pageview=true দিলে
if (isset($_GET['pageview']) && $_GET['pageview'] == '1') {
  $data['total_views'] = ($data['total_views'] ?? 0) + 1;
}

// Active user update
$data['active_sessions'][$session_id] = $now;

// ৩০ সেকেন্ডের বেশি পুরানো সেশন বাদ
foreach (($data['active_sessions'] ?? []) as $id => $last_seen) {
  if ($now - $last_seen > 30) unset($data['active_sessions'][$id]);
}

// save
file_put_contents($stats_file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// response
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  "active_users" => count($data['active_sessions'] ?? []),
  "total_views"  => $data['total_views'] ?? 0
], JSON_UNESCAPED_UNICODE);