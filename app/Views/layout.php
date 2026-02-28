<?php $websiteConfig = config('Website'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? $websiteConfig->siteName ?></title>
    
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="https://placehold.co">
    
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="prefetch" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff" as="font" type="font/woff" crossorigin>
    
    <link rel="preload" href="<?= base_url($websiteConfig->homepageLogo) ?>" as="image" type="image/png">
    <link rel="preload" href="<?= base_url($websiteConfig->homepageDecorativeImage) ?>" as="image" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css" rel="stylesheet"></noscript>
    
    <link rel="preload" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css" rel="stylesheet"></noscript>
    
    <link rel="preload" href="<?= base_url('css/responsive-mobile.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?= base_url('css/responsive-mobile.css') ?>" rel="stylesheet"></noscript>
    
    <link rel="preload" href="<?= base_url('css/home.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?= base_url('css/home.css') ?>" rel="stylesheet"></noscript>
    
    <style>
        @font-face {
            font-family: 'bootstrap-icons';
            src: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2') format('woff2'),
                 url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
            ascent-override: 110%;
            descent-override: 30%;
            line-gap-override: 0%;
        }

        @font-face {
            font-family: -apple-system;
            font-display: swap;
        }

        .bi::before,
        [class^="bi-"]::before,
        [class*=" bi-"]::before {
            font-family: 'bootstrap-icons' !important;
            font-display: swap;
        }

        .form-label.required:after {
            content:"*";
            color:red;
            margin-left: 2px;
        }
        .list-group-item:hover {
            background-color: var(--bs-dark);
            color: #fff;
        }
        body.login-page {
            background-image: url('<?= base_url($websiteConfig->loginBackgroundImage) ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        @media (min-width: 992px) { 
            #navbarLogo {
                width: 50px;
                height: 50px;
            }

            #navbarProfileImg {
                width: 40px;
                height: 40px;
            }
        }
        
        @media (min-width: 992px) {
            body::before {
                content: '';
                background-image: url('<?= base_url($websiteConfig->homepageLogo) ?>');
                display: none;
            }
        }
    </style>
    
    <link rel="prefetch" href="<?= base_url($websiteConfig->navbarLogo) ?>" as="image">
    <link rel="prefetch" href="<?= base_url($websiteConfig->homepageLogo) ?>" as="image">
    <link rel="prefetch" href="<?= base_url($websiteConfig->profileImage) ?>" as="image">
