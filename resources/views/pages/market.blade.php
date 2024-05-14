@extends('layout')
@section('content')
    <div class="page-title">Маркет</div>
    <div class="inventory">
        @if(!empty($market))
            @foreach($market as $item)
                <div class="item" style="--rarity-color: {{ $item['rarity']['color'] }};">
                    <div class="image">
                        <img src="{{ $item['image'] }}" alt="image" />
                    </div>
                    <div class="item-content">
                        <div class="info">
                            <span class="title">{{ $item['name'] }}</span>
                            <span class="rarity">{{ $item['rarity']['name'] }}</span>
                        </div>
                        <a href="https://steamcommunity.com/profiles/{{ $item['owner']['steam_id'] }}" target="_blank" class="owner">
                            <img src="{{ $item['owner']['avatar'] }}" alt="{{ $item['owner']['login'] }}" class="avatar" />
                            <span class="name">{{ $item['owner']['login'] }}</span>
                        </a>
                        <form method="POST" action="{{ $u->id == $item['owner']['id'] ? '/api/market/cancel' : '/api/p2p/create' }}" class="form-field">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}" />
                            <div class="field-input">
                                <input type="number" readonly value="{{ $item['price'] }}" />
                                <span class="input-after">руб.</span>
                            </div>
                            @if($u->id != $item['owner']['id'])
                            <button type="submit" class="field-btn color-green">Купить</button>
                            @else
                            <button type="submit" class="field-btn color-red">Отменить</button>
                            @endif
                        </form>
                    </div>
                </div>
            @endforeach
        @else
            <div class="not-found">Список пуст</div>
        @endif
    </div>
@endsection
