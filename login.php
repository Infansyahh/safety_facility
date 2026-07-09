<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$host     = "localhost";
$user     = "root";
$pass     = "";
$db  = "safety_facility"; 
$db = mysqli_connect($host, $user, $pass, $db);

if (!$db) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin/index.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (md5($password) === $user['password'] || $password === $user['password']) {

            $_SESSION['logged_in']    = true;
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

            header("Location: admin/index.php");
            exit();
        } else {
            $error_message = "Password yang Anda masukkan salah!";
        }
    } else {
        $error_message = "Username tidak terdaftar di sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Emergency Lamp Inspection</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="foto/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('https://images.unsplash.com/photo-1516549655169-df83a0774514?q=80&w=1920') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            width: 400px;
            padding: 40px 30px;
            border-radius: 30px;
            box-shadow: 0px 15px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            height: 93vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo-container {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-img {
            max-width: 260px;
            height: auto;
        }

        h2 {
            font-size: 26px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        .input-group {
            width: 100%;
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 25px;
            border: 1px solid #ccc;
            border-radius: 50px;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
            text-align: center;
        }

        .input-group input:focus {
            border-color: #615bb0;
            box-shadow: 0 0 8px rgba(97, 91, 176, 0.3);
        }

        .btn-signin {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            background: #615bb0;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .btn-signin:hover {
            background: #4f4999;
            box-shadow: 0px 5px 12px rgba(97, 91, 176, 0.4);
        }

        .extra-links {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .extra-links a {
            color: #0056b3;
            text-decoration: none;
            display: inline-block;
            margin: 4px 0;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        .emergency-login {
            color: #2b75cc !important;
            font-weight: 600;
        }

        .footer-text {
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }

        /* Animations & Responsiveness */
        .login-box {
            animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 450px) {
            .login-box {
                width: 90% !important;
                padding: 30px 20px !important;
                height: auto !important;
                min-height: 80vh !important;
                border-radius: 20px !important;
            }
            h2 {
                font-size: 22px !important;
            }
            .logo-img {
                max-width: 200px !important;
            }
        }
    </style>
</head>

<body>

    <div class="login-box">
        <div class="logo-container">
            <img src="foto/logo.png" alt="Safety Facility Logo" class="logo-img">
        </div>

        <h2>SIGN IN - BOGOR</h2>

        <?php if (!empty($error_message)): ?>
            <div style="color: #dc3545; background: #f8d7da; padding: 10px 20px; border-radius: 25px; font-size: 13px; margin-bottom: 20px; width: 100%; font-weight: 600;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" style="width: 100%;">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
            </div>

            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-signin">Sign In</button>
        </form>

        <div class="extra-links">
            <a href="login_darurat.php" class="emergency-login"><i class="fa-solid fa-key"></i> Login Darurat (Admin)</a>
            <br>
            <a href="landing_page.php"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            <br>
            <a href="lupa_password.php">Lupa Password?</a>
        </div>

        <div class="footer-text">
            Silahkan Sign In Terlebih dahulu
        </div>
    </div>

</body>

</html>