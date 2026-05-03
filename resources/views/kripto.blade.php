<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kripto Canlı Dashboard</title>

    
    <!-- Chart.js Kütüphanesini Sayfaya Ekleme -->
        <script src="https://cdn.tailwindcss.com"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- vite dahil edilimi -->
        @vite(['resources/js/app.js'])

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
<body class="bg-gray-50 dark:bg-[#0b0e11] text-gray-800 dark:text-gray-200 antialiased font-sans transition-colors duration-300 min-h-screen p-8">

   <div class="max-w-6xl mx-auto">
       <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4 bg-white dark:bg-[#181a20] p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-400 rounded flex items-center justify-center font-bold text-gray-900 text-xl">B</div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Kripto <span class="font-normal text-gray-500">Piyasalar</span></h1>
            <span id="ayEvresi" class="text-xs font-medium text-gray-500"></span>
        </div>
    </div>
    
    <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex flex-1 md:flex-none gap-2">
            <input type="text" id="coinInput" placeholder="Arama (Örn: BTC)" class="w-full bg-gray-50 dark:bg-[#0b0e11] border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 uppercase outline-none focus:border-yellow-400 dark:focus:border-yellow-500 text-sm text-gray-800 dark:text-white transition">
            <button onclick="fiyatGetir()" class="bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition text-gray-700 dark:text-gray-300 whitespace-nowrap">Hızlı Bak</button>
        </div>
        <button onclick="temaDegistir()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-300 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>
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
        const takipListesi = ['BTC', 'ETH', 'SOL', 'BNB', 'XRP','ADA','DOGE','LINK'];

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
<div id="card-${coin}" class="bg-white dark:bg-[#181a20] p-5 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-yellow-400 dark:hover:border-yellow-500 transition-colors cursor-pointer group" onclick="gecmisGetir('${coin}')">
    
   <div class="flex justify-between items-center mb-3">
    <div class="flex items-baseline gap-1">
        <span class="text-xl font-bold text-gray-900 dark:text-gray-100">${coin}</span>
        <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">/USDT</span>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">SPOT</span>
        <button onclick="alarmKur(event, '${coin}')" class="text-gray-400 hover:text-yellow-500 transition-colors" title="Fiyat Alarmı Kur">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        </button>
    </div>
</div>
    
    <div class="flex items-center justify-between">
        <div id="price-${coin}" class="text-2xl font-bold text-gray-900 dark:text-white font-mono tracking-tight transition-colors duration-200">
            $${data.current_price}
        </div>
        <div id="change-${coin}" class="text-sm font-bold">
            ${data.change_percent ? (data.change_percent >= 0 ? '+' : '') + data.change_percent + '%' : '%0.00'}
        </div>
    </div>
    
    <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
        <div class="flex items-center gap-1.5">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> 
            <span>Canlı Takip</span>
        </div>
        <span class="opacity-0 group-hover:opacity-100 transition-opacity text-yellow-500 font-medium">Grafik &rarr;</span>
    </div>
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
                        borderColor: '#eab308', // Çizgi rengi (sarı)
                       backgroundColor: 'rgba(234, 179, 8, 0.1)', 
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
    <li class="flex justify-between items-center bg-gray-50 dark:bg-[#0b0e11] p-3 rounded-lg border border-gray-100 dark:border-gray-800">
      <span class="text-gray-900 dark:text-gray-200 font-bold">${item.coin}</span>
     <span class="font-mono text-gray-800 dark:text-gray-300 font-semibold">$${item.price}</span>
    <span class="text-xs text-gray-400">${saat}</span>
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
        dashboardGuncelle();

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
        localStorage.setItem('tema', 'karanlik'); //hafızaya veriyi kaydediyor
    } else {
        localStorage.setItem('tema', 'aydinlik');
    }
}

