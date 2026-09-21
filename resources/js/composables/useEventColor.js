/*
 * One event colour resolver: `event.color` holds a V-Calendar palette NAME
 * (e.g. "teal"), not a hex — several of those names are also CSS colour
 * keywords but at different shades (CSS `green` is #008000, the palette's
 * #22c55e), so rendering the name directly as `backgroundColor` drifts the
 * swatch away from the bar it is meant to match. Resolve through this
 * composable instead:
 *
 *   <span :style="{ backgroundColor: eventHex(event) }">
 */
export function useEventColor() {
    // Selectable event colours (V-Calendar palette names + a swatch hex to show).
    const COLORS = [
        { name: "orange", hex: "#f97316" },
        { name: "red", hex: "#ef4444" },
        { name: "yellow", hex: "#eab308" },
        { name: "green", hex: "#22c55e" },
        { name: "teal", hex: "#14b8a6" },
        { name: "blue", hex: "#3b82f6" },
        { name: "indigo", hex: "#6366f1" },
        { name: "purple", hex: "#a855f7" },
        { name: "pink", hex: "#ec4899" },
        { name: "gray", hex: "#6b7280" },
    ];

    const COLOR_HEX = Object.fromEntries(COLORS.map((c) => [c.name, c.hex]));

    // Deterministic per-type fallback colour when an event has no chosen colour.
    const colorFor = (type) =>
        COLORS[[...(type ?? "")].reduce((sum, c) => sum + c.charCodeAt(0), 0) % COLORS.length].name;

    // The swatch an event renders as, resolved through COLORS rather than used
    // as a raw CSS colour name.
    const eventHex = (event) =>
        COLOR_HEX[event.color ?? colorFor(event.event_type)] ?? COLOR_HEX.gray;

    return { COLORS, colorFor, eventHex };
}
