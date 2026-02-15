var config = document.querySelector("[data-configuration]");
var activities = document.querySelectorAll("[data-container='coordinates']");

// SVG icon helpers for Leaflet markers
function createCircleIcon(color) {
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">' +
        '<circle style="fill:' + color + ';stroke:#000000;stroke-width:1.25" cx="10" cy="10" r="9.4"></circle>' +
        '</svg>';
    return L.icon({
        iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
        iconSize: [20, 20],
        iconAnchor: [10, 10],
    });
}

var blueIcon = createCircleIcon('#36a2eb');
var greenIcon = createCircleIcon('#4bc0c0');
var orangeIcon = createCircleIcon('#ffcd56');

var map;
var tileLayer;
var routeBounds;

// Activities
activities.forEach(function (activity) {
    var mapContainer = document.getElementById('mapContainer' + activity.dataset.mapContainer);

    map = L.map(mapContainer, {
        preferCanvas: true,
        zoomControl: activities.length === 1,
        dragging: activities.length === 1,
        scrollWheelZoom: activities.length === 1,
        doubleClickZoom: activities.length === 1,
        touchZoom: activities.length === 1,
        boxZoom: activities.length === 1,
        keyboard: activities.length === 1,
    });

    tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        crossOrigin: 'anonymous',
    }).addTo(map);

    var startPoint = null;
    var endPoint = null;
    var latLngs = [];

    activity.querySelectorAll("activity coord").forEach(function (coord) {
        var latitude = parseFloat(coord.dataset.lat);
        var longitude = parseFloat(coord.dataset.long);

        latLngs.push([latitude, longitude]);

        if (startPoint == null)
            startPoint = [latitude, longitude];
        endPoint = [latitude, longitude];
    });

    var color = randomColor({ luminosity: "dark" });

    // Route outline (wider, semi-transparent)
    var routeOutline = L.polyline(latLngs, {
        color: hexToRgbA(color, 0.7),
        weight: 8,
        lineCap: 'butt',
        lineJoin: 'round',
    }).addTo(map);

    // Route line (narrower, dashed white overlay)
    L.polyline(latLngs, {
        color: '#ffffff',
        weight: 4,
        dashArray: '0, 8',
        lineCap: 'butt',
        lineJoin: 'round',
    }).addTo(map);

    if (activities.length == 1) {
        L.marker(startPoint, { icon: orangeIcon }).addTo(map);
        L.marker(endPoint, { icon: greenIcon }).addTo(map);
    }

    // Fit map to route bounds with padding
    routeBounds = routeOutline.getBounds().pad(0.05);
    map.fitBounds(routeBounds);
});

function hexToRgbA(hex, alpha) {
    if (!alpha) {
        alpha = 1;
    }
    var c;
    if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
        c = hex.substring(1).split('');
        if (c.length == 3) {
            c = [c[0], c[0], c[1], c[1], c[2], c[2]];
        }
        c = '0x' + c.join('');
        return 'rgba(' + [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',') + ', ' + alpha + ')';
    }
    throw new Error('Bad Hex');
}

// Map capture for thumbnail generation
if (activities.length === 1) {
    if (config.dataset.captureId) {
        // Wait for the initial tiles to load, then capture via leaflet-image
        tileLayer.once('load', function () {
            leafletImage(map, function (err, canvas) {
                if (err || !canvas) return;
                fetch("/capture", {
                    method: "POST",
                    body: JSON.stringify({
                        capture_id: config.dataset.captureId,
                        data: canvas.toDataURL("image/png"),
                    }),
                    headers: {
                        "Content-type": "application/json; charset=UTF-8",
                        'X-CSRF-Token': document.querySelector('meta[name="_token"]').content,
                    }
                }).then(function () {
                    document.querySelectorAll(".mapContainer")[0].style.width = '100%';
                    map.invalidateSize();
                });
            });
        });
    }
}

// Marker on hover.
if (activities.length === 1 && document.querySelector("[data-container='speeds']")) {
    var placedMarkers = [];

    document.querySelector("[data-container='speeds']").addEventListener(
        "speed.hover",
        function (event) {
            // Remove previously placed markers
            placedMarkers.forEach(function (marker) {
                map.removeLayer(marker);
            });
            placedMarkers = [];

            event.detail.forEach(function (index) {
                var coord = activities[0].querySelectorAll("coord")[index];
                var marker = L.marker(
                    [parseFloat(coord.dataset.lat), parseFloat(coord.dataset.long)],
                    { icon: blueIcon }
                ).addTo(map);

                placedMarkers.push(marker);
            });
        },
        false,
    );
}
