<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Kecamatan Kabupaten Tasikmalaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map {
            height: 100vh;
        }

        /* Custom styling untuk popup leaflet */
        .custom-popup .leaflet-popup-content-wrapper {
            background: transparent;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .custom-popup .leaflet-popup-content {
            margin: 0;
            padding: 0;
        }

        .custom-popup .leaflet-popup-tip {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
        }

        /* Animasi hover untuk elemen dalam popup */
        .custom-popup .bg-white:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease-in-out;
        }

        /* Style untuk scrollbar jika konten terlalu panjang */
        .custom-popup::-webkit-scrollbar {
            width: 4px;
        }

        .custom-popup::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .custom-popup::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .custom-popup::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script>
        // Inisialisasi peta kosong
        var map = L.map('map').setView([-7.35, 108.2], 10);

        // ---------- BASEMAP ----------
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        });

        var esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/' +
            'World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '© Esri'
            });

        // ---------- STYLE ----------
        function stylePolygon(feature) {
            return {
                fillColor: '#4ade80',
                weight: 1,
                opacity: 1,
                color: 'white',
                dashArray: '2',
                fillOpacity: 0.7
            };
        }

        function onEachFeature(feature, layer) {
            var props = feature.properties;
            var html = "";
            if (props.NAMOBJ) html += "<b>Nama:</b> " + props.NAMOBJ + "<br>";
            if (props.WADMKC) html += "<b>Kecamatan:</b> " + props.WADMKC;
            layer.bindPopup(html);
        }

        // ---------- LAYER GEOJSON ----------
        var kecamatanLayer = L.layerGroup();
        fetch("/assets/geojson/kecamatan.geojson")
            .then(res => res.json())
            .then(data => {
                var geo = L.geoJSON(data, {
                    style: stylePolygon,
                    onEachFeature: onEachFeature
                }).addTo(kecamatanLayer);
                kecamatanLayer.addTo(map); // langsung tampilkan
                map.fitBounds(geo.getBounds()); // auto zoom ke kecamatan
            });

        var desaLayer = L.layerGroup();
        fetch("/assets/geojson/desa.geojson")
            .then(res => res.json())
            .then(data => {
                L.geoJSON(data, {
                    style: stylePolygon,
                    onEachFeature: onEachFeature
                }).addTo(desaLayer);
            });

        // ---------- LAYER TITIK ----------
        var kantorKecamatan = L.layerGroup([
            L.marker([-7.35, 108.2]).bindPopup("Kantor Kecamatan Contoh")
        ]);

        var sekolah = L.layerGroup([
            L.marker([-7.32, 108.25]).bindPopup("Sekolah Contoh")
        ]);

        // ---------- LAYER TERMINAL (API) ----------
        var terminalLayer = L.layerGroup();

        fetch("https://geoentry.tasikmalayakab.go.id/api/terminal")
            .then(res => res.json())
            .then(data => {
                // pastikan bentuk data array
                let items = Array.isArray(data) ? data : (data.data || []);
                items.forEach(item => {
                    // ambil lat & lot
                    let lat = parseFloat(item.latitude);
                    let lon = parseFloat(item.longitude); // perhatikan: lot bukan lon
                    if (!isNaN(lat) && !isNaN(lon)) {
                        let popupHtml = `
        <div class="bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden" style="width: 288px; max-width: 288px;">
            <!-- Header Executive Style -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-4 py-3">
                <div>
                    <h2 class="text-white font-semibold text-base">${item.nama_terminal || 'Terminal'}</h2>
                    <span class="text-slate-300 text-xs">Terminal Transportasi</span>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="p-4">
                <!-- Status Indicator -->
                <div class="mb-3">
                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></div>
                        Operasional
                    </div>
                </div>

                <!-- Primary Info Grid -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    ${item.luas ? `
                                                                    <div class="bg-blue-50 rounded-md p-3">
                                                                        <div class="text-lg font-bold text-blue-600">${item.luas}</div>
                                                                        <div class="text-xs text-blue-700 font-medium">Luas Area</div>
                                                                    </div>
                                                                    ` : ''}
                    
                    ${item.tahun ? `
                                                                    <div class="bg-purple-50 rounded-md p-3">
                                                                        <div class="text-lg font-bold text-purple-600">${item.tahun}</div>
                                                                        <div class="text-xs text-purple-700 font-medium">Tahun Operasi</div>
                                                                    </div>
                                                                    ` : ''}
                </div>

                <!-- Location & Details -->
                <div class="space-y-2">
                    ${item.alamat ? `
                                                                    <div class="flex items-start space-x-2 p-2 bg-gray-50 rounded-md">
                                                                        <div class="w-4 h-4 text-gray-500 mt-0.5">📍</div>
                                                                        <div>
                                                                            <div class="text-xs font-medium text-gray-900">Alamat</div>
                                                                            <div class="text-xs text-gray-700">${item.alamat}</div>
                                                                        </div>
                                                                    </div>
                                                                    ` : ''}

                    ${item.desa || item.kecamatan ? `
                                                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                                        <span class="text-xs font-medium text-gray-700">Wilayah</span>
                                                                        <span class="text-xs text-gray-900 font-semibold">${[item.desa, item.kecamatan].filter(Boolean).join(', ')}</span>
                                                                    </div>
                                                                    ` : ''}

                    ${item.pemilik ? `
                                                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                                        <span class="text-xs font-medium text-gray-700">Pengelola</span>
                                                                        <span class="text-xs text-gray-900 font-semibold">${item.pemilik}</span>
                                                                    </div>
                                                                    ` : ''}

                    ${item.tipe_terminal ? `
                                                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                                                                        <span class="text-xs font-medium text-gray-700">Tipe Terminal</span>
                                                                        <span class="text-xs text-gray-900 font-semibold">${item.tipe_terminal}</span>
                                                                    </div>
                                                                    ` : ''}
                </div>

                <!-- Action Button -->
                <div class="mt-4">
                    <button class="w-full bg-slate-800 hover:bg-slate-700 text-white font-medium py-1.5 px-2.5 rounded-md transition-colors duration-200 flex items-center justify-center space-x-1 text-xs">
                        <span>Lihat map</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

                        const marker = L.marker([lat, lon])
                            .bindPopup(popupHtml, {
                                maxWidth: 300,
                                minWidth: 288,
                                className: 'executive-popup',
                                autoClose: false,
                                closeOnEscapeKey: true
                            })
                            .addTo(terminalLayer);

                        // Fix untuk rendering popup yang benar
                        marker.on('popupopen', function() {
                            setTimeout(() => {
                                marker.getPopup().update();
                            }, 10);
                        });
                    }
                });
            })
            .catch(err => console.error("Gagal load API terminal:", err));

        // ---------- CONTROL ----------
        var baseMaps = {
            "Open Street Map": osm,
            "Esri Satelit": esri
        };

        var overlayMaps = {
            "Peta Kecamatan": kecamatanLayer,
            "Peta Desa": desaLayer,
            "Kantor Kecamatan": kantorKecamatan,
            "Sekolah": sekolah,
            "Terminal": terminalLayer // <-- menu baru
        };

        L.control.layers(baseMaps, overlayMaps, {
            collapsed: false
        }).addTo(map);
    </script>
</body>

</html>