// Sayfa ilk açıldığında çalışır, Tarayıcı hafızasında tema var mı diye veriyi okur
function temaKontrolEt() {
    const kayitliTema = localStorage.getItem('tema'); //hafızadaki veriyi okuyor
    
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

<script type="module">

// ALARM SİSTEMİ----------------------
    window.alarmlar = {};
//  MİNİMAL BİLDİRİM SİSTEMİ
window.toastGoster = function(mesaj, tip = 'info') {
    let container = document.getElementById('kripto-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'kripto-toast-container';
        container.className = 'fixed top-4 right-4 z-50 flex flex-col pointer-events-none';
        document.body.appendChild(container);
    }

    const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
    
    // Binance tarzı ince sol çizgi (Sarı veya Yeşil)
    let borderRenk = tip === 'alert' ? 'border-[#fcd535]' : 'border-[#0ecb81]';

    const toastHtml = `
    <div id="${toastId}" class="bg-white dark:bg-[#1e2329] ${borderRenk} border-l-4 text-gray-800 dark:text-gray-200 px-4 py-3 mb-2 shadow-lg text-sm rounded-r flex items-center transition-opacity duration-300 opacity-0">
        <span>${mesaj}</span>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastEl = document.getElementById(toastId);
    setTimeout(() => toastEl.classList.remove('opacity-0'), 10); // Hafifçe belirme

    // 4 saniye sonra kaybolma
    setTimeout(() => {
        toastEl.classList.add('opacity-0');
        setTimeout(() => toastEl.remove(), 300);
    }, 4000);
};

// 2. MİNİMAL FİYAT SORMA EKRANI (MODAL)
window.alarmModalAc = function(coin) {
    const modalHtml = `
    <div id="alarm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
     <div class="bg-white dark:bg-[#1e2329] w-80 rounded shadow-lg border border-gray-200 dark:border-gray-800 p-5">
     <div class="flex justify-between items-center mb-4">
         <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">${coin} Hedef Fiyat</h3>
      </div>
            
         <div class="relative mb-5">
        <input type="number" id="alarm-hedef-fiyat" class="w-full bg-gray-50 dark:bg-[#0b0e11] text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded p-2 text-sm focus:border-[#fcd535] focus:ring-0 outline-none transition-colors" placeholder="Fiyat girin">
         <span class="absolute right-3 top-2.5 text-xs text-gray-400 font-semibold">USDT</span>
            </div>
            
            <div class="flex justify-end gap-3 text-sm">
        <button onclick="document.getElementById('alarm-modal').remove()" class="px-4 py-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">İptal</button>
               <button onclick="alarmKaydet('${coin}')" class="px-4 py-1.5 bg-[#fcd535] hover:bg-[#f0c822] text-black font-semibold rounded transition-colors">Onayla</button>
         </div>
        </div>
    </div>`;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    document.getElementById('alarm-hedef-fiyat').focus();
};

window.alarmKaydet = function(coin) {
    const fiyat = document.getElementById('alarm-hedef-fiyat').value;
    if(fiyat && !isNaN(fiyat)) {
        window.alarmlar[coin] = parseFloat(fiyat);
        window.toastGoster(`Alarm kuruldu: ${coin} için $${fiyat}`, 'success');
        document.getElementById('alarm-modal').remove();
    }
};

window.alarmKur = function(event, coin) {
    event.stopPropagation();
    window.alarmModalAc(coin); 
};

//  ALARM ÇALDIĞINDA 
window.alarmCal = function(coin, fiyat) {
    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
    audio.play();
    
    window.toastGoster(`🔔 ${coin} hedefe ulaştı! Anlık fiyat: $${fiyat}`, 'alert');
};
 // ALARM SİSTEMİ---------------------- SON

    const oncekiFiyatlar = {};
    setTimeout(() => {
        if (window.Echo) {
            console.log("Echo dinleniyor...");

            window.Echo.channel('kripto-kanal')
            .listen('.fiyat.guncellendi', (e) => {

                const priceElement = document.getElementById(`price-${e.coin}`);
             const cardElement = document.getElementById(`card-${e.coin}`);
                // Yüzde elementini HTML'den seçiyoruz
              const changeElement = document.getElementById(`change-${e.coin}`); 

                if (priceElement && cardElement) {

                    // --- FİYAT GÜNCELLEME ALANI ---
                    const yeniFiyat = parseFloat(e.fiyat);
                    const eskiFiyat = oncekiFiyatlar[e.coin] ?? yeniFiyat;

               priceElement.innerHTML = `$${yeniFiyat}`;
               priceElement.classList.remove('text-green-500', 'text-red-500', 'text-gray-900', 'dark:text-white');
               if (yeniFiyat > eskiFiyat) {
                    priceElement.classList.add('text-green-500');
                    }
                    else if (yeniFiyat < eskiFiyat) {
                        priceElement.classList.add('text-red-500');
                    } else {
                        priceElement.classList.add('text-gray-900', 'dark:text-white');
                    }

                    //  YÜZDE GÜNCELLEME ALANI 
                    if (changeElement && e.yuzde !== undefined) {
                        const yuzde = parseFloat(e.yuzde); 
                        changeElement.innerHTML = (yuzde >= 0 ? '+' : '') + yuzde.toFixed(2) + '%';
                        
                        // Pozitifse Yeşil, Negatifse Kırmızı yap
                        if (yuzde >= 0) {
                            changeElement.className = "text-sm font-bold text-green-500";
                        } else {
                            changeElement.className = "text-sm font-bold text-red-500";
                        }
                    }

                    // ALARM KONTROL NOKTASI 
        if (window.alarmlar[e.coin]) {
            const hedef = window.alarmlar[e.coin];
            if ((eskiFiyat < hedef && yeniFiyat >= hedef) || (eskiFiyat > hedef && yeniFiyat <= hedef)) {
                window.alarmCal(e.coin, yeniFiyat);
                delete window.alarmlar[e.coin]; 
            }
        }

              oncekiFiyatlar[e.coin] = yeniFiyat; }

            });

        } else {
            console.error("Echo yüklenmedi!");
        }
    }, 1000);
</script>

</body>
</html>