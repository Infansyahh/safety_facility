<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Darurat - Safety Facility</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body,
        html {
            height: 100%;
            background: url('https://images.unsplash.com/photo-1516549655169-df83a0774514?q=80&w=1920') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-ah {
            color: #00a2e8;
            font-size: 38px;
            font-weight: 900;
            line-height: 1;
            display: block;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
        }

        .logo-text {
            color: #ff3b3b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
        }

        .glass-card h2 {
            color: #ffffff;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 30px;
            background-color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            color: #333;
            outline: none;
            transition: 0.3s;
        }

        .input-group input:focus {
            background-color: rgba(255, 255, 255, 1);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 30px;
            background-color: #ff4757;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background-color: #ff253a;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
            transition: 0.3s;
        }

        .back-link:hover {
            text-decoration: underline;
            color: #f1f2f6;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="glass-card">

            <div class="logo-container">
                <span class="logo-ah">SF</span>
                <span class="logo-text">Safety Facility</span>
            </div>

            <h2><i class="fa-solid fa-key"></i> Login Darurat</h2>

            <form action="proses-login-darurat.php" method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username Darurat" required autocomplete="off">
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="Password Darurat" required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk
                </button>
            </form>

            <a href="login.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
            </a>
        </div>
    </div>

</body>

</html>