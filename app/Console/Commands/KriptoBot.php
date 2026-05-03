<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\KriptoFiyatGuncellendi;
use Illuminate\Support\Facades\Http;

class KriptoBot extends Command
{
    // Terminalden botu çalıştırmak için kullanacağımız komut ismi
    protected $signature = 'kripto:bot';

    // Botun ne işe yaradığını anlatan açıklama
    protected $description = 'Arka planda Binance fiyatlarını çeker ve WebSockets tüneline fırlatır.';

    public function handle()
    {
     $this->info("Kripto Bot çalıştırıldı! Fiyatlar WebSocket tüneline pompalanıyor...");
    $this->info("Durdurmak için klavyeden CTRL + C tuşlarına basabilirsiniz.");

        // ADA, DOGE ve LINK'in sonuna USDT eklendi
     $coins = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'AVAXUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'LINKUSDT'];

        // Sonsuz döngü: Bot çalıştığı sürece hiç durmadan devam edecek
        while (true) {
        foreach ($coins as $coin) {
         try {
             // URL "ticker/24hr" olarak değiştirildi
           $response = Http::get("https://api.binance.com/api/v3/ticker/24hr?symbol={$coin}");
                    
        if ($response->successful()) {
         // ticker/24hr adresinde güncel fiyat lastPrice adıyla gelir
             $fiyat = round($response->json('lastPrice'), 4);
                        
         // İsimdeki usdt kısmını siliyoruz ki ekranda sadece BTC, ETH yazsın
         $temizCoin = str_replace('USDT', '', $coin); 
                        
             // Binance'den priceChangePercent'i alıyoruz GERÇEK YÜZDE
                 $yuzde = round($response->json('priceChangePercent'), 2);
                        
         // Oluşturduğumuz Event'in içine coini, fiyatı ve GERÇEK YÜZDEYİ koyup tünele fırlatıyoruz
         event(new KriptoFiyatGuncellendi($temizCoin, $fiyat, $yuzde));
                        
                    }
                } catch (\Exception $e) {
                  $this->error("Bir hata oluştu, ancak bot çalışmaya devam ediyor.");
                }
            }
            
            // Binance sunucularını yorup ban yememek için 2 saniye dinlen
            sleep(2);
        }
    }
}