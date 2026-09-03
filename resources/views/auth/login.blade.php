<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

:root{
    --orange:#f97316;
    --orange-dark:#ea580c;
    --orange-soft:#fff7ed;
    --text:#0f172a;
    --muted:#64748b;
    --border:#dbe2ea;
    --danger-bg:#fef2f2;
    --danger-border:#fecaca;
    --danger-text:#b91c1c;
    --info-bg:#fff7ed;
    --info-border:#fed7aa;
    --info-text:#c2410c;
    --white:#ffffff;
    --shadow:0 24px 60px rgba(15,23,42,0.20);
    --shadow-soft:0 12px 30px rgba(15,23,42,0.12);
}

body{
    min-height:100vh;
    background:
        linear-gradient(rgba(15,23,42,0.72), rgba(15,23,42,0.72)),
        url("https://tse3.mm.bing.net/th/id/OIP.5BSmLxFdl_QxgTyHv8nQYAHaER?rs=1&pid=ImgDetMain&o=7&rm=3");
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
}

.login-wrapper{
    width:100%;
    max-width:460px;
}

.login-card{
    background:rgba(255,255,255,0.96);
    backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,0.55);
    border-radius:28px;
    box-shadow:var(--shadow);
    overflow:hidden;
}

.login-top{
    padding:28px 32px 10px;
    text-align:center;
}

.logo-shell{
    width:112px;
    height:112px;
    margin:0 auto 14px;
    border-radius:50%;
    background:linear-gradient(135deg,#fff7ed,#ffffff);
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 10px 24px rgba(249,115,22,0.18);
    border:1px solid rgba(249,115,22,0.12);
}

.logo-shell img{
    width:88px;
    height:88px;
    object-fit:contain;
}

.title{
    font-size:34px;
    font-weight:800;
    color:var(--text);
    margin-bottom:8px;
    letter-spacing:-0.03em;
}

.subtitle{
    color:var(--muted);
    font-size:15px;
    line-height:1.6;
}

.login-body{
    padding:22px 32px 32px;
}

.alert{
    padding:13px 14px;
    border-radius:14px;
    margin-bottom:16px;
    font-size:14px;
    font-weight:600;
    line-height:1.5;
}

.alert-danger{
    background:var(--danger-bg);
    color:var(--danger-text);
    border:1px solid var(--danger-border);
}

.alert-info{
    background:var(--info-bg);
    color:var(--info-text);
    border:1px solid var(--info-border);
}

.form-group{
    margin-bottom:18px;
}

.form-label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:700;
    color:#334155;
}

.input-wrap{
    position:relative;
}

.form-control{
    width:100%;
    height:56px;
    padding:0 16px;
    border:1.5px solid var(--border);
    border-radius:16px;
    background:#fbfdff;
    color:var(--text);
    font-size:15px;
    outline:none;
    transition:all .22s ease;
}

.form-control::placeholder{
    color:#94a3b8;
}

.form-control:focus{
    border-color:rgba(249,115,22,0.70);
    background:#fff;
    box-shadow:0 0 0 4px rgba(249,115,22,0.12);
}

.password-input{
    padding-right:82px;
}

.toggle{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    border:none;
    background:none;
    color:#64748b;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    padding:4px 6px;
}

.toggle:hover{
    color:var(--orange);
}

.login-btn{
    width:100%;
    height:58px;
    border:none;
    border-radius:16px;
    background:linear-gradient(135deg,var(--orange) 0%, var(--orange-dark) 100%);
    color:#fff;
    font-size:17px;
    font-weight:800;
    cursor:pointer;
    margin-top:8px;
    box-shadow:0 14px 30px rgba(249,115,22,0.28);
    transition:transform .2s ease, box-shadow .2s ease, opacity .2s ease;
}

.login-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 18px 34px rgba(249,115,22,0.34);
}

.login-btn:active{
    transform:translateY(0);
}

.bottom-links{
    margin-top:18px;
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:14px;
}

.forgot-wrap{
    width:100%;
    text-align:left;
}

.forgot-wrap a{
    color:var(--orange);
    font-size:14px;
    font-weight:700;
    text-decoration:none;
}

.forgot-wrap a:hover{
    text-decoration:underline;
}

.divider{
    width:100%;
    height:1px;
    background:linear-gradient(to right, transparent, #e2e8f0, transparent);
}

.register-wrap{
    text-align:center;
    font-size:14px;
    color:#475569;
    line-height:1.7;
}

.register-wrap a{
    color:var(--orange);
    font-weight:800;
    text-decoration:none;
}

.register-wrap a:hover{
    text-decoration:underline;
}

.footer{
    margin-top:18px;
    text-align:center;
    font-size:13px;
    color:#64748b;
}

.footer strong{
    color:var(--text);
}

@media (max-width:576px){
    body{
        padding:14px;
    }

    .login-top{
        padding:24px 20px 8px;
    }

    .login-body{
        padding:18px 20px 24px;
    }

    .logo-shell{
        width:96px;
        height:96px;
    }

    .logo-shell img{
        width:76px;
        height:76px;
    }

    .title{
        font-size:30px;
    }

    .subtitle{
        font-size:14px;
    }

    .form-control{
        height:54px;
        font-size:14px;
    }

    .login-btn{
        height:55px;
        font-size:16px;
    }
}
</style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-top">
            <div class="logo-shell">
                <img src="ccc3d.png" alt="Logo">
            </div>

            <h1 class="title">Welcome</h1>
            <p class="subtitle">Login to your account to continue</p>
        </div>

        <div class="login-body">
            <?php if ($force_login): ?>
                <div class="alert alert-info">Please log in to continue with your job application.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Student ID</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            name="student_id"
                            class="form-control"
                            placeholder="Enter your Student ID"
                            value="<?php echo htmlspecialchars(old('student_id', old('username', ''))); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control password-input"
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" class="toggle" onclick="togglePassword()">Show</button>
                    </div>
                </div>

                <button class="login-btn" type="submit">Login</button>

                <div class="bottom-links">
                    <div class="forgot-wrap">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>

                    <div class="divider"></div>

                    <div class="register-wrap">
                        No account yet?
                        <a href="{{ route('register') }}">Register here</a>
                    </div>
                </div>

                <div class="footer">
                    <strong>Welcome</strong>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
function togglePassword(){
    const pass = document.getElementById("password");
    const btn = document.querySelector(".toggle");

    if(pass.type === "password"){
        pass.type = "text";
        btn.innerHTML = "Hide";
    } else {
        pass.type = "password";
        btn.innerHTML = "Show";
    }
}
</script>

</body>
</html>
