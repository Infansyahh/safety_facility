<?php
/**
 * config.php - Konfigurasi REST API
 * Header CORS + JSON, koneksi database, dan helper response.
 */

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$pass     = getenv('DB_PASS') ?: '';
$db_name  = getenv('DB_NAME') ?: 'safety_facility';

$koneksi = @mysqli_connect($host, $user, $pass, $db_name);
if (!$koneksi) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi ke database gagal: ' . mysqli_connect_error(),
    ]);
    exit;
}
mysqli_set_charset($koneksi, 'utf8mb4');

function api_response($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function api_input()
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (is_array($data)) {
        return $data;
    }
    return $_POST;
}

function api_get($key, $default = '')
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function api_action($default = '')
{
    if (isset($_GET['action'])) {
        return $_GET['action'];
    }
    $data = api_input();
    return isset($data['action']) ? $data['action'] : $default;
}

function is_logged_in()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function require_login()
{
    if (!is_logged_in()) {
        api_response([
            'success' => false,
            'message' => 'Akses ditolak. Silakan login terlebih dahulu.',
        ], 401);
    }
}

function api_operator_name()
{
    return $_SESSION['nama_operator_popup'] ?? $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin';
}

function api_user_id()
{
    return isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : null;
}