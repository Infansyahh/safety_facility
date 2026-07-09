<?php
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengecekan fasilitas keselamatan - Safety Facility</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="foto/logo.png">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-dots {
            background-color: #ffffff;
            background-image: radial-gradient(#e5e7eb 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        /* Premium Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(35px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out;
        }
        .animate-slide-up {
            animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .animate-slide-right {
            animation: slideInRight 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="foto/logo.png" alt="Safety Facility Logo" class="h-12 w-auto object-contain">
                <span class="text-xl font-bold text-slate-800 sm:inline">Pengecekan fasilitas keselamatan</span>
            </div>

            <div class="flex items-center space-x-3">
                <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md text-sm transition unique-btn">
                    Login Bogor
                </a>
                <a href="login.php" class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-md text-sm transition">
                    Login Maja
                </a>
            </div>
        </div>
    </header>

    <section class="bg-[#000766] text-white overflow-hidden py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <div class="lg:col-span-7 space-y-6 animate-slide-up">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    Sistem Manajemen <br>
                    <span class="text-yellow-300">Pengecekan fasilitas keselamatan</span>
                </h1>
                <p class="text-lg text-red-100 max-w-xl leading-relaxed">
                    Kelola pengecekan dan perawatan fasilitas keselamatan dengan lebih mudah, cepat, dan terorganisir.
                </p>

                <div>
                    <button class="bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold px-6 py-3 rounded-lg shadow-md transition flex items-center space-x-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Learn</span>
                    </button>
                </div>
            </div>

            <div class="lg:col-span-5 flex justify-center lg:justify-end animate-slide-right">
                <div class="relative max-w-md w-full rounded-2xl overflow-hidden shadow-2xl border border-white/10">
                    <img src="foto/foto.png" alt="Petugas K3 melakukan pengecekan APAR" class="w-full h-auto object-cover block">
                </div>
            </div>

        </div>
    </section>

    <section class="bg-dots py-20 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Fitur Utama</h2>
                <p class="text-gray-500 text-sm">Semua yang kamu butuhkan untuk mengelola pengecekan FASILITAS KESELAMATAN lebih mudah.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <?php
                $features = [
                    [
                        'icon' => '<img src="foto/Pengecekan.png" alt="">',
                        'title' => 'Pengecekan Rutin',
                        'desc' => 'Pantau jadwal pengecekan Alat keamanan secara berkala agar selalu siap pakai.'
                    ],
                    [
                        'icon' => ' <img src="foto/laporan.png" alt=""> ',
                        'title' => 'Laporan Digital',
                        'desc' => 'Semua laporan pengecekan tersimpan rapi dan bisa diakses kapan saja.'
                    ],
                    [
                        'icon' => '🔔',
                        'title' => 'Notifikasi Otomatis',
                        'desc' => 'Dapatkan pengingat otomatis untuk pengecekan dan perawatan.'
                    ],
                    [
                        'icon' => '<img src="foto/qr.png" alt="">',
                        'title' => 'QR Code Scanner',
                        'desc' => 'Akses data pengecekan Alat lebih cepat dengan memindai QR Code pada perangkat.'
                    ],
                    [
                        'icon' => '<img src="foto/manajemen.png" alt="">',
                        'title' => 'Manajemen Pengguna & Admin',
                        'desc' => 'Kelola peran pengguna dan admin dengan akses yang terstruktur dan aman.'
                    ],
                    [
                        'icon' => '<img src="foto/Visualisasi.png" alt="">',
                        'title' => 'Visualisasi Data',
                        'desc' => 'Pantau hasil pengecekan dengan grafik interaktif untuk analisis yang lebih mudah.'
                    ]
                ];

                $index = 0;
                foreach ($features as $f) :
                    $index++;
                ?>
                    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition duration-200 text-center flex flex-col items-center animate-slide-up" style="animation-delay: <?= $index * 0.15; ?>s;">
                        <div class="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center text-2xl mb-5">
                            <?= $f['icon']; ?>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-3"><?= $f['title']; ?></h3>
                        <p class="text-gray-500 text-sm leading-relaxed max-w-xs"><?= $f['desc']; ?></p>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <footer class="bg-[#111827] text-gray-400 pt-16 pb-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-12 pb-12 border-b border-gray-800">

            <div class="md:col-span-5 space-y-4">
                <h3 class="text-xl font-bold text-white">Pengecekan FASILITAS KESELAMATAN</h3>
                <p class="text-sm leading-relaxed max-w-sm text-gray-400">
                    Sistem digital untuk memudahkan pengecekan dan pelaporan FASILITAS KESELAMATAN secara efisien, cepat, dan terstruktur.
                </p>
            </div>

            <div class="md:col-span-3 space-y-4">
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider">Navigasi</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><button onclick="openModal('Tentang Kami', 'Aplikasi Pengecekan Fasilitas Keselamatan membantu perusahaan melakukan monitoring dan pengecekan Fasilitas keselamatan berbasis QR Code agar lebih cepat, efisien, dan terdokumentasi.')" class="hover:text-white transition">Tentang Kami</button></li>
                    <li><button onclick="openModal('Layanan', 'Daftar layanan kami...')" class="hover:text-white transition">Layanan</button></li>
                    <li><button onclick="openModal('Kebijakan Privasi', 'Kami menghargai privasi pengguna. Data hanya digunakan untuk kepentingan monitoring APAR dan tidak akan dibagikan kepada pihak ketiga tanpa izin resmi.')" class="hover:text-white transition">Kebijakan Privasi</button></li>
                    <li><button onclick="openModal('Bantuan', 'Butuh bantuan? hubungi...')" class="hover:text-white transition">Bantuan</button></li>
                    <li><button onclick="openModal('Kontak', 'Informasi kontak kami...')" class="hover:text-white transition">Kontak</button></li>
                </ul>
            </div>

            <div class="md:col-span-4 space-y-4">
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider">Kontak</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>Email: <span class="text-gray-300">support@gmail.com</span></li>
                    <li>Telp: <span class="text-gray-300">+62 821-2509-8439</span></li>
                </ul>
                <div class="flex items-center space-x-4 pt-2">
                    <a href="facebook.com" class="w-8 h-8 rounded-full bg-gray-800 hover:bg-blue-600 text-white flex items-center justify-center transition text-sm"><img src="foto/facebook.png" alt=""></a>
                    <a href="instagram.com" class="w-8 h-8 rounded-full bg-gray-800 hover:bg-pink-600 text-white flex items-center justify-center transition text-sm"><img src="foto/instagram.png" alt=""></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-gray-800 hover:bg-blue-700 text-white flex items-center justify-center transition text-sm"><img src="foto/linkedin.png" alt=""></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-gray-800 hover:bg-emerald-600 text-white flex items-center justify-center transition text-sm">w</a>
                </div>
            </div>

        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500">
            <p>&copy; <?= date('Y'); ?> Pengecekan FASILITAS KESELAMATAN. All rights reserved.</p>
            <p class="mt-2 sm:mt-0">v1.0.0</p>
        </div>
    </footer>

    <div id="learnModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
            <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="cur rentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h3 class="text-2xl font-bold text-slate-800 mb-4">Tentang Fasilitas Keselamatan</h3>
            <p class="text-gray-600 leading-relaxed mb-4">
                Fasilitas keselamatan adalah perangkat penting yang dirancang untuk melindungi jiwa dan aset dari bahaya.
                Contohnya meliputi APAR (Alat Pemadam Api Ringan), Hydrant, Jalur Evakuasi, dan Detektor Asap.
                Sistem ini membantu Anda memastikan setiap perangkat tersebut dalam kondisi prima melalui inspeksi berkala.
            </p>
            <button id="closeModalBtn" class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition">
                Mengerti
            </button>
        </div>
    </div>
    <div id="dynamicModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 mb-4"></h3>
            <div id="modalContent" class="text-gray-600 leading-relaxed mb-6"></div>
            <button onclick="closeModal()" class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition">
                Tutup           
            </button>
        </div>
    </div>

    <script>
        const modal = document.getElementById('learnModal');
        const learnBtn = document.querySelector('button:has(span)'); // Mengambil tombol Learn
        const closeBtns = [document.getElementById('closeModal'), document.getElementById('closeModalBtn')];

        learnBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });
        const dynamicModal = document.getElementById('dynamicModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');

        function openModal(title, content) {
            modalTitle.innerText = title;
            modalContent.innerHTML = content; // Menggunakan innerHTML agar bisa masukkan tag <p> atau <ul>
            dynamicModal.classList.remove('hidden');
            dynamicModal.classList.add('flex');
        }

        function closeModal() {
            dynamicModal.classList.add('hidden');
            dynamicModal.classList.remove('flex');
        }

        // Menutup modal jika klik di luar area modal
        window.onclick = function(event) {
            if (event.target == dynamicModal) {
                closeModal();
            }
    }
    </script>
</body>

</html>