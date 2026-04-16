<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Kripto Fiyat Takip</title>
 <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">Anlık Kripto Takip</h1>
        
        <div class="flex gap-2 mb-6">
     <input type="text" id="coinInput" placeholder="Örn: ETH, BTC, SOL" class="w-full border border-gray-300 rounded px-4 py-2 uppercase outline-none focus:border-blue-500">
            <button onclick="fiyatGetir()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Ara</button>
        </div>

        <div id="sonucAlani" class="hidden text-center mb-6 p-4 border rounded bg-gray-50">
         <h2 id="coinIsmi" class="text-xl font-bold text-gray-700"></h2>
            <p id="coinFiyati" class="text-3xl font-black text-green-600 mt-2"></p>
             <p id="hataMesaji" class="text-red-500 font-semibold"></p>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2 border-b pb-1">Son Fiyat Sorgulamaları</h3>
            <ul id="gecmisListesi" class="text-sm text-gray-600 space-y-1">
              <li class="text-gray-400 italic">Henüz bir arama yapılmadı...</li>
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