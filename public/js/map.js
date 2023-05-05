var config = document.querySelector("[data-configuration]");

var platform = new H.service.Platform({
    apikey: config.dataset.hereApiKey,
    app_id: config.dataset.hereAppId,
    app_code: config.dataset.hereAppCode
});

var defaultLayers = platform.createDefaultLayers();

var map = new H.Map(
    document.getElementById('mapContainer'),
    defaultLayers.vector.normal.map,
);

var captured = false;
if (config.dataset.captureId) {
    map.getEngine().addEventListener('render', (evt) => {
        if (map.getEngine() === evt.target) {
            if (!captured) {
                map.capture(function(capturedCanvas) {
                    fetch("/capture", {
                        method: "POST",
                        body: JSON.stringify({
                            capture_id: config.dataset.captureId,
                            data: capturedCanvas.toDataURL("image/png"),
                        }),
                        headers: {
                            "Content-type": "application/json; charset=UTF-8",
                            'X-CSRF-Token': document.querySelector('meta[name="_token"]').content,
                        }
                    }).then(function () {
                        document.getElementById('mapContainer').style.width = '100%';
                        map.getViewPort().resize();
                    });
                });
                captured = true;
            }
        }
    });
}

var activities = document.querySelectorAll("[data-container='coordinates'] activity");
var markers = document.querySelectorAll("[data-container='coordinates'] markers coord");

var ui = H.ui.UI.createDefault(map, defaultLayers);
var behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));

var blue = new H.map.Icon('<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.1" id="svg2" width="20" height="20">\n' +
    '<circle id="c2" style="fill:#36a2eb;stroke:#000000;stroke-width:1.25" cx="10" cy="10" r="9.4"></circle>\n' +
    '</svg>\n');
var green = new H.map.Icon('<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.1" id="svg2" width="20" height="20">\n' +
    '<circle id="c2" style="fill:#4bc0c0;stroke:#000000;stroke-width:1.25" cx="10" cy="10" r="9.4"></circle>\n' +
    '</svg>\n');
var orange = new H.map.Icon('<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" version="1.1" id="svg2" width="20" height="20">\n' +
    '<circle id="c2" style="fill:#ffcd56;stroke:#000000;stroke-width:1.25" cx="10" cy="10" r="9.4"></circle>\n' +
    '</svg>\n');

var group = new H.map.Group();
map.removeObjects(map.getObjects())

// Activities
activities.forEach(activity => {
    var startPoint = null;
    routeString = new H.geo.LineString();

    activity.querySelectorAll("coord").forEach(coord => {
        var latitude = parseFloat(coord.dataset.lat);
        var longitude = parseFloat(coord.dataset.long);

        coords = { lat: latitude, lng: longitude };

        routeString.pushLatLngAlt(latitude, longitude);

        if (startPoint == null)
            startPoint = coords;
        endPoint = coords;
    });

    var color = randomColor({ luminosity: "dark" });
    var routeOutline = new H.map.Polyline(routeString, {
        style: {
            lineWidth: 8,
            strokeColor: hexToRgbA(color, 0.7),
            lineTailCap: 'arrow-tail',
            lineHeadCap: 'arrow-head'
        }
    });

    var routeLine = new H.map.Polyline(routeString, {
        style: {
            strokeColor: '#ffffff',
            lineWidth: 4,
            lineDash: [0, 4],
            lineTailCap: 'arrow-tail',
            lineHeadCap: 'arrow-head',
        }
    });

    var startMarker = new H.map.Marker(startPoint, {icon: orange});
    var endMarker = new H.map.Marker(endPoint, {icon: green});

    if (activities.length == 1)
        group.addObjects([routeOutline, routeLine, startMarker, endMarker]);
    else
        group.addObjects([routeOutline, routeLine]);
});

// Markers
markers.forEach(marker => {
    var marker = new H.map.Marker({
        lat: parseFloat(marker.dataset.lat),
        lng: parseFloat(marker.dataset.long)
    });

    group.addObject(marker);
});

map.addObject(group);

var bounds = group.getBoundingBox();
bounds.a -= 0.002;
bounds.f -= 0.002;
bounds.b += 0.002;
bounds.c += 0.002;
map.getViewModel().setLookAtData({
    bounds: bounds
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

// Marker on hover.
if (activities.length === 1) {
    var placedMarkers = [];

    document.querySelector("[data-container='speeds']").addEventListener(
        "speed.hover",
        function(event) {
            if (placedMarkers) {
                map.removeObjects(placedMarkers);
                placedMarkers = [];
            }

            var group = new H.map.Group();

            event.detail.forEach(function (index) {
                coord = activities[0].querySelectorAll("coord")[index];
                var marker = new H.map.Marker({
                    lat: parseFloat(coord.dataset.lat) - 0.0001,
                    lng: parseFloat(coord.dataset.long)
                }, {icon: blue});

                group.addObject(marker);
            })

            placedMarkers.push(group);
            map.addObject(group);
        },
        false,
    );
}
