<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <link rel="shortcut icon" href="favicon.png" />
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
        <title>EZSell</title>
        <link rel="stylesheet" type="text/css" href="/css/fonts.css" />
        <link rel="stylesheet" type="text/css" href="/css/app.css" />
        <link rel="stylesheet" type="text/css" href="/css/notify.css" />
        <link rel="stylesheet" type="text/css" href="/css/modal.css" />
        <script src="/js/jquery-3.7.1.min.js" type="text/javascript"></script>
        <script src="/js/notify.js" type="text/javascript"></script>
        <script src="/js/app.js" type="text/javascript"></script>
    </head>
    <body>
        <!-- Modals -->
        @include('modal.pay')
        <div class="header">
            <div class="container">
                <div class="header-content">
                    <a href="/" class="logo">
                        <img src="/images/logo.png" alt="logo" />
                    </a>
                    <div class="menu">
                        <a href="/help" class="{{ Request::is('help') ? 'item active' : 'item' }}">Помощь</a>
                        <a href="/rules" class="{{ Request::is('rules') ? 'item active' : 'item' }}">Правила</a>
                        <a href="/about" class="{{ Request::is('about') ? 'item active' : 'item' }}">О нас</a>
                        @auth
                            <a href="/market" class="{{ Request::is('market') ? 'item active' : 'item' }}">Маркет</a>
                        @endauth
                    </div>
                    <div class="right">
                        @guest
                        <a href="/login/steam" class="login-button">Войти через Steam</a>
                        @endguest
                        @auth
                            <div class="profile">
                                <div class="balance" data-modal="pay">{{ $u->balance }}р</div>
                                <div class="avatar">
                                    <img src="{{ $u->avatar }}" alt="{{ $u->login }}" />
                                </div>
                                <a href="/logout" class="logout">Выйти</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        <div class="content-container">
            <div class="container">
                <div class="content">
                    @yield('content')
                </div>
            </div>
            <div class="footer">
                <div class="systems">
                    <img src="/images/footer-payments.png" alt="payments" />
                </div>
                <div class="info">
                    <div class="container">
                        <div class="footer-content">
                            <div class="contacts">
                                <span class="title">Свяжитесь с нами</span>
                                <img src="/images/footer-contacts.png" alt="contacts" />
                            </div>
                            <div class="address">
                                <span class="title">Адрес</span>
                                <p>г. Москва, ул. Верхняя Масловка, д. 15</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(session('error'))
            <script>
                $.notify("{{ session('error') }}", {type: "danger"});
            </script>
        @elseif(session('success'))
            <script>
                $.notify("{{ session('success') }}", {type: "success"});
            </script>
        @endif
    </body>
</html>
