import L from "leaflet";
import "leaflet/dist/leaflet.css";

/*
 * Shared OSRS Leaflet setup. Tiles come from mejrs/layers_osrs — the live OSRS
 * Wiki map data, re-rendered each game update (tracking master keeps it
 * current). With L.CRS.Simple the map's coordinate space *is* game coordinates,
 * so a position is simply [y, x] (see gameToLatLng) with no transform.
 */
const TILE_URL = "https://raw.githubusercontent.com/mejrs/layers_osrs/refs/heads/master/mapsquares/-1/{z}/{plane}_{x}_{y}.png";

// Custom tile layer matching the wiki/mejrs scheme: filename is plane_x_y with
// y flipped as -(1 + coords.y).
const OsrsTileLayer = L.TileLayer.extend({
    getTileUrl(coords) {
        return L.Util.template(this._url, {
            z: coords.z,
            plane: this.options.plane ?? 0,
            x: coords.x,
            y: -(1 + coords.y),
        });
    },
});

export const gameToLatLng = (x, y) => L.latLng(y, x);

/**
 * Create a Leaflet map wired to the OSRS tiles in the given element.
 * `mapOptions` is merged into the L.map options (e.g. zoomControl, dragging).
 */
export function createOsrsMap(element, mapOptions = {}) {
    const map = L.map(element, {
        crs: L.CRS.Simple,
        minZoom: -4,
        maxZoom: 8,
        ...mapOptions,
    });

    new OsrsTileLayer(TILE_URL, {
        plane: 0,
        minZoom: -4,
        maxNativeZoom: 4,
        maxZoom: 8,
        noWrap: true,
        attribution: 'Map data © <a href="https://oldschool.runescape.wiki">OSRS Wiki</a> (mejrs)',
    }).addTo(map);

    return map;
}
