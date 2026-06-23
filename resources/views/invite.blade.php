<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Primary Meta Tags --}}
    <title>Kelompok Magang {{ $groupName }} di {{ $appName }}</title>
    <meta name="description"
        content="Gunakan kode {{ $groupCode }} untuk bergabung ke kelompok magang {{ $groupName }} di {{ $appName }}.">
    <meta name="robots" content="noindex, nofollow">

    {{-- Open Graph / Facebook / WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $appUrl }}/join/{{ $groupCode }}">
    <meta property="og:title" content="Kelompok Magang {{ $groupName }}">
    <meta property="og:description"
        content="Gunakan kode {{ $groupCode }} untuk bergabung ke kelompok magang {{ $groupName }} di {{ $appName }}.">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Banner kelompok magang {{ $groupName }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:locale" content="id_ID">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kelompok Magang {{ $groupName }}">
    <meta name="twitter:description"
        content="Gunakan kode {{ $groupCode }} untuk bergabung ke kelompok magang {{ $groupName }} di {{ $appName }}.">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    {{-- Instant redirect for real users (crawlers ignore this) --}}
    <meta http-equiv="refresh" content="0;url={{ $redirectUrl }}">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100svh;
        }

        .card {
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #334155;
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }

        a {
            color: #818cf8;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="spinner"></div>
        <p>Mengalihkan ke halaman bergabung&hellip;</p>
        <p style="margin-top:.5rem">
            <a href="{{ $redirectUrl }}">Klik di sini jika tidak dialihkan otomatis</a>
        </p>
    </div>
    <script>
        window.location.replace({{ Js::from($redirectUrl) }});
    </script>
</body>

</html>