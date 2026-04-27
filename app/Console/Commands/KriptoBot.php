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

        // Takip edeceğimiz coinlerin listesi 
        $coins = ['BTCUSDT', 'ETHUSDT', 'SOLUSDT', 'AVAXUSDT'];

        // Sonsuz döngü: Bot çalıştığı sürece hiç durmadan devam edecek
        while (true) {
            foreach ($coins as $coin) {
                try {
                    // Binance API sinden o anki fiyatı çekiyoruz
                    $response = Http::get("https://api.binance.com/api/v3/ticker/price?symbol={$coin}");
                    
                    if ($response->successful()) {
                        // Fiyatı küsuratlarıyla alıyoruz
                        $fiyat = round($response->json('price'), 4);
                        
                        // İsimdeki USDT kısmını siliyoruz ki ekranda sadece BTC, ETH yazsın
                        $temizCoin = str_replace('USDT', '', $coin); 
                        
                       
                        // Oluşturduğumuz Event'in içine coini ve fiyatı koyup tünele fırlatıyoruz
                        event(new KriptoFiyatGuncellendi($temizCoin, $fiyat));
                        
                        // İstersen fırlattığı verileri terminalde görmek için alttaki satırı açabilirsin
                        
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