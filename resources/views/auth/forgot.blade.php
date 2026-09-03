<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    background:
        linear-gradient(rgba(15,23,42,0.72), rgba(15,23,42,0.72)),
        url("https://tse3.mm.bing.net/th/id/OIP.5BSmLxFdl_QxgTyHv8nQYAHaER?rs=1&pid=ImgDetMain&o=7&rm=3");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
}

.card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(8px);
    padding: 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.20);
    border: 1px solid rgba(255, 255, 255, 0.55);
}

.card h2 {
    color: #0f172a;
    text-align: center;
    margin-bottom: 30px;
    font-size: 28px;
    font-weight: 800;
}

input {
    width: 100%;
    padding: 14px;
    margin: 12px 0;
    border-radius: 12px;
    border: 1.5px solid #dbe2ea;
    box-sizing: border-box;
    font-size: 15px;
    transition: all 0.22s ease;
}

input:focus {
    outline: none;
    border-color: rgba(249, 115, 22, 0.70);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
}

button {
    width: 100%;
    padding: 14px;
    background: #f97316;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    font-size: 15px;
    margin-top: 10px;
    transition: all 0.22s ease;
}

button:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(249, 115, 22, 0.20);
}

.alert {
    padding: 14px;
    margin-bottom: 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
</style>
</head>

<body>

<div class="card">
<h2>Forgot Password</h2>

<?php if($msg): ?>
<div class="alert success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" value="<?php echo htmlspecialchars(old('email', '')); ?>" placeholder="Enter your email" required>
<button type="submit">Send Reset Link</button>
</form>

</div>

</body>
</html>
