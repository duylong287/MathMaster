<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | MathMaster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Trang tự chứa CSS: không cần npm run build, không phụ thuộc CDN */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
            background: #f8fafc;
        }
        .card {
            width: 100%; max-width: 980px; min-height: 580px;
            display: flex; overflow: hidden;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .08);
        }

        /* ---------- Cột trái ---------- */
        .brand-panel {
            position: relative; overflow: hidden;
            flex: 1 1 50%; padding: 48px;
            display: flex; flex-direction: column; justify-content: space-between;
            color: #fff;
            background:
                radial-gradient(circle at 85% 10%, rgba(37, 99, 235, .45), transparent 45%),
                linear-gradient(160deg, #0f172a 0%, #020617 100%);
        }
        .brand-panel .sym {
            position: absolute; font-weight: 700; color: rgba(255, 255, 255, .06);
            pointer-events: none; user-select: none; line-height: 1;
        }
        .sym.s1 { font-size: 150px; right: -10px; top: 60px; }
        .sym.s2 { font-size: 110px; left: 20px; bottom: 170px; }
        .sym.s3 { font-size: 90px;  right: 70px; bottom: 40px; }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; position: relative; }
        .logo .inf { color: #60a5fa; font-size: 28px; line-height: 1; }
        .brand-body { position: relative; }
        .brand-body h2 { margin: 0 0 12px; font-size: 28px; line-height: 1.25; font-weight: 800; }
        .brand-body p.lead { margin: 0 0 28px; color: #94a3b8; font-size: 14px; line-height: 1.6; max-width: 340px; }
        .features { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; }
        .features li { display: flex; gap: 12px; align-items: flex-start; font-size: 14px; color: #cbd5e1; line-height: 1.5; }
        .features .dot {
            flex: none; width: 24px; height: 24px; border-radius: 999px;
            background: rgba(37, 99, 235, .25); color: #93c5fd;
            display: flex; align-items: center; justify-content: center;
        }
        .features .dot svg { width: 14px; height: 14px; }
        .features strong { color: #fff; font-weight: 600; }
        .brand-foot { position: relative; font-size: 12px; color: #64748b; }

        /* ---------- Cột phải ---------- */
        .form-panel { flex: 1 1 50%; padding: 56px 56px; display: flex; align-items: center; }
        .form-wrap { width: 100%; max-width: 380px; margin: 0 auto; }
        .mobile-logo { display: none; align-items: center; gap: 8px; margin-bottom: 28px; font-size: 20px; font-weight: 700; color: #0f172a; }
        .mobile-logo .inf { color: #2563eb; font-size: 26px; line-height: 1; }
        h1 { margin: 0 0 6px; font-size: 26px; font-weight: 800; color: #0f172a; }
        .sub { margin: 0 0 26px; font-size: 14px; color: #64748b; }

        .alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 20px; border-radius: 10px; font-size: 13.5px; line-height: 1.5; }
        .alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
        .alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert.ok  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        .field { margin-bottom: 18px; }
        label.lbl { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 500; color: #334155; }
        .input-wrap { position: relative; }
        .input-wrap .ico { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #94a3b8; pointer-events: none; }
        .input {
            width: 100%; padding: 11px 14px 11px 40px;
            font: inherit; font-size: 14px; color: #0f172a;
            background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .input::placeholder { color: #94a3b8; }
        .input:focus { background: #fff; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
        .input.invalid { border-color: #f87171; background: #fff; }
        .input.has-toggle { padding-right: 44px; }
        .toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 34px; height: 34px; border: 0; background: transparent; color: #94a3b8;
            border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .toggle:hover { color: #475569; background: #f1f5f9; }
        .toggle svg { width: 18px; height: 18px; }
        .hidden { display: none !important; }

        .row { display: flex; justify-content: space-between; align-items: center; margin: 4px 0 22px; font-size: 13.5px; }
        .remember { display: flex; align-items: center; gap: 8px; color: #475569; cursor: pointer; user-select: none; }
        .remember input { width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; }
        .row a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .row a:hover { text-decoration: underline; }

        .btn {
            width: 100%; padding: 12px 16px; border: 0; border-radius: 10px;
            font: inherit; font-size: 14.5px; font-weight: 600; color: #fff;
            background: #2563eb; cursor: pointer;
            transition: background .15s, transform .15s;
        }
        .btn:hover { background: #1d4ed8; }
        .btn:active { transform: translateY(1px); }
        .btn:disabled { background: #93c5fd; cursor: not-allowed; }

        /* ---------- Màn hình nhỏ ---------- */
        @media (max-width: 860px) {
            body { padding: 16px; align-items: flex-start; }
            .brand-panel { display: none; }
            .card { min-height: 0; margin-top: 4vh; }
            .form-panel { padding: 32px 24px; }
            .mobile-logo { display: flex; }
        }
    </style>
</head>
<body>
<div class="card">

    <!-- Cột trái: giới thiệu -->
    <aside class="brand-panel">
        <span class="sym s1">∑</span>
        <span class="sym s2">π</span>
        <span class="sym s3">√</span>

        <div class="logo"><span class="inf">∞</span> MathMaster</div>

        <div class="brand-body">
            <h2>Luyện đề Toán,<br>theo dõi tiến bộ từng ngày</h2>
            <p class="lead">Hệ thống luyện đề trực tuyến dành cho học sinh THPT.</p>

            <ul class="features">
                <li>
                    <span class="dot"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                    <span><strong>Đa dạng các đề thi được</strong> biên soạn và sưu tầm</span>
                </li>
                <li>
                    <span class="dot"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                    <span><strong>Chấm điểm tự động</strong> theo cấu trúc ba phần của đề thi THPT.</span>
                </li>
                <li>
                    <span class="dot"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                    <span><strong>Phân tích năng lực</strong> theo chủ đề và mức độ câu hỏi.</span>
                </li>
            </ul>
        </div>

        <div class="brand-foot">&copy; {{ date('Y') }} MathMaster</div>
    </aside>

    <!-- Cột phải: form đăng nhập -->
    <main class="form-panel">
        <div class="form-wrap">
            <div class="mobile-logo"><span class="inf">∞</span> MathMaster</div>

            <h1>Chào mừng trở lại</h1>
            <p class="sub">Đăng nhập để tiếp tục luyện đề.</p>

            @if(session('success') || session('status'))
                <div class="alert ok" role="status">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') ?? session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert err" role="alert">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('email') ?: $errors->first() }}</span>
                </div>
            @endif

            <form id="loginForm" action="{{ route('login') }}" method="POST" novalidate>
                @csrf

                <div class="field">
                    <label class="lbl" for="email">Email đăng nhập</label>
                    <div class="input-wrap">
                        <svg class="ico" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="input {{ $errors->has('email') ? 'invalid' : '' }}"
                               placeholder="ten@example.com" autocomplete="username" autofocus required>
                    </div>
                </div>

                <div class="field">
                    <label class="lbl" for="password">Mật khẩu</label>
                    <div class="input-wrap">
                        <svg class="ico" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <input type="password" id="password" name="password"
                               class="input has-toggle {{ $errors->has('email') ? 'invalid' : '' }}"
                               placeholder="Nhập mật khẩu" autocomplete="current-password" required>
                        <button type="button" id="togglePassword" class="toggle" aria-label="Hiện hoặc ẩn mật khẩu">
                            <svg id="eyeOpen" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg id="eyeShut" class="hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="row">
                    <label class="remember" for="remember">
                        <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                        Ghi nhớ đăng nhập
                    </label>
                    <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                </div>

                <button type="submit" id="submitBtn" class="btn">Đăng nhập</button>
            </form>
        </div>
    </main>
</div>

<script>
    (function () {
        var pw = document.getElementById('password');
        var open = document.getElementById('eyeOpen');
        var shut = document.getElementById('eyeShut');

        document.getElementById('togglePassword').addEventListener('click', function () {
            var show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            open.classList.toggle('hidden', show);
            shut.classList.toggle('hidden', !show);
        });

        // Chặn bấm đăng nhập hai lần (tránh tăng số lần sai liên tiếp)
        var form = document.getElementById('loginForm');
        var btn = document.getElementById('submitBtn');
        form.addEventListener('submit', function () {
            if (form.checkValidity && !form.checkValidity()) { return; }
            btn.disabled = true;
            btn.textContent = 'Đang đăng nhập...';
        });
    })();
</script>
</body>
</html>