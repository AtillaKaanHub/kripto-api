<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kripto Canlı Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js Kütüphanesini Sayfaya Ekleme -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            
            <div class="mb-6 bg-gray-900 p-4 rounded-lg border border-gray-700">
             <canvas id="fiyatGrafigi" height="80"></canvas>
           </div>
           
            <ul id="gecmisListesi" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <li class="text-gray-500 italic col-span-2 text-center">İncelemek istediğiniz coini aratın veya kartına tıklayın.</li>
            </ul>
        </div>
    </div>

<script>
        // Panelde takip etmek istediğimiz varsayılan coinler
        const takipListesi = ['BTC', 'ETH', 'SOL', 'BNB'];

        // DASHBOARD U GÜNCELLEYEN FONKSİYON
        async function dashboardGuncelle() {
         const grid = document.getElementById('dashboardGrid');
            
         // Her coin için API ye istek atar
         for (const coin of takipListesi) {
          try {
         const response = await fetch(`/api/fiyat/${coin}`);
         const data = await response.json();

             if(data.status === 'success') {
                 // Eğer kart zaten varsa sadece fiyatı güncelle, yoksa yeni kart oluştur
                 let card = document.getElementById(`card-${coin}`);
                  if(!card) {
                   grid.innerHTML += `
                    <div id="card-${coin}" class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-blue-500 transition cursor-pointer" onclick="gecmisGetir('${coin}')">
                  <div class="flex justify-between items-center mb-4">
                   <span class="text-2xl font-bold">${coin}</span>
                   <span class="text-xs bg-gray-700 px-2 py-1 rounded text-gray-300">USD</span>
                    </div>
                 <div id="price-${coin}" class="text-3xl font-black text-green-400">$${data.current_price}</div>
                      <div class="text-xs text-gray-500 mt-2">Şimdi güncellendi</div>
                     </div>`;
                        } else {
                            document.getElementById(`price-${coin}`).innerText = `$${data.current_price}`;
                        }
                    }
               } catch (error) {
                  console.error("Dashboard hatası:", error);
                }
            }
        }

       // Grafiği hafızada tutacağımız değişken (Sayfa değiştiğinde eskisini silebilmek için)
let kriptoGrafik = null;

// GEÇMİŞİ VE GRAFİĞİ GETİREN FONKSİYON
async function gecmisGetir(coin) {
    document.getElementById('gecmisBaslik').innerText = `${coin} Son Fiyat Hareketleri`;
    const list = document.getElementById('gecmisListesi');
    
    try {
        const response = await fetch(`/api/gecmis/${coin}`);
        const resData = await response.json();

        list.innerHTML = "";
        
        if(resData.status === 'success') {
            // GRAFİK İÇİN VERİ HAZIRLIĞI 
            const grafikFiyatlar = [];
            const grafikZamanlar = [];

            // API den en yeni veriler ilk sırada geliyor. Grafiğin soldan sağa 
            // doğru akması için verileri tersine (eskiden yeniye) çeviriyoruz.
            const tersVeriler = [...resData.data].reverse();

            tersVeriler.forEach(item => {
                let saat = new Date(item.created_at).toLocaleTimeString('tr-TR');
                grafikFiyatlar.push(item.price); // Fiyatları ayır
                grafikZamanlar.push(saat);       // Saatleri ayır
            });

            // GRAFİĞİ ÇİZME 
            const ctx = document.getElementById('fiyatGrafigi').getContext('2d');
            
            // Eğer daha önce çizilmiş bir grafik varsa onu temizle (üst üste binmesin)
            if(kriptoGrafik != null) {
                kriptoGrafik.destroy();
            }

            // Yeni Grafiği Yarat
            kriptoGrafik = new Chart(ctx, {
                type: 'line', // Çizgi grafiği
                data: {
                    labels: grafikZamanlar, // Alt taraftaki saatler
                    datasets: [{
                        label: `${coin} Fiyatı`,
                        data: grafikFiyatlar, // Dalgalanan fiyatlar
                        borderColor: '#3b82f6', // Çizgi rengi (Mavi)
                        backgroundColor: 'rgba(59, 130, 246, 0.1)', // Altının hafif mavi dolgusu
                        borderWidth: 2,
                        fill: true, // Altını doldur
                        tension: 0.4 // Çizgileri yumuşat (keskin zikzak yerine dalga yapar)
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }, // Üstteki etiketi gizle
                    scales: {
                        y: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } }, // Eksen renkleri
                        x: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } }
                    }
                }
            });

            // LİSTEYİ DOLDURMA 
            resData.data.forEach(item => {
                let saat = new Date(item.created_at).toLocaleTimeString('tr-TR');
                list.innerHTML += `
                    <li class="flex justify-between bg-gray-900 p-3 rounded border border-gray-800">
                        <span class="text-blue-400 font-bold">${item.coin}</span>
                        <span class="font-mono text-green-400">$${item.price}</span>
                        <span class="text-xs text-gray-500">${saat}</span>
                    </li>`;
            });
        }
    } catch (e) { console.log(e); }
}

        // Sayfa açıldığında arama kutusu için fonksiyonu bağlar
          function fiyatGetir() {
            const c = document.getElementById('coinInput').value.toUpperCase();
            if(c) {
                gecmisGetir(c);
                // Eğer listede yoksa geçici olarak ekleyebilirsin veya sadece geçmişi görür
            }
        }

        // CANLI TAKİP MOTORU 
        // Sayfa ilk açıldığında hemen çalıştır
        dashboardGuncelle();

        // Her 5 saniyede bir dashboardGuncelle fonksiyonunu otomatik çalıştır
        setInterval(dashboardGuncelle, 5000);

    </script>
</body>
</html>