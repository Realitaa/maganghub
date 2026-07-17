<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Atur Ulang Kata Sandi - MagangHub</title>
    <style type="text/css">
        /* Mobile responsive overrides */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
                box-sizing: border-box !important;
            }

            .email-card {
                padding-left: 20px !important;
                padding-right: 20px !important;
                padding-top: 30px !important;
                padding-bottom: 30px !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f4f7f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f4f7f6"
        style="background-color: #f4f7f6; padding-top: 40px; padding-bottom: 40px;">
        <tr>
            <td align="center">
                <table class="email-container" border="0" cellpadding="0" cellspacing="0"
                    style="max-width: 570px; width: 100%; margin-left: auto; margin-right: auto;">
                    <!-- Logo Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <span
                                style="font-size: 24px; font-weight: bold; color: #007a55; letter-spacing: -0.5px;">MagangHub</span>
                        </td>
                    </tr>
                    <!-- Email Card -->
                    <tr>
                        <td class="email-card" bgcolor="#ffffff"
                            style="background-color: #ffffff; padding-left: 40px; padding-right: 40px; padding-top: 40px; padding-bottom: 40px; border-radius: 8px; border-width: 1px; border-style: solid; border-color: #e2e8f0;">
                            <h1
                                style="margin-top: 0; margin-bottom: 16px; font-size: 20px; font-weight: 700; color: #1a202c; line-height: 1.4;">
                                Halo, {{ $name }}!</h1>

                            <p
                                style="margin-top: 0; margin-bottom: 24px; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun
                                Anda di MagangHub.
                            </p>

                            <!-- Button Container Table -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" bgcolor="#007a55"
                                                    style="background-color: #007a55; border-radius: 6px; padding-left: 24px; padding-right: 24px; padding-top: 12px; padding-bottom: 12px;">
                                                    <a href="{{ $url }}" target="_blank"
                                                        style="font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; display: block; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                                                        Atur Ulang Kata Sandi
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="margin-top: 0; margin-bottom: 16px; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                Tautan atur ulang kata sandi ini akan kedaluwarsa dalam waktu 60 menit.
                            </p>

                            <p
                                style="margin-top: 0; margin-bottom: 32px; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan saja email ini.
                            </p>

                            <p
                                style="margin-top: 0; margin-bottom: 0; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                Salam hangat,<br />
                                <strong>Tim MagangHub</strong>
                            </p>

                            <!-- Divider Line -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                style="margin-top: 32px; margin-bottom: 24px;">
                                <tr>
                                    <td
                                        style="border-top-width: 1px; border-top-style: solid; border-top-color: #e2e8f0; height: 1px; line-height: 1px; font-size: 1px;">
                                        &nbsp;</td>
                                </tr>
                            </table>

                            <p
                                style="margin-top: 0; margin-bottom: 0; font-size: 12px; line-height: 1.5; color: #718096;">
                                Jika Anda mengalami kendala saat menekan tombol "Atur Ulang Kata Sandi", salin dan
                                tempel URL di bawah ini ke peramban web Anda:
                                <br />
                                <a href="{{ $url }}"
                                    style="color: #007a55; text-decoration: underline; word-break: break-all;">
                                    {{ $url }}
                                </a>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer Info -->
                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <p
                                style="margin-top: 0; margin-bottom: 0; font-size: 12px; color: #a0aec0; line-height: 1.5;">
                                &copy; {{ date('Y') }} MagangHub. Hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>