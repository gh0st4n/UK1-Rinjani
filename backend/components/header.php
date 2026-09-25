<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemenhaj Panel - Sistem Informasi Haji & Umroh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="../frontend/template/assets/img/traveloka.png" rel="icon">

    <style>
        :root {
            --bg-sidebar: #f4efe6;
            --bg-active: #e2d7c3;
            --accent-gold: #b38e46;
            --text-dark: #2c2825;
            --text-muted: #78716c;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #eae5dc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            color: var(--text-dark);
        }

        /* Container Pembungkus Vertikal Utama */
        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* Pembungkus Tengah (Sidebar + Main Content) */
        .main-content-wrapper {
            display: flex;
            flex: 1 0 auto;
            width: 100%;
        }

        /* Sidebar Flex */
        .sidebar {
            width: 260px;
            flex-shrink: 0;
            background-color: var(--bg-sidebar);
            border-right: 1px solid #e0d6c5;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }

        /* Wrapper Content */
        .main-wrapper {
            flex: 1;
            min-width: 0;
            background-color: #f7f4ee;
            display: flex;
            flex-direction: column;
        }

        .content-body {
            padding: 24px 40px 30px 30px;
            width: 100%;
            box-sizing: border-box;
            flex: 1;
        }

        /* Footer Di Luar Sidebar (Lebar 100% Menimpa Bawah Sidebar) */
        .main-footer {
            background-color: #eae5dc;
            color: var(--text-muted);
            font-size: 13px;
            padding: 15px 0;
            text-align: center;
            border-top: 1px solid #e0d6c5;
            width: 100%;
            flex-shrink: 0;
            z-index: 1060;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(44, 40, 37, 0.4);
            z-index: 1040;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -260px;
                height: 100vh;
                transition: all 0.3s ease-in-out;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-wrapper {
                width: 100% !important;
            }

            .content-body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="app-container">
        <div class="main-content-wrapper">