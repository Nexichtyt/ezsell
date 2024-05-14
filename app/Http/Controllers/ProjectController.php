<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\P2P;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return view(
            'pages.index',
            Auth::user() ? [
                'inventory' => $this->getInventory($this->user->steam_id),
                'active_p2p' => P2P::with([
                    'market' => function ($query) {
                        $query->with('owner');
                    }
                ])->where(function($query) {
                    $query->where('buyer_id', $this->user->id)->orWhere('owner_id', $this->user->id);
                })->where('status', 0)->get()
            ] : []
        );
    }

    public function paymentCheck(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return ['success' => false, 'msg' => 'ID платежа для проверки не найден'];
        $payment = Payments::where(['id' => $id, 'checked' => 0, 'user_id' => $this->user->id])->first();
        if(!$payment) return ['success' => false, 'msg' => 'Платеж для проверки не найден'];
        if($payment->status == 0) return ['success' => false, 'status' => 'wait'];
        if($payment->status == 2) return ['success' => false, 'status' => 'cancel', 'msg' => 'Платеж на сумму ' . $payment->amount . ' руб. отменён'];
        $payment->update(['checked' => 1]);
        $this->user->balance += $payment->amount;
        $this->user->save();
        return ['success' => true, 'balance' => $this->user->balance, 'msg' => 'Платеж на сумму ' . $payment->amount . ' руб. зачислен'];
    }

    public function paymentCreate(Request $r)
    {
        $amount = intval($r->amount);
        if(!$amount) return ['success' => false, 'msg' => 'Введите сумму пополнения'];
        if($amount < 100 || $amount > 15000) return ['success' => false, 'msg' => 'Сумма пополнения от 100 до 15000 рублей'];
        $db = Payments::create([
            'user_id' => $this->user->id,
            'amount' => $amount,
            'card' => $this->generateRandomCardNumber()
        ]);
        return ['success' => true, 'pay' => ['id' => $db['id'], 'amount' => $amount, 'card' => preg_replace("/(\d{4})(?=\d)/", "$1 ", $db['card'])]];
    }

    public function about()
    {
        return view('pages.about');
    }

    public function rules()
    {
        return view('pages.rules');
    }

    public function help()
    {
        return view('pages.help');
    }

    public function setTrade(Request $r)
    {
        $trade = $r->trade;
        if(!$trade) return redirect('/')->with('error', 'Введите трейд ссылку');
        if(!$this->isValidTradeLink($trade)) return redirect('/')->with('error', 'Введите верную трейд ссылку');
        if($trade == $this->user->tradelink) return redirect('/')->with('error', 'Данная трейд ссылка уже установлена');
        $first_update = empty($this->user->tradelink);
        $this->user->update(['tradelink' => $trade]);
        return redirect('/')->with('success', 'Трейд ссылка успешно ' . ($first_update ? 'сохранена' : 'обновлена'));
    }

    public function p2p($uuid)
    {
        $p2p = P2P::with([
            'market' => function ($query) {
                $query->with(['owner']);
            }
        ])->where(['buyer_id' => $this->user->id, 'uuid' => $uuid])->orWhere(['owner_id' => $this->user->id, 'uuid' => $uuid])->first();
        if(!$p2p) return redirect('/');
        if($p2p->status == 1 || $p2p->status == 2) return redirect('/')->with('success', 'P2P обмен на предмет "' . $p2p->market->info['name'] . '" ' . ($p2p->status == 1 ? 'успешно прошел' : 'отменён'));
        return view(
            'pages.p2p',
            [
                'p2p' => $p2p
            ]
        );
    }

    public function p2pAcceptTrade(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return redirect('/')->with('error', 'Ошибка валидации');
        $p2p = P2P::with([
            'buyer', 'market' => function ($query) {
                $query->with(['owner']);
            }
        ])->where(['id' => $id, 'status' => 0, 'owner_send_trade' => 1, 'buyer_accept_trade' => 0])->first();
        if(!$p2p || $p2p->buyer->id != $this->user->id) return redirect('/')->with('error', 'P2P обмен не найден');
        $p2p->update(['buyer_accept_trade' => 1, 'status' => 1]);
        $totalAmount = $p2p->market->price - floatval($p2p->market->price * .05);
        $p2p->market->owner->balance += $totalAmount;
        $p2p->market->owner->save();
        return redirect('/')->with('success', 'P2P обмен успешно закрыт. Вы получили предмет "' . $p2p->market->info['name'] . '"');
    }

    public function p2pSendTrade(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return redirect('/')->with('error', 'Ошибка валидации');
        $p2p = P2P::with([
            'market' => function ($query) {
                $query->with(['owner']);
            }
        ])->where(['id' => $id, 'status' => 0, 'owner_send_trade' => 0])->first();
        if(!$p2p || $p2p->market->owner->id != $this->user->id) return redirect('/')->with('error', 'P2P обмен не найден');
        $p2p->update(['owner_send_trade' => 1]);
        return redirect('/p2p/' . $p2p->uuid)->with('success', 'Статус P2P обмена обновлен');
    }

    public function p2pCancel(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return redirect('/')->with('error', 'Ошибка валидации');
        $p2p = P2P::with([
            'market' => function ($query) {
                $query->with(['owner']);
            }
        ])->where(['id' => $id, 'status' => 0, 'owner_send_trade' => 0])->first();
        if(!$p2p || $p2p->market->owner->id != $this->user->id) return redirect('/market')->with('error', 'P2P обмен не найден');
        $p2p->update(['status' => 2]);
        $p2p->market->update(['status' => 2]);
        return redirect('/')->with('success', 'P2P обмен отменен');
    }

    public function p2pCreate(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return redirect('/market')->with('error', 'Ошибка валидации');
        $market = Market::where(['id' => $id, 'status' => 0])->first();
        if(!$market) return redirect('/market')->with('error', 'Выбранный вами предмет не найден или данный предмет ваш');
        if($market->owner_id == $this->user->id) return redirect('/market')->with('error', 'Вы не можете купить свой же предмет');
        if(empty($this->user->tradelink)) return redirect('/market')->with('error', 'Для создания P2P обмена вставьте свою трейд ссылку');
        if($this->user->balance < $market->price) return redirect('/market')->with('error', 'Недостаточно средств');
        $this->user->balance -= $market->price;
        $this->user->save();
        $market->update(['status' => 1]);
        $db = P2P::create([
            'uuid' => Str::uuid(),
            'buyer_id' => $this->user->id,
            'owner_id' => $market->owner_id,
            'market_id' => $market->id,
        ]);
        return redirect('/p2p/' . $db['uuid']);
    }

    public function market()
    {
        $db = Market::with([
            'owner' => function ($query) {
                $query->select('id', 'steam_id', 'avatar', 'login');
            }
        ])->where(['status' => 0])->orderBy('id', 'desc')->get();
        $items = [];
        foreach ($db as $item) {
            $items[] = array_merge(
                $item->info,
                [
                    'id' => $item->id,
                    'owner' => $item->owner,
                    'price' => $item->price
                ]
            );
        }
        return view(
            'pages.market',
            [
                'market' => $items
            ]
        );
    }

    public function marketCancel(Request $r)
    {
        $id = intval($r->id);
        if(!$id) return redirect('/market')->with('error', 'Ошибка валидации');
        $market = Market::where(['id' => $id, 'owner_id' => $this->user->id, 'status' => 0])->first();
        if(!$market) return redirect('/market')->with('error', 'Выбранный вами предмет не найден');
        $market->update(['status' => 2]);
        return redirect('/market')->with('success', 'Предмет "' . $market->info['name'] . '" удален из маркета');
    }

    public function marketCreate(Request $r)
    {
        $assetid = intval($r->assetid);
        $amount = floatval($r->amount);
        if(!$assetid || !$amount) return redirect('/')->with('error', 'Ошибка валидации');
        $inventory = $this->getInventory($this->user->steam_id);
        if(array_search(strval($assetid), array_column($inventory, 'assetid')) === false) return redirect('/')->with('error', 'Предмет не найден');
        $item = $inventory[array_search(strval($assetid), array_column($inventory, 'assetid'))];
        if(empty($item)) return redirect('/')->with('error', 'Предмет не найден');
        if(!$item['available_for_p2p']) return redirect('/')->with('error', 'Предмет находится в трейд бане');
        $marketAvailable = Market::where(['owner_id' => $this->user->id, 'assetid' => $assetid])->whereIn('status', [0, 1])->exists();
        if($marketAvailable) return redirect('/')->with('error', 'Данный предмет уже был опубликован для продажи');
        if($amount < 0) return redirect('/')->with('error', 'Минимальная сумма для создания продажи 0.01 руб.');
        $maxAmountMarket = doubleval($item['price'] * 2);
        if($amount > $maxAmountMarket) return redirect('/')->with('error', 'Максимальная сумма для создания продажи ' . $maxAmountMarket . ' руб.');
        if(empty($this->user->tradelink)) return redirect('/market')->with('error', 'Для выставления предмета на маркете вставьте свою трейд ссылку');
        Market::create([
            'assetid' => $assetid,
            'owner_id' => $this->user->id,
            'price' => $amount,
            'info' => $item
        ]);
        return redirect('/market')->with('success', 'Предмет ' . $item['name'] . ' успешно выставлен на продажу за ' . $amount . ' руб.');
    }

    private function getInventory($steam_id)
    {
        $request = curl_init("https://steamcommunity.com/inventory/" . $steam_id . "/730/2?language=ru");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7'
        ]);
        $response = curl_exec($request);
        curl_close($request);
        if(!$response) return [];
        $json = json_decode($response, true);
        if(empty($json) || empty($json['descriptions']) || empty($json['assets']) || !$json['success']) return [];
        $cs2Prices = $this->getCS2ItemsPrice();
        $assets = $json['assets'];
        $items = [];
        foreach ($json['descriptions'] as $item) {
            $itemAssetId = $this->getCS2ItemAssetId($assets, $item['classid'], $item['instanceid']);
            $marketExists = Market::where(['owner_id' => $this->user->id, 'assetid' => $itemAssetId])->whereIn('status', [0, 1])->exists();
            if($marketExists) continue;
            $keyCS2Prices = array_search($item['market_hash_name'], array_column($cs2Prices, "market_hash_name"));
            $getCS2Price = $cs2Prices[$keyCS2Prices];
            $keyRarity = array_search("Rarity", array_column($item['tags'], "category"));
            $getRarity = $item['tags'][$keyRarity];
            $rarity = !empty($getRarity) ? [
                'name' => mb_convert_case($getRarity['localized_tag_name'], MB_CASE_TITLE, "UTF-8"),
                'color' => '#' . $getRarity['color']
            ] : ['name' => 'Стандартное', 'color' => '#ded6cc'];
            if(!empty($getRarity) && $getRarity['localized_tag_name'] == 'Стандартное') continue;
            $items[] = [
                'market_hash_name' => $item['market_hash_name'],
                'name' => $item['name'],
                'type' => $item['type'],
                'classid' => $item['classid'],
                'instanceid' => $item['instanceid'],
                'assetid' => $itemAssetId,
                'image' => 'https://steamcommunity-a.akamaihd.net/economy/image/' . $item['icon_url'],
                'price' => !empty($getCS2Price) ? $getCS2Price['price'] : 0,
                'rarity' => $rarity,
                'available_for_p2p' => $item['tradable']
            ];
        }
        return $items;
    }

    private function getCS2ItemAssetId($assets, $classid, $instanceid)
    {
        $assetid = null;
        foreach ($assets as $asset) {
            if($asset['classid'] == $classid && $asset['instanceid'] == $instanceid) {
                $assetid = $asset['assetid'];
                break;
            }
        }
        return $assetid;
    }

    private function getCS2ItemsPrice()
    {
        return json_decode(file_get_contents(app_path('Json/cs2prices.json')), true);
    }

    private function isValidTradeLink($tradeLink)
    {
        $pattern = '/^https?:\/\/steamcommunity\.com\/tradeoffer\/new\/\?partner=[0-9]+&token=[a-zA-Z0-9_-]+$/';
        return preg_match($pattern, $tradeLink);
    }

    private function generateRandomCardNumber()
    {
        $number = '';
        for ($i = 0; $i < 16; $i++) {
            $number .= mt_rand(0, 9);
        }
        return $number;
    }
}
