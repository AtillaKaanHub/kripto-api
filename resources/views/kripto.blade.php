<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kripto Canlı Dashboard</title>

    <!-- Chart.js Kütüphanesini Sayfaya Ekleme -->
        <script src="https://cdn.tailwindcss.com"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- karanlık mod -->
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

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
<body class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-white transition-colors duration-300 min-h-screen p-8">

   <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-bold text-blue-600 dark:text-blue-400">Kripto Canlı Panel</h1>
                <p class="text-gray-500 dark:text-gray-400">
                    Veriler otomatik güncellenir <span class="live-dot ml-1"></span>
                    <br>
                    <span id="ayEvresi" class="text-indigo-500 dark:text-indigo-300 font-semibold text-sm mt-1 inline-block"></span>
                </p>
            </div>
            
            <div class="flex gap-4 items-center">
                <button onclick="temaDegistir()" class="text-2xl p-2 rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 hover:shadow-lg transition">
                    🌓
                </button>

                <div class="flex gap-2">
                    <input type="text" id="coinInput" placeholder="BTC, ETH..." class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded px-4 py-2 uppercase outline-none focus:border-blue-500 text-gray-800 dark:text-white shadow-sm">
                    <button onclick="fiyatGetir()" class="bg-blue-600 px-6 py-2 rounded text-white hover:bg-blue-700 transition shadow-sm">Hızlı Bak</button>
                </div>
            </div>
        </div>

        <div id="dashboardGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            </div>

      <div class="mb-6 bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
          <h3 id="gecmisBaslik" class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 text-center">İşlem Geçmişi</h3>
            
            <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300">
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

        // Sayfa yüklendiğinde Ay evresini hesapla ve ekrana yaz
document.getElementById('ayEvresi').innerText = "Güncel Ay Evresi: " + getMoonPhaseEmoji();

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
    <div id="card-${coin}" class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition hover:border-blue-500 cursor-pointer" onclick="gecmisGetir('${coin}')">
        <div class="flex justify-between items-center mb-4">
            <span class="text-2xl font-bold text-gray-800 dark:text-white">${coin}</span>
            <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300">USD</span>
        </div>
        <div id="price-${coin}" class="text-3xl font-black text-green-500 dark:text-green-400">$${data.current_price}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Şimdi güncellendi</div>
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
                   <li class="flex justify-between bg-gray-50 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-800">
        <span class="text-blue-600 dark:text-blue-400 font-bold">${item.coin}</span>
        <span class="font-mono text-green-600 dark:text-green-400">$${item.price}</span>
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


        // O anki tarihe göre Ay'ın evresini hesaplayan fonksiyon
function getMoonPhaseEmoji() {
    const bugun = new Date();
    // Bilinen geçmiş bir Yeni Ay tarihi (Referans noktası)
    const yeniAyTarihi = new Date('2000-01-06T18:14:00Z');
    
    // Geçen süreyi güne çeviriyoruz
const fark = bugun - yeniAyTarihi;
    const gun = fark / (1000 * 60 * 60 * 24);
    
// Ay döngüsü yaklaşık 29.53 gündür
    const dongu = 29.53058867;
    let evre = (gun % dongu) / dongu;
    if (evre < 0) evre += 1;

    // Yüzdelere göre Ay ın şeklini döndürüyoruz
    if (evre >= 0.97 || evre < 0.03) return "🌑 Yeni Ay (Dip Beklentisi?)";
    if (evre >= 0.03 && evre < 0.22) return "🌒 Büyüyen Hilal";
    if (evre >= 0.22 && evre < 0.28) return "🌓 İlk Dördün";
    if (evre >= 0.28 && evre < 0.47) return "🌔 Büyüyen Şişkin";
    if (evre >= 0.47 && evre < 0.53) return "🌕 Dolunay (Tepe Beklentisi?)";
    if (evre >= 0.53 && evre < 0.72) return "🌖 Küçülen Şişkin";
    if (evre >= 0.72 && evre < 0.78) return "🌗 Son Dördün";
    return "🌘 Küçülen Hilal";
}


// KARANLIK / AYDINLIK MOD YÖNETİMİ
function temaDegistir() {
    const html = document.documentElement; // <html> etiketini seçer
    html.classList.toggle('dark'); // dark sınıfını varsa siler, yoksa ekler
    
    // Kullanıcının tercihini tarayıcıya kaydet 
    if(html.classList.contains('dark')) {
        localStorage.setItem('tema', 'karanlik');
    } else {
        localStorage.setItem('tema', 'aydinlik');
    }
}

// Sayfa ilk açıldığında çalışır, Tarayıcı hafızasında tema var mı diye bakar
function temaKontrolEt() {
    const kayitliTema = localStorage.getItem('tema');
    
    if (kayitliTema === 'karanlik') {
        document.documentElement.classList.add('dark');
    } else if (kayitliTema === 'aydinlik') {
        document.documentElement.classList.remove('dark');
    } else {
        // Eğer daha önce hiç seçim yapmamışsa, kullanıcının bilgisayar ayarlarına bakar
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    }
}

// Sayfa yüklenirken hemen temayı kontrol et
temaKontrolEt();



    </script>
</body>
</html>