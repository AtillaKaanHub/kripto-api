<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kripto Canlı Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Canlı yanıp sönen yeşil nokta efekti */
        .live-dot {
            height: 10px; width: 10px; background-color: #22c55e;
            border-radius: 50%; display: inline-block;
            animation: blink 1.5s infinite;
        }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold text-blue-400">Kripto Canlı Panel</h1>
                <p class="text-gray-400">Veriler otomatik olarak güncellenir <span class="live-dot ml-1"></span></p>
            </div>
            
            <div class="flex gap-2">
                <input type="text" id="coinInput" placeholder="BTC, ETH..." class="bg-gray-800 border border-gray-700 rounded px-4 py-2 uppercase outline-none focus:border-blue-500 text-white">
                <button onclick="fiyatGetir()" class="bg-blue-600 px-6 py-2 rounded hover:bg-blue-700 transition">Hızlı Bak</button>
            </div>
        </div>

        <div id="dashboardGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            </div>

        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
            <h3 id="gecmisBaslik" class="text-xl font-semibold mb-4 text-gray-300 border-b border-gray-700 pb-2 text-center">İşlem Geçmişi</h3>
            <ul id="gecmisListesi" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <li class="text-gray-500 italic col-span-2 text-center">İncelemek istediğiniz coini aratın veya kartına tıklayın.</li>
            </ul>
        </div>
    </div>

    <script>
        // Butona basıldığında çalışacak ana fonksiyon
        async function fiyatGetir() {
            const coin = document.getElementById('coinInput').value;
            const sonucAlani = document.getElementById('sonucAlani');
           const coinIsmi = document.getElementById('coinIsmi');
            const coinFiyati = document.getElementById('coinFiyati');
            const hataMesaji = document.getElementById('hataMesaji');
           const gecmisListesi = document.getElementById('gecmisListesi');

            // Eğer kutu boşsa uyarı ver
            if(!coin) {
                alert("Lütfen bir coin ismi girin!");
                return;
            }

            // Arayüzü sıfırla ve görünür yap
            sonucAlani.classList.remove('hidden');
            coinIsmi.innerText = "Yükleniyor...";
            coinFiyati.innerText = "";
            hataMesaji.innerText = "";

            try {
                //  Kendi API'mize FİYAT İÇİN İstek Atıyoruz (Sayfa yenilenmez!)
                const fiyatYanit = await fetch(`/api/fiyat/${coin}`);
                const fiyatVerisi = await fiyatYanit.json();

                if(fiyatVerisi.status === 'success') {
                    coinIsmi.innerText = fiyatVerisi.coin;
                    coinFiyati.innerText = "$" + fiyatVerisi.current_price;
                } else {
                    coinIsmi.innerText = "";
                    hataMesaji.innerText = fiyatVerisi.message; // 404 hatasını ekrana bas
                }

                // Kendi API'mize GEÇMİŞ İÇİN İstek Atıyoruz
                const gecmisYanit = await fetch(`/api/gecmis/${coin}`);
                const gecmisVerisi = await gecmisYanit.json();

                gecmisListesi.innerHTML = ""; // Listeyi temizle

                if(gecmisVerisi.status === 'success') {
                    // Gelen geçmiş verilerini döngüyle listeye ekle
                    gecmisVerisi.data.forEach(item => {
                        // Tarihi biraz daha okunabilir formata çevirelim
                        let tarih = new Date(item.created_at).toLocaleTimeString('tr-TR');
                        gecmisListesi.innerHTML += `<li class="flex justify-between border-b border-gray-100 py-1">
                            <span>${item.coin}</span>
                           <span class="font-semibold text-gray-800">$${item.price}</span>
                            <span class="text-xs text-gray-400">${tarih}</span>
                        </li>`;
                    });
                } else {
                    gecmisListesi.innerHTML = `<li class="text-gray-400 italic">Geçmiş kayıt bulunamadı.</li>`;
                }

            } catch (error) {
                coinIsmi.innerText = "";
                hataMesaji.innerText = "Sunucu ile bağlantı kurulamadı.";
            }
        }
    </script>
</body>
</html>