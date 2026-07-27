<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng nhập Quản trị - {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6b8f3f;
            --primary-dark: #4f6b2f;
            --success: #07a35d;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-900: #111827;
            --radius: 12px;
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .loginContainer { width: 100%; max-width: 440px; position: relative; z-index: 1; }
        .loginCard {
            background: rgba(255,255,255,0.98);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }
        .loginCard_header { padding: 2rem 2rem 1.5rem; text-align: center; }
        .loginCard_header_logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        .loginCard_header_logo svg { width: 32px; height: 32px; color: white; }
        .loginCard_header_title { font-size: 1.5rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.375rem; }
        .loginCard_header_subtitle { font-size: 0.9375rem; color: var(--gray-500); }
        .loginCard_body { padding: 1.5rem 2rem 2rem; }
        .formGroup { margin-bottom: 1.25rem; }
        .formGroup_label { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--gray-700); }
        .formGroup_input { position: relative; }
        .formGroup_input input {
            width: 100%; padding: 0.875rem 1rem 0.875rem 3rem;
            font-size: 0.9375rem; color: var(--gray-900);
            background: var(--gray-50); border: 2px solid var(--gray-200);
            border-radius: var(--radius); outline: none; font-family: inherit;
        }
        .formGroup_input input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(107,143,63,0.15); background: white; }
        .formGroup_input input.error { border-color: var(--danger); background: #fef2f2; }
        .formGroup_input_icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); }
        .formGroup_input_icon svg { width: 20px; height: 20px; }
        .formGroup_remember { display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem; }
        .formGroup_remember label { font-size: 0.875rem; color: var(--gray-500); cursor: pointer; }
        .alert { display: none; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.25rem; font-size: 0.875rem; }
        .alert.show { display: block; }
        .alert--error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert--success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .submitBtn {
            width: 100%; padding: 1rem; margin-top: 1.5rem;
            font-size: 1rem; font-weight: 600; color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none; border-radius: var(--radius); cursor: pointer;
        }
        .submitBtn:disabled { opacity: 0.6; cursor: not-allowed; }
        .loginCard_footer { padding: 1rem 2rem; background: var(--gray-50); text-align: center; }
        .loginCard_footer a { color: var(--primary); text-decoration: none; font-size: 0.8125rem; }
        .brandFooter { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.5); font-size: 0.8125rem; }
    </style>
</head>
<body>
    <div class="loginContainer">
        <div class="loginCard">
            <div class="loginCard_header">
                <div class="loginCard_header_logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="loginCard_header_title">Đăng nhập Quản trị</h1>
                <p class="loginCard_header_subtitle">Chào mừng bạn quay trở lại! Vui lòng đăng nhập để tiếp tục.</p>
            </div>
            <div class="loginCard_body">
                <div id="alertBox" class="alert alert--error"><span id="alertMessage"></span></div>
                <form id="loginForm">
                    @csrf
                    <div class="formGroup">
                        <label class="formGroup_label" for="email">Email</label>
                        <div class="formGroup_input">
                            <input type="text" name="email" id="email" placeholder="admin@example.com" autocomplete="email" required>
                            <span class="formGroup_input_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                        </div>
                    </div>
                    <div class="formGroup">
                        <label class="formGroup_label" for="password">Mật khẩu</label>
                        <div class="formGroup_input">
                            <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required>
                            <span class="formGroup_input_icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                        </div>
                    </div>
                    <div class="formGroup_remember">
                        <input type="checkbox" name="remember" id="remember" value="1">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <button type="submit" class="submitBtn" id="submitBtn">Đăng nhập</button>
                </form>
            </div>
            <div class="loginCard_footer">
                <a href="/">← Quay về trang chủ</a>
            </div>
        </div>
        <div class="brandFooter">© {{ date('Y') }} {{ config('app.name') }}</div>
    </div>
    <script>
        const alertBox = document.getElementById('alertBox');
        const alertMessage = document.getElementById('alertMessage');
        const submitBtn = document.getElementById('submitBtn');

        function showAlert(message, type = 'error') {
            alertBox.className = 'alert alert--' + type + ' show';
            alertMessage.textContent = message;
        }

        function hideAlert() {
            alertBox.classList.remove('show');
        }

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            hideAlert();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            if (!email || !password) {
                showAlert('Vui lòng nhập đầy đủ thông tin.');
                return;
            }

            submitBtn.disabled = true;

            fetch('{{ route('admin.loginAdmin') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password, remember }),
            })
                .then(response => response.json().then(data => ({ status: response.status, data })))
                .then(({ data }) => {
                    submitBtn.disabled = false;
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect_url || '{{ route('admin.dashboard') }}';
                        }, 800);
                    } else {
                        showAlert(data.message || 'Đăng nhập thất bại.');
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    showAlert('Có lỗi xảy ra. Vui lòng thử lại sau.');
                });
        });

        document.getElementById('email').focus();
    </script>
</body>
</html>
