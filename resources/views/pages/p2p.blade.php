@extends('layout')
@section('content')
    <div class="page-title">P2P #{{ $p2p->id }}</div>
    <div class="p2p">
        <div class="info">
            <div class="owner">
                <div class="avatar">
                    <img src="{{ $p2p->market->owner->avatar }}" alt="avatar" />
                </div>
                <span class="name">{{ $p2p->market->owner->login }}</span>
            </div>
            <svg width="125" height="63" viewBox="0 0 1707 854" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_990_2103)">
                    <path d="M1237.33 62.1337C1223.2 64.9337 1207.6 72.8004 1198.27 82.1337C1185.07 95.067 1178.53 108.267 1175.87 126.934C1174.53 136.534 1174.67 139.867 1176.53 148.8C1179.47 163.2 1186.53 177.067 1195.73 186.4C1200.13 190.8 1247.87 226 1309.2 270L1415.07 346L737.198 346.667C102.132 347.334 58.9317 347.467 52.9317 349.6C33.465 356.534 17.5984 369.867 8.93169 386.267C-6.26831 415.2 -1.33498 449.2 21.3317 472.134C30.665 481.6 38.1317 486.534 50.265 491.067L57.9984 494L735.065 494.667L1412.13 495.334L1311.87 577.334C1256.67 622.4 1208.4 662.4 1204.67 666.267C1200.8 670.134 1195.6 677.467 1192.8 682.934C1177.87 712.8 1183.07 745.6 1206.8 769.467C1220.67 783.467 1237.87 790.667 1257.73 790.667C1270.93 790.667 1282.53 787.734 1293.73 781.6C1303.07 776.4 1673.33 474.534 1686 461.734C1707.33 440.134 1712.53 405.467 1698.4 377.334C1692.27 364.8 1682.53 355.6 1655.2 335.867C1510.4 231.6 1295.07 77.2004 1288.93 73.2004C1277.87 66.0004 1266.8 62.5337 1252.67 62.0004C1246.13 61.7337 1239.2 61.867 1237.33 62.1337Z" fill="#56565650"/>
                </g>
                <defs>
                    <clipPath id="clip0_990_2103">
                        <rect width="1706.67" height="853.333" fill="white"/>
                    </clipPath>
                </defs>
            </svg>
            <div class="weapon" style="--rarity-color: {{ $p2p->market->info['rarity']['color'] }};">
                <div class="image">
                    <img src="{{ $p2p->market->info['image'] }}" alt="avatar" />
                </div>
                <span class="name">{{ $p2p->market->info['name'] }}</span>
            </div>
        </div>
        @if($p2p->market->owner->id == $u->id && $p2p->owner_send_trade == 0)
        <div class="flex-center">
            <a href="{{ $p2p->buyer->tradelink }}" class="btn-send-trade" target="_blank">Отправить "{{ $p2p->market->info['name'] }}"</a>
            <span class="description">После отправки предмета нажмите на кнопку "Я отправил предмет"</span>
            <form action="/api/p2p/send-trade" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $p2p->id }}">
                <button type="submit" class="btn-accept-trade">Я отправил предмет</button>
            </form>
            <small class="r-t">Рекомендуем нажимать кнопку "Я отправил предмет" перед отправкой предмета</small>
        </div>
        @endif
        @if($p2p->buyer->id == $u->id && $p2p->owner_send_trade == 0)
        <div class="flex-center">
            <span class="description">"{{ $p2p->market->owner->login }}" отправляет предмет...</span>
            <small>Через 5 секунд обновится страница для обновления статуса обмена</small>
            <script type="text/javascript">
                setTimeout(() => location.reload(), 5000);
            </script>
        </div>
        @endif
        @if($p2p->buyer->id == $u->id && $p2p->owner_send_trade == 1)
            <div class="flex-center">
                <span class="description">После того как вы приняли предмет "{{ $p2p->market->info['name'] }}" в Steam, подтвердите это на сайте нажав на кнопку "Я получил предмет" тем самым завершите P2P сделку.</span>
                <form action="/api/p2p/accept-trade" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $p2p->id }}">
                    <button type="submit" class="btn-accept-trade">Я получил предмет</button>
                </form>
            </div>
        @endif
        @if($p2p->owner_send_trade == 0)
            <div class="flex-center">
                <form action="/api/p2p/cancel" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $p2p->id }}">
                    <button type="submit" class="btn-cancel">Отменить обмен</button>
                </form>
            </div>
        @endif
    </div>
@endsection
