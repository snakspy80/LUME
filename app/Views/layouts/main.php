<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Lume Tutorials') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <style>
        :root {
            --container-max: 1320px;
            --ink: #eef4f0;
            --muted: #9bb0a6;
            --line: rgba(111, 157, 134, 0.35);
            --bg: #050807;
            --panel: rgba(12, 18, 16, 0.9);
            --brand: #2ab673;
            --brand-strong: #8de0b4;
            --dark: #050807;
            --danger: #b91c1c;
            --shadow: 0 18px 36px rgba(0, 0, 0, 0.35);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Sora', sans-serif;
            color: var(--ink);
            position: relative;
            min-height: 100vh;
            isolation: isolate;
            background-color: #030504;
            background-image:
                radial-gradient(circle at 16% 12%, rgba(255, 201, 71, 0.2) 0%, rgba(255, 201, 71, 0) 28%),
                radial-gradient(circle at 84% 14%, rgba(43, 214, 130, 0.24) 0%, rgba(43, 214, 130, 0) 30%),
                radial-gradient(circle at 74% 76%, rgba(21, 153, 96, 0.2) 0%, rgba(21, 153, 96, 0) 24%),
                radial-gradient(circle at 46% 100%, rgba(255, 201, 71, 0.12) 0%, rgba(255, 201, 71, 0) 34%),
                radial-gradient(circle at 54% 42%, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0) 26%),
                repeating-linear-gradient(90deg, rgba(43, 214, 130, 0.075) 0 1px, transparent 1px 34px),
                repeating-linear-gradient(0deg, rgba(255, 201, 71, 0.05) 0 1px, transparent 1px 34px),
                linear-gradient(145deg, #010202 0%, #07100c 36%, #0d1813 100%);
            background-attachment: fixed;
        }

        body[data-theme="light"] {
            --ink: #13241a;
            --muted: #486556;
            --line: rgba(44, 121, 82, 0.22);
            --panel: rgba(255, 255, 255, 0.86);
            --shadow: 0 18px 36px rgba(15, 30, 52, 0.1);
            background-color: #f9fcf7;
            background-image:
                radial-gradient(circle at 16% 12%, rgba(255, 206, 84, 0.28) 0%, rgba(255, 206, 84, 0) 28%),
                radial-gradient(circle at 84% 14%, rgba(43, 214, 130, 0.24) 0%, rgba(43, 214, 130, 0) 30%),
                radial-gradient(circle at 74% 76%, rgba(21, 153, 96, 0.14) 0%, rgba(21, 153, 96, 0) 24%),
                radial-gradient(circle at 44% 100%, rgba(255, 206, 84, 0.14) 0%, rgba(255, 206, 84, 0) 34%),
                radial-gradient(circle at 56% 44%, rgba(34, 197, 94, 0.08) 0%, rgba(34, 197, 94, 0) 24%),
                repeating-linear-gradient(90deg, rgba(43, 214, 130, 0.05) 0 1px, transparent 1px 34px),
                repeating-linear-gradient(0deg, rgba(255, 206, 84, 0.04) 0 1px, transparent 1px 34px),
                linear-gradient(145deg, #fffef8 0%, #f8fcf3 42%, #eefaf4 100%);
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            opacity: 0.18;
            background-repeat: no-repeat;
        }

        body::before {
            background-image:
                url("https://api.iconify.design/mdi/react.svg?color=%23187f56"),
                url("https://api.iconify.design/mdi/lan-connect.svg?color=%23187f56"),
                url("https://api.iconify.design/mdi/monitor.svg?color=%23187f56"),
                url("https://api.iconify.design/mdi/server-network.svg?color=%23187f56"),
                url("https://api.iconify.design/mdi/code-array.svg?color=%23187f56"),
                url("https://api.iconify.design/mdi/shield-lock-outline.svg?color=%23187f56");
            background-position:
                right 12% top 90px,
                right 30% top 210px,
                right 8% top 320px,
                right 24% top 470px,
                right 6% top 560px,
                right 28% top 640px;
            background-size:
                110px 110px,
                110px 110px,
                96px 96px,
                92px 92px,
                88px 88px,
                88px 88px;
        }

        body::after {
            background-image:
                url("https://api.iconify.design/mdi/lightbulb-on-outline.svg?color=%23d8a106"),
                url("https://api.iconify.design/mdi/head-question-outline.svg?color=%23d8a106"),
                url("https://api.iconify.design/mdi/palette-outline.svg?color=%23d8a106"),
                url("https://api.iconify.design/mdi/draw-pen.svg?color=%23d8a106"),
                url("https://api.iconify.design/mdi/star-four-points-outline.svg?color=%23d8a106"),
                url("https://api.iconify.design/mdi/creation.svg?color=%23d8a106");
            background-position:
                left 8% top 92px,
                left 26% top 208px,
                left 10% top 360px,
                left 28% top 510px,
                left 14% top 618px,
                left 30% top 680px;
            background-size:
                112px 112px,
                96px 96px,
                92px 92px,
                86px 86px,
                74px 74px,
                88px 88px;
        }

        .container {
            width: min(var(--container-max), calc(100% - clamp(20px, 4vw, 52px)));
            margin: 0 auto;
        }

        .utility {
            background: rgba(4, 7, 6, 0.9);
            color: #f5f7fa;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        body[data-theme="light"] .utility {
            background: rgba(255, 255, 255, 0.72);
            color: var(--ink);
            border-bottom: 1px solid rgba(19, 36, 26, 0.08);
            backdrop-filter: blur(10px);
        }

        .utility-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 7px 0;
            font-size: 12px;
        }

        .utility-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .utility a {
            color: #dde4ec;
            text-decoration: none;
            opacity: 0.94;
        }

        body[data-theme="light"] .utility a { color: #254334; }

        .utility a:hover { opacity: 1; color: #ffffff; }
        .utility a.is-active {
            color: #9df0c8;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(42, 182, 115, 0.5);
        }

        .header {
            position: sticky;
            top: 0;
            z-index: 30;
            background:
                linear-gradient(180deg, rgba(12, 18, 20, 0.72) 0%, rgba(12, 18, 20, 0.52) 62%, rgba(12, 18, 20, 0.12) 100%);
            backdrop-filter: blur(10px);
            border-bottom: none;
            box-shadow: none;
        }

        body[data-theme="light"] .header {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.58) 62%, rgba(255, 255, 255, 0.14) 100%);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 12px 0;
        }

        .header-lead {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1 1 520px;
            min-width: min(100%, 320px);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0;
            text-decoration: none;
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: lowercase;
        }

        .brand-text {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            line-height: 1;
        }

        .brand-text strong {
            color: #8ef0ba;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 0.03em;
        }

        .brand-text span {
            color: rgba(255, 255, 255, 0.62);
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .header-search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: min(100%, 240px);
            max-width: 520px;
        }

        .header-search input {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 10px 14px;
            font: inherit;
            background: rgba(255, 255, 255, 0.06);
            color: #f5f7fa;
            outline: none;
        }

        body[data-theme="light"] .header-search input {
            border-color: rgba(19, 36, 26, 0.12);
            background: rgba(19, 36, 26, 0.04);
            color: var(--ink);
        }

        body[data-theme="light"] .header-search input::placeholder {
            color: rgba(19, 36, 26, 0.45);
        }

        .header-search input::placeholder {
            color: rgba(245, 247, 250, 0.56);
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .menu {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        body[data-theme="light"] .menu {
            border-top-color: rgba(19, 36, 26, 0.08);
        }

        .menu a {
            text-decoration: none;
            color: rgba(245, 247, 250, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 600;
        }

        body[data-theme="light"] .menu a {
            color: #284435;
            border-color: rgba(19, 36, 26, 0.12);
            background: rgba(255, 255, 255, 0.54);
        }

        .menu a:hover {
            border-color: rgba(51, 184, 121, 0.42);
            color: #ffffff;
            background: rgba(51, 184, 121, 0.08);
            box-shadow: 0 0 0 1px rgba(51, 184, 121, 0.1);
        }

        .menu a.is-active {
            background: linear-gradient(135deg, var(--brand) 0%, #23a66f 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 0 0 1px rgba(101, 235, 168, 0.14), 0 6px 14px rgba(31, 140, 95, 0.16);
        }

        .btn {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 9px 13px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: transform 0.16s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand) 0%, #23a66f 100%);
            color: #fff;
            box-shadow: 0 0 0 1px rgba(101, 235, 168, 0.18), 0 0 20px rgba(42, 182, 115, 0.22), 0 8px 16px rgba(31, 140, 95, 0.25);
        }

        .btn.is-active {
            background: linear-gradient(135deg, var(--brand) 0%, #23a66f 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 0 0 1px rgba(101, 235, 168, 0.22), 0 0 22px rgba(42, 182, 115, 0.28), 0 8px 16px rgba(31, 140, 95, 0.25);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.04);
            color: var(--ink);
            border-color: rgba(255, 255, 255, 0.12);
        }

        body[data-theme="light"] .btn-light {
            background: rgba(255, 255, 255, 0.72);
            border-color: rgba(19, 36, 26, 0.12);
        }

        .theme-toggle {
            min-width: 92px;
        }

        .btn-light:hover {
            border-color: rgba(51, 184, 121, 0.55);
            background: rgba(51, 184, 121, 0.12);
            box-shadow: 0 0 0 1px rgba(51, 184, 121, 0.14), 0 0 18px rgba(42, 182, 115, 0.18);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .page { padding: clamp(14px, 2vw, 24px) 0 30px; }

        .layout {
            display: grid;
            grid-template-columns: minmax(230px, clamp(230px, 23vw, 290px)) minmax(0, 1fr);
            gap: clamp(12px, 1.6vw, 22px);
            align-items: start;
        }

        .sidebar,
        .card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            background: rgba(10, 14, 13, 0.84);
            box-shadow: var(--shadow);
        }

        body[data-theme="light"] .sidebar,
        body[data-theme="light"] .card {
            border-color: rgba(19, 36, 26, 0.08);
            background: rgba(255, 255, 255, 0.82);
        }

        .sidebar {
            position: sticky;
            top: 112px;
            max-height: calc(100vh - 116px);
            overflow: auto;
        }

        .side-title {
            margin: 0;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 14px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 14px 14px 0 0;
        }

        body[data-theme="light"] .side-title {
            background: rgba(19, 36, 26, 0.04);
            border-bottom-color: rgba(19, 36, 26, 0.08);
        }

        .side-list { list-style: none; margin: 0; padding: 8px; }
        .side-list li a {
            display: block;
            text-decoration: none;
            color: var(--ink);
            font-size: 14px;
            padding: 9px 10px;
            border-radius: 10px;
        }

        .side-list li a:hover { background: rgba(42, 182, 115, 0.12); color: var(--brand-strong); }

        .content { min-width: 0; }
        .content .card { padding: clamp(14px, 1.35vw, 20px); margin-bottom: 12px; }
        .content a {
            color: var(--ink);
        }

        .content a:visited {
            color: var(--ink);
        }

        .content a:hover {
            color: var(--brand-strong);
        }

        .title {
            margin: 0 0 6px;
            font-family: 'DM Serif Display', serif;
            font-size: clamp(30px, 4vw, 46px);
            line-height: 1.08;
        }

        .subtitle { margin: 0; color: var(--muted); line-height: 1.65; }
        .meta { color: var(--muted); font-size: 13px; margin-bottom: 8px; }

        .badge {
            display: inline-block;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 12px;
            background: rgba(255, 255, 255, 0.04);
        }

        .video-wrap {
            position: relative;
            padding-top: 56.25%;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--line);
            margin-bottom: 10px;
            background: #000;
        }

        .video-wrap iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .search-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .input, textarea, select {
            width: 100%;
            padding: 10px 11px;
            border: 1px solid var(--line);
            border-radius: 9px;
            font: inherit;
            background: rgba(255, 255, 255, 0.05);
            color: var(--ink);
            margin-bottom: 9px;
            outline: none;
        }

        body[data-theme="light"] .input,
        body[data-theme="light"] textarea,
        body[data-theme="light"] select {
            background: rgba(255, 255, 255, 0.88);
        }

        select option {
            background: #0b1210;
            color: var(--ink);
        }

        .input:focus, textarea:focus, select:focus {
            border-color: rgba(31, 140, 95, 0.5);
            box-shadow: 0 0 0 3px rgba(31, 140, 95, 0.16);
        }

        .label { display: block; font-weight: 600; margin-bottom: 4px; }
        .field-error { color: #b91c1c; margin: -4px 0 8px; font-size: 13px; }
        .grid { display: grid; gap: 10px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .profile-shell { display:grid; grid-template-columns:minmax(220px, 260px) minmax(0, 1fr); gap:16px; }

        .stack { display: flex; gap: 8px; flex-wrap: wrap; }

        .flash {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
            border: 1px solid transparent;
        }

        .flash-ok { background: rgba(19, 78, 54, 0.35); color: #c6f6dc; border-color: rgba(74, 222, 128, 0.35); }
        .flash-err { background: rgba(127, 29, 29, 0.35); color: #fecaca; border-color: rgba(248, 113, 113, 0.35); }

        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .stat { background: rgba(255, 255, 255, 0.03); border: 1px solid var(--line); border-radius: 10px; padding: 10px; }
        .stat .n { font-size: 24px; font-weight: 700; }
        .stat .l { color: var(--muted); font-size: 12px; }
        .profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 9px;
            border: 1px solid rgba(93, 102, 114, 0.25);
            border-radius: 999px;
            color: var(--ink);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.04);
            font-size: 13px;
            font-weight: 600;
        }

        .profile-pill:hover {
            border-color: rgba(51, 184, 121, 0.55);
            box-shadow: 0 0 0 1px rgba(51, 184, 121, 0.14), 0 0 18px rgba(42, 182, 115, 0.16);
        }

        .avatar-mini {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid var(--line);
            display: inline-block;
        }

        .avatar-fallback {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid rgba(42, 182, 115, 0.28);
            background: rgba(42, 182, 115, 0.14);
            color: #bdf3d6;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
        }

        .site-footer {
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(6, 10, 8, 0.82);
            backdrop-filter: blur(10px);
        }

        body[data-theme="light"] .site-footer {
            border-top-color: rgba(19, 36, 26, 0.08);
            background: rgba(255, 255, 255, 0.78);
        }

        .footer-shell {
            padding: 24px 0 14px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: minmax(230px, 1.25fr) repeat(4, minmax(160px, 1fr));
            gap: 22px;
        }

        .footer-brand {
            display: grid;
            gap: 10px;
        }

        .footer-brand strong {
            font-size: 22px;
            color: #8ef0ba;
            letter-spacing: 0.03em;
            text-transform: lowercase;
        }

        .footer-copy {
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .footer-title {
            margin: 0 0 10px;
            color: #41c880;
            font-size: 16px;
            font-weight: 700;
        }

        body[data-theme="light"] .footer-title {
            color: #1c9a5f;
        }

        .footer-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 8px;
        }

        .footer-list a,
        .footer-list span {
            color: var(--ink);
            text-decoration: none;
            line-height: 1.6;
            font-size: 14px;
        }

        .footer-person {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: baseline;
        }

        .footer-person strong {
            font-size: 14px;
        }

        .footer-person-links {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-list a:hover {
            color: var(--brand-strong);
        }

        .footer-socials {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .footer-socials a {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 7px 12px;
            text-decoration: none;
            color: var(--ink);
            background: rgba(255, 255, 255, 0.04);
            font-size: 13px;
            font-weight: 600;
        }

        body[data-theme="light"] .footer-socials a {
            border-color: rgba(19, 36, 26, 0.12);
            background: rgba(255, 255, 255, 0.72);
        }

        .footer-socials a:hover {
            border-color: rgba(51, 184, 121, 0.4);
            background: rgba(51, 184, 121, 0.08);
        }

        .footer-bottom {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--muted);
            font-size: 13px;
        }

        body[data-theme="light"] .footer-bottom {
            border-top-color: rgba(19, 36, 26, 0.08);
        }

        @media (max-width: 1180px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { position: static; max-height: none; }
        }

        @media (max-width: 980px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .menu { padding-top: 8px; }
            .grid-2 { grid-template-columns: 1fr; }
            .profile-shell { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .utility-inner { justify-content: center; }
            .utility-links:last-child { display: none; }
        }

        @media (max-width: 640px) {
            .btn { padding: 8px 11px; }
            .brand { font-size: 16px; }
            .header-lead { flex-direction: column; align-items: stretch; }
            .header-search { max-width: none; width: 100%; }
            .footer-grid { grid-template-columns: 1fr; }
            .top-actions { width: 100%; }
        }
    </style>
</head>
<body>
    <?php
        $uriPath = trim((string) service('request')->getUri()->getPath(), '/');
        $isPath = static fn(string $path): bool => $uriPath === trim($path, '/');
        $isPathPrefix = static fn(string $prefix): bool => $uriPath !== '' && str_starts_with($uriPath, trim($prefix, '/'));
        $isHome = $uriPath === '' || $uriPath === 'index.php';
        $notificationCount = session()->get('user_id') ? lume_notification_unread_count((int) session()->get('user_id')) : 0;
    ?>
    <div class="utility">
        <div class="container utility-inner">
            <div class="utility-links">
                <a class="<?= $isHome ? 'is-active' : '' ?>" href="/">Home</a>
                <a class="<?= ($isPath('search') || $isPathPrefix('course')) ? 'is-active' : '' ?>" href="/search">Explore</a>
                <a class="<?= $isPath('my-learning') ? 'is-active' : '' ?>" href="/my-learning">My Learning</a>
                <a class="<?= $isPath('leaderboard') ? 'is-active' : '' ?>" href="/leaderboard">Leaderboard</a>
                <a class="<?= ($isPath('dashboard') || $isPathPrefix('posts/edit')) ? 'is-active' : '' ?>" href="/dashboard">Creator Studio</a>
            </div>
            <div class="utility-links">
                <a href="/search">Trending</a>
                <a href="/search">Creators</a>
            </div>
        </div>
    </div>

    <header class="header">
        <div class="container header-inner">
            <div class="header-lead">
                <a class="brand" href="/">
                    <span class="brand-text">
                        <strong>lume</strong>
                        <span>creative studio</span>
                    </span>
                </a>
                <form class="header-search" method="get" action="/search">
                    <input type="text" name="q" placeholder="Search courses, creators, topics" value="<?= esc((string) service('request')->getGet('q')) ?>" data-typing-placeholder="React roadmap|LAN and WAN basics|Python arrays|Ethical hacking intro|DSA for beginners|Build with PHP">
                    <button class="btn btn-brand" type="submit">Search</button>
                </form>
            </div>

            <div class="top-actions">
                <button class="btn btn-light theme-toggle" type="button" id="themeToggle" aria-label="Toggle day and night mode">Day</button>
                <a class="btn btn-light <?= ($isPath('search') || $isPathPrefix('course')) ? 'is-active' : '' ?>" href="/search">Explore</a>
                <?php if (session()->get('user_id')): ?>
                    <a class="btn btn-light <?= $isPath('notifications') ? 'is-active' : '' ?>" href="/notifications">Alerts<?= $notificationCount > 0 ? ' (' . esc((string) $notificationCount) . ')' : '' ?></a>
                    <?php
                        $avatar = (string) session()->get('user_avatar');
                        $name = (string) session()->get('user_name');
                    ?>
                    <a class="profile-pill" href="/profile">
                        <?php if ($avatar !== ''): ?>
                            <img src="/<?= esc($avatar) ?>" alt="avatar" class="avatar-mini">
                        <?php else: ?>
                            <span class="avatar-fallback"><?= esc(strtoupper(substr($name !== '' ? $name : 'U', 0, 1))) ?></span>
                        <?php endif; ?>
                        <span><?= esc($name !== '' ? $name : 'Profile') ?></span>
                    </a>
                    <form action="/logout" method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <button class="btn btn-danger" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-light <?= $isPath('login') ? 'is-active' : '' ?>" href="/login">Login</a>
                    <a class="btn btn-light <?= $isPath('register') ? 'is-active' : '' ?>" href="/register">Register</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="container menu">
            <a class="<?= $isHome ? 'is-active' : '' ?>" href="/">Home</a>
            <a class="<?= ($isPath('search') || $isPathPrefix('course')) ? 'is-active' : '' ?>" href="/search">Search</a>
            <a class="<?= $isPath('leaderboard') ? 'is-active' : '' ?>" href="/leaderboard">Leaderboard</a>
            <?php if (session()->get('user_id')): ?>
                <a class="<?= $isPath('notifications') ? 'is-active' : '' ?>" href="/notifications">Alerts<?= $notificationCount > 0 ? ' (' . esc((string) $notificationCount) . ')' : '' ?></a>
                <a class="<?= ($isPath('dashboard') || $isPathPrefix('posts/edit')) ? 'is-active' : '' ?>" href="/dashboard">Dashboard</a>
                <a class="<?= $isPath('my-learning') ? 'is-active' : '' ?>" href="/my-learning">My Learning</a>
                <a class="<?= $isPath('posts/create') ? 'is-active' : '' ?>" href="/posts/create">Create Course</a>
            <?php else: ?>
                <a class="<?= $isPath('register') ? 'is-active' : '' ?>" href="/register">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container page">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash flash-ok"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash flash-err"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="layout">
            <aside class="sidebar">
                <h3 class="side-title">Popular Topics</h3>
                <ul class="side-list">
                    <?php $sideCategories = $categories ?? []; ?>
                    <?php if (empty($sideCategories)): ?>
                        <li><a href="/?category=Web%20Development">Web Development</a></li>
                        <li><a href="/?category=Programming">Programming</a></li>
                        <li><a href="/?category=Data%20Science">Data Science</a></li>
                        <li><a href="/?category=Database">Database</a></li>
                        <li><a href="/?category=AI">AI</a></li>
                    <?php else: ?>
                        <?php foreach ($sideCategories as $item): ?>
                            <li><a href="/?category=<?= esc(urlencode((string) $item)) ?>"><?= esc((string) $item) ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </aside>

            <section class="content">
                <?= $this->renderSection('content') ?>
            </section>
        </div>
    </main>
    <footer class="site-footer">
        <div class="container footer-shell">
            <div class="footer-grid">
                <section class="footer-brand">
                    <strong>lume</strong>
                    <p class="footer-copy">A creative learning studio where tutorials feel alive with video lessons, trends, progress tracking, and creator-led tech learning.</p>
                    <p class="footer-copy" style="margin:0;">Founder: Mahargha Biswas<br>Co-Founder: Timir Purkait</p>
                    <div class="footer-socials">
                        <a href="https://www.linkedin.com/in/mahargha-biswas/" target="_blank" rel="noopener noreferrer">Mahargha LinkedIn</a>
                        <a href="https://github.com/snakspy80" target="_blank" rel="noopener noreferrer">Mahargha GitHub</a>
                        <a href="https://www.linkedin.com/in/timir-purkait-4b247a3b9/" target="_blank" rel="noopener noreferrer">Timir LinkedIn</a>
                    </div>
                </section>

                <section>
                    <h3 class="footer-title">Platform</h3>
                    <ul class="footer-list">
                        <li><a href="/">Home</a></li>
                        <li><a href="/search">Search</a></li>
                        <li><a href="/my-learning">My Learning</a></li>
                        <li><a href="/dashboard">Creator Studio</a></li>
                        <li><a href="/posts/create">Create Course</a></li>
                    </ul>
                </section>

                <section>
                    <h3 class="footer-title">Categories</h3>
                    <ul class="footer-list">
                        <?php if (empty($sideCategories ?? [])): ?>
                            <li><a href="/?category=Web%20Development">Web Development</a></li>
                            <li><a href="/?category=Programming">Programming</a></li>
                            <li><a href="/?category=AI">AI</a></li>
                            <li><a href="/?category=Database">Database</a></li>
                            <li><a href="/?category=Cybersecurity">Cybersecurity</a></li>
                        <?php else: ?>
                            <?php foreach (array_slice(array_values($sideCategories), 0, 5) as $item): ?>
                                <li><a href="/?category=<?= esc(urlencode((string) $item)) ?>"><?= esc((string) $item) ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>

                <section>
                    <h3 class="footer-title">Support</h3>
                    <ul class="footer-list">
                        <li><a href="mailto:lumecoproration@gmail.com">lumecoproration@gmail.com</a></li>
                        <li><span>24/7 Contact Help</span></li>
                        <li><span>Cybersecurity & Backend: Mahargha Biswas</span></li>
                        <li><span>Frontend & Consumer Help: Timir Purkait</span></li>
                    </ul>
                </section>

                <section>
                    <h3 class="footer-title">Profiles</h3>
                    <ul class="footer-list">
                        <li class="footer-person">
                            <strong>Mahargha Biswas</strong>
                            <span class="footer-person-links">
                                <a href="https://www.linkedin.com/in/mahargha-biswas/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                                <a href="https://github.com/snakspy80" target="_blank" rel="noopener noreferrer">GitHub</a>
                            </span>
                        </li>
                        <li class="footer-person">
                            <strong>Timir Purkait</strong>
                            <span class="footer-person-links">
                                <a href="https://www.linkedin.com/in/timir-purkait-4b247a3b9/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                                <a href="https://github.com/tytpfv" target="_blank" rel="noopener noreferrer">GitHub</a>
                            </span>
                        </li>
                    </ul>
                </section>
            </div>

            <div class="footer-bottom">
                © <?= esc(date('Y')) ?> Lume. Built by Mahargha Biswas and Timir Purkait.
            </div>
        </div>
    </footer>
    <script>
    (() => {
        const body = document.body;
        const toggle = document.getElementById('themeToggle');
        const savedTheme = window.localStorage.getItem('lume-theme');
        const applyTheme = (theme) => {
            body.setAttribute('data-theme', theme);
            if (toggle) {
                toggle.textContent = theme === 'light' ? 'Night' : 'Day';
                toggle.setAttribute('aria-label', theme === 'light' ? 'Switch to night mode' : 'Switch to day mode');
            }
        };

        applyTheme(savedTheme === 'light' ? 'light' : 'dark');

        toggle?.addEventListener('click', () => {
            const nextTheme = body.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            window.localStorage.setItem('lume-theme', nextTheme);
            applyTheme(nextTheme);
        });
    })();
    </script>
    <script>
    (() => {
        const inputs = document.querySelectorAll('input[data-typing-placeholder]');
        if (!inputs.length) return;

        inputs.forEach((input) => {
            if (input.value.trim() !== '') return;

            const raw = input.getAttribute('data-typing-placeholder') || '';
            const phrases = raw.split('|').map((item) => item.trim()).filter(Boolean);
            if (!phrases.length) return;

            let phraseIndex = 0;
            let charIndex = 0;
            let deleting = false;
            let paused = false;
            let isFocused = false;

            const tick = () => {
                if (isFocused) {
                    input.placeholder = '';
                    window.setTimeout(tick, 220);
                    return;
                }

                if (input.value.trim() !== '') {
                    input.placeholder = '';
                    window.setTimeout(tick, 500);
                    return;
                }

                const phrase = phrases[phraseIndex] || '';

                if (!deleting) {
                    charIndex += 1;
                    input.placeholder = phrase.slice(0, charIndex);

                    if (charIndex >= phrase.length) {
                        deleting = true;
                        paused = true;
                        window.setTimeout(() => {
                            paused = false;
                            tick();
                        }, 1200);
                        return;
                    }
                } else {
                    charIndex -= 1;
                    input.placeholder = phrase.slice(0, Math.max(charIndex, 0));

                    if (charIndex <= 0) {
                        deleting = false;
                        phraseIndex = (phraseIndex + 1) % phrases.length;
                    }
                }

                const delay = paused ? 1200 : deleting ? 45 : 85;
                window.setTimeout(tick, delay);
            };

            input.addEventListener('focus', () => {
                isFocused = true;
                input.placeholder = '';
            });

            input.addEventListener('click', () => {
                isFocused = true;
                input.placeholder = '';
            });

            input.addEventListener('blur', () => {
                isFocused = false;
                if (input.value.trim() === '') {
                    charIndex = 0;
                    deleting = false;
                }
            });

            tick();
        });
    })();
    </script>
</body>
</html>
