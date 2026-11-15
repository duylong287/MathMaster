<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu | MathMaster</title>
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
            background:
                radial-gradient(circle at 90% 0%, rgba(37, 99, 235, .10), transparent 40%),
                #f8fafc;
        }
        .card {
            width: 100%; max-width: 440px; padding: 40px 36px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .08);
        }
        .logo { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 26px; font-size: 20px; font-weight: 700; color: #0f172a; }
        .logo .inf { color: #2563eb; font-size: 26px; line-height: 1; }
        .key {
            width: 52px; height: 52px; margin: 0 auto 16px; border-radius: 14px;
            background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;
        }
        .key svg { width: 26px; height: 26px; }
        h1 { margin: 0 0 8px; text-align: center; font-size: 24px; font-weight: 800; color: #0f172a; }
        .sub { margin: 0 0 24px; text-align: center; font-size: 14px; line-height: 1.6; color: #64748b; }

        .alert { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 20px; border-radius: 10px; font-size: 13.5px; line-height: 1.5; }
        .alert svg { flex: none; width: 18px; height: 18px; margin-top: 1px; }
        .alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert.ok  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        .field { margin-bottom: 20px; }
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

        .btn {
            width: 100%; padding: 12px 16px; border: 0; border-radius: 10px;
            font: inherit; font-size: 14.5px; font-weight: 600; color: #fff;
            background: #2563eb; cursor: pointer; transition: background .15s, transform .15s;
        }
        .btn:hover { background: #1d4ed8; }
        .btn:active { transform: translateY(1px); }
        .btn:disabled { background: #93c5fd; cursor: not-allowed; }

        .back { margin-top: 22px; text-align: center; font-size: 13.5px; }
        .back a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .back a:hover { text-decoration: underline; }
        .hint { margin: 18px 0 0; padding-top: 16px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 12.5px; line-height: 1.6; color: #94a3b8; }

        @media (max-width: 480px) {
            body { padding: 16px; align-items: flex-start; }
            .card { margin-top: 4vh; padding: 32px 22px; }
        }
    </style>
</head>
<body>
<div class="card">
    <div class="logo"><span class="inf">∞</span> MathMaster</div>

    <div class="key">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
    </div>

    <h1>Khôi phục mật khẩu</h1>
    <p class="sub">Nhập email của tài khoản, hệ thống sẽ gửi mật khẩu mới vào hộp thư của bạn.</p>

    @if(session('status') || session('success'))
        <div class="alert ok" role="status">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('status') ?? session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert err" role="alert">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @error('email')
        <div class="alert err" role="alert">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <form id="forgotForm" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="field">
            <label class="lbl" for="email">Email tài khoản</label>
            <div class="input-wrap">
                <svg class="ico" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="input {{ $errors->has('email') ? 'invalid' : '' }}"
                       placeholder="ten@example.com" autocomplete="email" autofocus required>
            </div>
        </div>

        <button type="submit" id="submitBtn" class="btn">Gửi mật khẩu mới</button>
    </form>

    <div class="back"><a href="{{ route('login') }}">&larr; Quay lại đăng nhập</a></div>
    <p class="hint">Sau khi nhận mật khẩu mới, hãy đăng nhập và đổi lại mật khẩu ở mục Thông tin cá nhân.</p>
</div>

<script>
    (function () {
        // Chặn bấm gửi hai lần (mỗi lần gửi sẽ tạo một mật khẩu mới và tính vào giới hạn số lần)
        var form = document.getElementById('forgotForm');
        var btn = document.getElementById('submitBtn');
        form.addEventListener('submit', function () {
            if (form.checkValidity && !form.checkValidity()) { return; }
            btn.disabled = true;
            btn.textContent = 'Đang gửi...';
        });
    })();
</script>
</body>
</html>