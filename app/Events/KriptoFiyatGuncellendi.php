<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// implements ShouldBroadcast kısmı Laravel e bu olayın WebSockets üzerinden fırlatılacağını söyler.
class KriptoFiyatGuncellendi implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // JavaScript e göndereceğimiz veriler (Public olmak zorundadır)
    public $coin;
    public $fiyat;

    // Olay tetiklendiğinde içine verileri koyduğumuz yer
    public function __construct($coin, $fiyat)
    {
        $this->coin = $coin;
        $this->fiyat = $fiyat;
    }

    // Verinin HANGİ kanaldan fırlatılacağını belirliyoruz
    public function broadcastOn(): array
    {
        // kripto-kanal adında, şifresiz ve herkese açık bir tünel oluşturuyoruz
        return [
            new Channel('kripto-kanal'),
        ];
    }

    // Olayın JavaScript tarafında hangi isimle yakalanacağını belirliyoruz 
    public function broadcastAs(): string
    {
        return 'fiyat.guncellendi';
    }
}
