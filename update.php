<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_login();


function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit("Method Not Allowed");
}

$reservationId = $_POST["reservation_id"] ?? "";
$title = trim($_POST["title"] ?? "");
$userName = trim($_POST["user_name"] ?? "");
$back = $_POST["back"] ?? "index.php";

if (!preg_match("/^\d+$/", (string)$reservationId)) {
  http_response_code(400);
  exit("reservation_id が不正です");
}

// ★ここから追加（所有者チェック）
$check = $pdo->prepare("SELECT user_id FROM reservation WHERE reservation_id = ? LIMIT 1");
$check->execute([(int)$reservationId]);
$row = $check->fetch(PDO::FETCH_ASSOC);
require_owner_or_403($row["user_id"] ?? null);
// ★ここまで追加

if ($title === "" || $userName === "") {
  header("Location: " . $back);
  exit;
}


$stmt = $pdo->prepare("
  UPDATE reservation
  SET title = ?, user_name = ?
  WHERE reservation_id = ?
  LIMIT 1
");
$stmt->execute([$title, $userName, (int)$reservationId]);

header("Location: " . $back);
exit;
