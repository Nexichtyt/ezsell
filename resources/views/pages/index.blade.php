@extends('layout')
@section('content')
    @guest
        <div class="main-banner">
            <div class="title">Продать <span>Виртуальные</span> вещи за Реальные Деньги Мгновенно!</div>
            <div class="guide">Для начала войдите в профиль</div>
            <a class="banner-button" href="/login/steam">Войти через Steam</a>
        </div>
    @endguest
    @auth
        <form action="/api/user/set-trade" method="POST" class="trade-link">
            @csrf
            <div class="field">
                <input type="text" name="trade" value="{{ $u->tradelink }}" class="trade-input" placeholder="Введите трейд ссылку" />
                <button type="submit" class="button-save {{ empty($u->tradelink) ? 'color-blue' : 'color-green' }}">{{ empty($u->tradelink) ? 'Сохранить' : 'Изменить' }}</button>
            </div>
            <a href="https://steamcommunity.com/profiles/{{ $u->steam_id }}/tradeoffers/privacy" class="small-info-trade" target="_blank">Как узнать свою трейд ссылку?</a>
        </form>
        <div class="page-title">Ваши текущие P2P сделки</div>
        <div class="inventory">
            @if(count($active_p2p) != 0)
                @foreach($active_p2p as $item)
                    <div class="item" style="--rarity-color: {{ $item->market['info']['rarity']['color'] }};">
                        <div class="image">
                            <img src="{{ $item->market['info']['image'] }}" alt="image" />
                        </div>
                        <div class="item-content">
                            <div class="info">
                                <span class="title">{{ $item->market['info']['name'] }}</span>
                                <span class="rarity">{{ $item->market['info']['rarity']['name'] }}</span>
                            </div>
                            @if($u->id == $item->market->owner->id)
                                <a href="https://steamcommunity.com/profiles/{{ $item->buyer->steam_id }}" target="_blank" class="owner">
                                    <img src="{{ $item->buyer->avatar }}" alt="{{ $item->buyer->login }}" class="avatar" />
                                    <span class="name">{{ $item->buyer->login }}</span>
                                </a>
                            @else
                                <a href="https://steamcommunity.com/profiles/{{ $item->market->owner->steam_id }}" target="_blank" class="owner">
                                    <img src="{{ $item->market->owner->avatar }}" alt="{{ $item->market->owner->login }}" class="avatar" />
                                    <span class="name">{{ $item->market->owner->login }}</span>
                                </a>
                            @endif
                            <div class="form-field">
                                <div class="field-input">
                                    <input type="number" readonly value="{{ $item->market['price'] }}" />
                                    <span class="input-after">руб.</span>
                                </div>
                                <a href="/p2p/{{ $item->uuid }}" class="field-btn">Перейти</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="not-found">Список пуст</div>
            @endif
        </div>
        <div class="page-title">Мой инвентарь</div>
        <div class="inventory">
            @if(!empty($inventory))
                @foreach($inventory as $item)
                    <div class="item" style="--rarity-color: {{ $item['rarity']['color'] }};">
                        <div class="image">
                            <img src="{{ $item['image'] }}" alt="image" />
                        </div>
                        <div class="item-content">
                            <div class="info">
                                <span class="title">{{ $item['name'] }}</span>
                                <span class="rarity">{{ $item['rarity']['name'] }}</span>
                            </div>
                            <form method="POST" action="/api/market/create" class="form-field">
                                @csrf
                                <input type="hidden" name="assetid" value="{{ $item['assetid'] }}" />
                                <div class="field-input">
                                    <input type="number" required step="0.01" pattern="^\d*(\.\d{0,2})?$" name="amount" min="0.01" max="{{ floatval($item['price']) * 2 }}" placeholder="Введите стоимость предмета" value="{{ $item['price'] }}" />
                                    <span class="input-after">руб.</span>
                                </div>
                                @if($item['available_for_p2p'])
                                <button type="submit" class="field-btn">Выставить</button>
                                @else
                                <button type="submit" class="field-btn" disabled>Недоступно</button>
                                @endif
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="not-found">Ваш инвентарь CS2 пуст</div>
            @endif
        </div>
    @endauth
@endsection