</head>
<body class="<?= esc($bodyClass ?? '') ?>">
    <nav class="navbar navbar-expand border-bottom bg-white">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <?php if (session('role') && session('role') !== 'murid'): ?>
                    <button class="btn btn-success text-mobile-lg" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions"><i class="bi bi-list"></i></button>
                <?php endif; ?>
                <div class="d-flex align-items-center ml-3">
                    <img src="<?= base_url($websiteConfig->navbarLogo) ?>" alt="Logo" class="d-inline-block align-text-top me-2 img-mobile-md" id="navbarLogo"/>
                    <a class="navbar-brand text-mobile-md" href="<?= base_url('/') ?>"><?= esc($websiteConfig->siteName) ?></a>
                </div>
            </div>
            <?php if (session('role')): ?>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= base_url($websiteConfig->profileImage) ?>" alt="User" class="d-inline-block align-text-top rounded-circle img-mobile-sm" id="navbarProfileImg"/>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-mobile-md" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= base_url('login') ?>" class="link-underline-light text-mobile-md" id="loginLink">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-mobile-lg" id="offcanvasWithBothOptionsLabel">Selamat datang, <?= session('name') ?></h5>
            <button type="button" class="btn-close btn-close-mobile" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="list-group list-group-flush">
                <li class="list-group-item border-0 text-mobile-md" id="formListItem">
                    <a data-bs-toggle="collapse" href="#formExample" role="button" aria-expanded="false" aria-controls="formExample" style="text-decoration: none; color: inherit;" class="d-flex align-items-center justify-content-between">
                        <span>
                            <i class="bi bi-file-earmark-text"></i>
                            Form
                        </span>
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <div class="collapse" id="formExample">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item text-bg-dark border-0 text-mobile-md">
                                <a data-bs-toggle="collapse" href="#submenuManual" role="button" aria-expanded="false"
                                aria-controls="submenuManual" style="text-decoration: none; color: inherit;"
                                class="d-flex align-items-center justify-content-between">
                                    <span>Peminjaman Manual</span>
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <div class="collapse" id="submenuManual">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item text-bg-dark border-0 text-mobile-md">
                                        <a href="<?= base_url('peminjaman-kelas') ?>"
                                        style="text-decoration: none; color: inherit;">&nbsp;&nbsp;&nbsp;&nbsp;Kelas</a>
                                    </li>
                                    <li class="list-group-item text-bg-dark border-0 text-mobile-md">
                                        <a href="<?= base_url('peminjaman-perpustakaan') ?>"
                                        style="text-decoration: none; color: inherit;">&nbsp;&nbsp;&nbsp;&nbsp;Perpustakaan</a>
                                    </li>
                                </ul>
                            </div>
                            <?php if (session('role') !== 'guru'): ?>
                            <li class="list-group-item text-bg-dark border-0 text-mobile-md">
                                <a href="<?= base_url('automate') ?>" style="text-decoration: none; color: inherit;">Peminjaman Otomatis</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <li class="list-group-item border-0 text-mobile-md">
                    <a href="<?= base_url('user') ?>" style="text-decoration: none; color: inherit;">
                        <i class="bi bi-person"></i>
                        Manajemen User
                    </a>
                </li>
                <?php if (session('role') === 'admin'): ?>
                    <li class="list-group-item border-0 text-mobile-md">
                        <a href="<?= base_url('management-buku') ?>" style="text-decoration: none; color: inherit;">
                            <i class="bi bi-book"></i>
                            Manajemen Buku
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (session('role') === 'admin'): ?>
                    <li class="list-group-item border-0 text-mobile-md">
                        <a href="<?= base_url('management-class') ?>" style="text-decoration: none; color: inherit;">
                            <i class="bi bi-mortarboard"></i>
                            Manajemen Kelas
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="container-xxl">
        <?= $this->renderSection('content') ?>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-config='{"delay": 5000}'>
            <div class="toast-header">
            <img src="<?= base_url($websiteConfig->homepageLogo) ?>" class="rounded me-2" alt="Logo" style="width: 20px;">
            <strong class="me-auto"><?= esc($websiteConfig->siteName) ?></strong>
            <small id="toastTime"><?= date('H:i') ?></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
            Hello, world! This is a toast message.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script defer src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.tooltip.min.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.spline.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.resize.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.pie.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.symbol.js') ?>"></script>
    <script defer src="<?= base_url('js/plugins/flot/jquery.flot.time.js') ?>"></script>
    <script defer src="<?= base_url('js/toast.js') ?>"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var collapse = document.getElementById('formExample');
        var listItem = document.getElementById('formListItem');
        
        if (!collapse || !listItem) return;
        
        var chevronIcon = listItem.querySelector('.bi-chevron-left');

        collapse.addEventListener('show.bs.collapse', function () {
            listItem.classList.add('text-bg-dark', 'border-start', 'border-5', 'border-success');
            if (chevronIcon) {
                chevronIcon.classList.remove('bi-chevron-left');
                chevronIcon.classList.add('bi-chevron-down');
            }
        });
        collapse.addEventListener('hide.bs.collapse', function () {
            listItem.classList.remove('text-bg-dark', 'border-start', 'border-5', 'border-success');
            if (chevronIcon) {
                chevronIcon.classList.remove('bi-chevron-down');
                chevronIcon.classList.add('bi-chevron-left');
            }
        });
    });
    </script>
</body>
</html>