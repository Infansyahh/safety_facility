<?php
/**
 * api/auth.php - Endpoint Autentikasi (login, logout, reset password)
 */
require_once __DIR__ . '/config.php';

$action = api_action('');

if ($action === 'check') {
    require_login();
    api_response([
        'success' => true,
        'logged_in' => true,
        'id_user' => $_SESSION['id_user'],
        'username' => $_SESSION['username'],
        'nama_lengkap' => $_SESSION['nama_lengkap'],
    ]);
}

if ($action === 'login') {
    $data = api_input();
    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if ($username === '' || $password === '') {
        api_response(['success' => false, 'message' => 'Username dan password wajib diisi.'], 400);
    }

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (md5($password) === $user['password'] || $password === $user['password']) {
            $_SESSION['logged_in']    = true;
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            api_response([
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role' => $user['role'],
                ],
            ]);
        } else {
            api_response(['success' => false, 'message' => 'Password yang Anda masukkan salah!'], 401);
        }
    } else {
        api_response(['success' => false, 'message' => 'Username tidak terdaftar di sistem!'], 401);
    }
}

if ($action === 'darurat_login') {
    $data = api_input();
    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if ($username === '' || $password === '') {
        api_response(['success' => false, 'message' => 'Username dan password wajib diisi.'], 400);
    }

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (md5($password) === $user['password'] || $password === $user['password']) {
            if (($user['role'] ?? '') !== 'admin') {
                api_response(['success' => false, 'message' => 'Akun tidak memiliki akses login darurat.'], 403);
            }
            $_SESSION['logged_in']    = true;
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            api_response([
                'success' => true,
                'message' => 'Login darurat berhasil.',
                'user' => [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role' => $user['role'],
                ],
            ]);
        } else {
            api_response(['success' => false, 'message' => 'Password yang Anda masukkan salah!'], 401);
        }
    } else {
        api_response(['success' => false, 'message' => 'Username tidak terdaftar di sistem!'], 401);
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    api_response(['success' => true, 'message' => 'Logout berhasil.']);
}

if ($action === 'reset_password') {
    $data = api_input();
    $username          = isset($data['username']) ? trim($data['username']) : '';
    $password_baru     = isset($data['password_baru']) ? $data['password_baru'] : '';
    $konfirmasi        = isset($data['konfirmasi_password']) ? $data['konfirmasi_password'] : '';

    if ($username === '' || $password_baru === '') {
        api_response(['success' => false, 'message' => 'Semua field wajib diisi.'], 400);
    }
    if ($password_baru !== $konfirmasi) {
        api_response(['success' => false, 'message' => 'Konfirmasi password tidak cocok!'], 400);
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {
        api_response(['success' => false, 'message' => 'Username tidak ditemukan!'], 404);
    }

    $hashed = md5($password_baru);
    $upd = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE username = ?");
    mysqli_stmt_bind_param($upd, 'ss', $hashed, $username);
    mysqli_stmt_execute($upd);

    api_response(['success' => true, 'message' => 'Password berhasil direset!']);
}

api_response(['success' => false, 'message' => 'Action tidak dikenal.'], 404);