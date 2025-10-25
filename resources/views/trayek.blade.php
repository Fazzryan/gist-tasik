<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Trayek Singaparna - Biru Elegan</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body,
        html {
            margin: 0;
            height: 100%;
        }

        #map {
            width: 100%;
            height: 100vh;
        }

        .leaflet-control-layers {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inisialisasi peta
        const map = L.map('map').setView([-7.35, 108.2], 11);

        // 🗺️ Basemap
        const base = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 🚍 Daftar file trayek
        const trayekFiles = [
            "Singaparna-Cibalanaarik.json",
            "Singaparna-Cigalonang.json",
            "Singaparna-Cikeusal.json",
            "Singaparna-Cimerah.json",
            "Singaparna-Ciponoyo-Cipanas.json",
            "Singaparna-Leuwisari.json",
            "Singaparna-Leuwisari-Cidugaleun.json",
            "Singaparna-Leuwisari-Malaganti.json",
            "Singaparna-Linggamulya-Rawa.json",
            "Singaparna-Linggasirna.json",
            "Singaparna-Salawu.json",
            "Singaparna-Sindangsono.json",
            "Singaparna-Sukakarsa.json",
            "Singaparna-Sukamulih-Pangkalan.json",
            "Singaparna-Sukarame.json",
            "Singaparna-Tenjowaringin.json"
        ];

        const trayekLayers = {};
        const overlayLayers = {
            "Kecamatan": kecamatanLayer,
            "Desa": desaLayer
        };

        // 🚏 Load semua trayek dan langsung tampil
        Promise.all(
            trayekFiles.map(file =>
                fetch('assets/geojson/' + file)
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data) return null;

                    // Warna biru seragam untuk semua trayek
                    const layer = L.geoJSON(data, {
                        style: {
                            color: "#007bff",
                            weight: 4,
                            opacity: 0.9
                        },
                        onEachFeature: (f, l) => {
                            // Nama trayek dari nama file
                            const namaTrayek = file
                                .replace('.json', '')
                                .replace('Singaparna-', 'Trayek Singaparna – ')
                                .replace(/-/g, ' ');

                            // Popup saat diklik
                            l.bindPopup(`<b>${namaTrayek}</b>`);
                        }
                    }).addTo(map);

                    trayekLayers[file.replace('.json', '')] = layer;
                    return layer;
                })
            )
        ).then(() => {
            Object.assign(overlayLayers, trayekLayers);
            L.control.layers(null, overlayLayers, {
                collapsed: false,
                position: 'topright'
            }).addTo(map);
        });

        // 🟢 Layer Kecamatan (warna lembut)
        const kecamatanLayer = L.geoJSON(null, {
            style: {
                color: "#2E8B57", // hijau daun lembut
                weight: 1,
                fillColor: "#ADFF2F80", // hijau muda transparan
                fillOpacity: 0.2
            },
            onEachFeature: (f, l) => l.bindPopup(`<b>Kecamatan:</b> ${f.properties.WADMKC || 'Tidak diketahui'}`)
        }).addTo(map);

        // 🟩 Layer Desa (lebih samar)
        const desaLayer = L.geoJSON(null, {
            style: {
                color: "#2E8B57", // hijau daun lembut
                weight: 1,
                fillColor: "#ADFF2F80", // hijau muda transparan
                fillOpacity: 0.2
            },
            onEachFeature: (f, l) => l.bindPopup(`<b>Desa:</b> ${f.properties.NAMOBJ || 'Tidak diketahui'}`)
        });

        // 🧭 Load data wilayah
        fetch('assets/geojson/kecamatan.geojson').then(r => r.json()).then(d => {
            kecamatanLayer.addData(d);
            map.fitBounds(kecamatanLayer.getBounds());
        });
        fetch('assets/geojson/desa.geojson').then(r => r.json()).then(d => desaLayer.addData(d));
    </script>
</body>

</html>
