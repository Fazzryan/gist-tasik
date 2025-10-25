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
    </style>
</head>

<body>
    <div id="map"></div>

    <script>
        // Inisialisasi peta kosong (tanpa tile basemap)
        var map = L.map('map').setView([-7.35, 108.2], 10);

        // ---------- BASEMAP (opsional kalau mau ditampilkan via checkbox) ----------
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
        fetch("/assets/peta/kecamatan.json")
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
        fetch("/assets/peta/desa.json")
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

        // ---------- CONTROL ----------
        var baseMaps = {
            "Open Street Map": osm,
            "Esri Satelit": esri
        };

        var overlayMaps = {
            "Peta Kecamatan": kecamatanLayer,
            "Peta Desa": desaLayer,
            "Kantor Kecamatan": kantorKecamatan,
            "Sekolah": sekolah
        };

        L.control.layers(baseMaps, overlayMaps, {
            collapsed: false
        }).addTo(map);
    </script>
</body>

</html>
