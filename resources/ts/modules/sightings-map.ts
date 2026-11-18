import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface SightingPoint {
    lat: number;
    lng: number;
    when: string;
    place: string;
}

function readPoints(element: HTMLElement): SightingPoint[] {
    try {
        const parsed: unknown = JSON.parse(element.dataset['points'] ?? '[]');

        return Array.isArray(parsed) ? (parsed as SightingPoint[]) : [];
    } catch {
        return [];
    }
}

function escapeHtml(value: string): string {
    const div = document.createElement('div');
    div.textContent = value;

    return div.innerHTML;
}

export function installSightingsMaps(root: ParentNode = document): void {
    root.querySelectorAll<HTMLElement>('[data-sightings-map]').forEach((element) => {
        if (element.dataset['mapReady'] === '1') {
            return;
        }

        element.dataset['mapReady'] = '1';

        const points = readPoints(element);
        const centerLat = Number(element.dataset['lat'] ?? '');
        const centerLng = Number(element.dataset['lng'] ?? '');
        const hasCenter = Number.isFinite(centerLat) && Number.isFinite(centerLng) && (centerLat !== 0 || centerLng !== 0);

        const map = L.map(element, { scrollWheelZoom: false });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        const bounds: L.LatLngExpression[] = [];

        points.forEach((point) => {
            const marker = L.circleMarker([point.lat, point.lng], { radius: 9, color: '#b45309', weight: 2, fillColor: '#f59e0b', fillOpacity: 0.7 });
            marker.bindPopup(`<strong>${escapeHtml(point.when)}</strong><br>${escapeHtml(point.place)}`);
            marker.addTo(map);
            bounds.push([point.lat, point.lng]);
        });

        if (hasCenter) {
            L.circleMarker([centerLat, centerLng], { radius: 10, color: '#1d1d1f', weight: 2, fillColor: '#ffffff', fillOpacity: 0.9 })
                .bindPopup('Last seen by the owner')
                .addTo(map);
            bounds.push([centerLat, centerLng]);
        }

        if (bounds.length > 1) {
            map.fitBounds(L.latLngBounds(bounds), { padding: [30, 30], maxZoom: 15 });
        } else if (bounds.length === 1) {
            map.setView(bounds[0] as L.LatLngExpression, 14);
        } else {
            map.setView([54.5, -3.0], 5);
        }

        const form = element.dataset['pickForm'] ? document.getElementById(element.dataset['pickForm']) : null;

        if (form === null) {
            return;
        }

        const lat = form.querySelector<HTMLInputElement>('input[name="latitude"]');
        const lng = form.querySelector<HTMLInputElement>('input[name="longitude"]');
        const status = form.querySelector<HTMLElement>('[data-pick-status]');
        let picked: L.CircleMarker | null = null;

        const setPick = (latitude: number, longitude: number): void => {
            if (lat === null || lng === null) {
                return;
            }

            lat.value = latitude.toFixed(6);
            lng.value = longitude.toFixed(6);

            if (picked === null) {
                picked = L.circleMarker([latitude, longitude], { radius: 10, color: '#0f766e', weight: 3, fillColor: '#14b8a6', fillOpacity: 0.8 }).addTo(map);
            } else {
                picked.setLatLng([latitude, longitude]);
            }

            if (status !== null) {
                status.textContent = 'Pin placed. You can tap the map again to move it.';
            }
        };

        map.on('click', (event: L.LeafletMouseEvent) => setPick(event.latlng.lat, event.latlng.lng));

        form.querySelector<HTMLButtonElement>('[data-use-location]')?.addEventListener('click', () => {
            if (!('geolocation' in navigator)) {
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setPick(position.coords.latitude, position.coords.longitude);
                    map.setView([position.coords.latitude, position.coords.longitude], 16);
                },
                () => {
                    if (status !== null) {
                        status.textContent = 'Could not get your location. Tap the map instead.';
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 },
            );
        });
    });
}
