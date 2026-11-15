<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mật khẩu mới | MathMaster</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#1e293b;">
    <!-- Dòng xem trước trong hộp thư -->
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">Mật khẩu mới cho tài khoản MathMaster của bạn.</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">

                    <!-- Đầu thư -->
                    <tr>
                        <td align="center" style="background-color:#0f172a; padding:22px 24px;">
                            <span style="color:#60a5fa; font-size:26px; font-weight:bold; vertical-align:middle;">&infin;</span>
                            <span style="color:#ffffff; font-size:20px; font-weight:bold; vertical-align:middle; padding-left:6px;">MathMaster</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 32px 28px 32px;">
                            <h1 style="margin:0 0 10px 0; font-size:22px; line-height:1.3; color:#0f172a; text-align:center;">Mật khẩu mới của bạn</h1>
                            <p style="margin:0 0 24px 0; font-size:15px; line-height:1.6; color:#475569; text-align:center;">Chào <strong style="color:#0f172a;">{{ $name }}</strong>, hệ thống đã nhận được yêu cầu cấp lại mật khẩu cho tài khoản của bạn.</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; margin-bottom:26px;">
                                <tr>
                                    <td align="center" style="padding:20px;">
                                        <div style="color:#64748b; font-size:12px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">Mật khẩu mới</div>
                                        <div style="color:#0f172a; font-weight:bold; font-size:24px; letter-spacing:2px; font-family:Consolas, 'Courier New', monospace;">{{ $newPassword }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 26px auto;">
                                <tr>
                                    <td align="center" style="background-color:#2563eb; border-radius:8px;">
                                        <a href="{{ route('login') }}" style="display:inline-block; padding:12px 28px; color:#ffffff; font-size:15px; font-weight:bold; text-decoration:none;">Đăng nhập ngay</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 10px 0; font-size:13px; line-height:1.6; color:#64748b;">
                                Sau khi đăng nhập, hãy đổi lại mật khẩu ở mục "Thông tin cá nhân" để đảm bảo an toàn.
                            </p>
                            <p style="margin:0; padding-top:16px; border-top:1px solid #e2e8f0; font-size:13px; line-height:1.6; color:#64748b;">
                                Nếu bạn không yêu cầu cấp lại mật khẩu, hãy đăng nhập bằng mật khẩu trên rồi đổi ngay, hoặc báo cho giáo viên phụ trách.
                            </p>

                        </td>
                    </tr>

                    <!-- Chân thư -->
                    <tr>
                        <td align="center" style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 24px; font-size:12px; color:#94a3b8; line-height:1.6;">
                            Đây là email tự động từ hệ thống MathMaster, vui lòng không trả lời email này.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>