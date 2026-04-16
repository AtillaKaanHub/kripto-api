<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\CryptoHistory; 

class CryptoController extends Controller
{
      // Rota'daki {coin} parametresi doğrudan bu fonksiyona değişken olarak gelir
    public function getPrice($coin)
    {
        // Gelen coin adını tamamen büyük harfe çevirip, Binance nin istediği formata getiriyoruz
        $symbol = strtoupper($coin) . 'USDT';

        //  Dinamik sembolümüz ile Binance API sine istek atıyoruz
        $response = Http::get("https://api.binance.com/api/v3/ticker/price?symbol={$symbol}");

        // kullanıcı saçma bir coin yazarsa Sistemimizin çökmemesi için bu hatayı yakalayıp kendi JSON hata mesajımızı dönüyoruz.
        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => "{$symbol} işlem çifti bulunamadı. Lütfen geçerli bir coin girin."
            ], 404); // 404 Not Found (Bulunamadı) HTTP kodu
        }

        $data = $response->json();
        
        // Altcoinlerin fiyatı çok düşük olabileceği için virgülden sonra 4 basamak alıyoruz
        $usdPrice = round($data['price'], 4); 

        // controllere verıyı kaydettık
        CryptoHistory::create([
            'coin' => strtoupper($coin),
            'price' => $usdPrice
        ]);

        // Dinamik ve temiz JSON yanıtımızı oluşturuyoruz
        return response()->json([
            'status' => 'success',
            'message' => strtoupper($coin) . ' Fiyatı Başarıyla Çekildi',
            'coin' => strtoupper($coin),
            'currency' => 'USD',
            'current_price' => $usdPrice
        ]);
    }
    
    public function getHistory($coin)
    {
     // kullanıcının gırdıgı adı buyuk hale cevirir   
     $coinName = strtoupper($coin);

     // veritabanında bu verıye ait son 10 kaydı en yeniden en eskiye çekiyoruz
     $history = CryptoHistory::where('coin', $coinName)
     ->orderBy('created_at', 'desc') // Tarihe göre azalan 
        ->take(10) // Sadece son 10 kaydı al
        ->get();
 
        //veritabanında bu coine ait hiç kayıt yoksa 404 hatası döner
       if ($history->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "{$coinName} için henüz kaydedilmiş bir fiyat geçmişi bulunmuyor. Lütfen önce fiyat sorgulaması yapın."
            ], 404);
        }

        //bulunan verileri JSON formatında dışarıya sunar 
        return response()->json([
            'status' => 'success',
            'coin' => $coinName,
            'kayit_sayisi' => $history->count(),
            'data' => $history
        ]);


    }

}
