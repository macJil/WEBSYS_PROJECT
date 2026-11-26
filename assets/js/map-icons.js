// Form selections and saved records use the same fixed set of symbols.
function recordPin(layer, type) {
    const hazards = { flood: '≈', fire: '🔥', landslide: '◩', road_obstruction: '⛔', other: '⚠' };
    let style = 'incident';
    let symbol = Object.hasOwn(hazards, type) ? hazards[type] : '⚠';
    if (layer === 'households') {
        style = 'household';
        if (!Object.hasOwn(hazards, type)) symbol = '🏠';
    } else if (layer === 'centers') {
        style = type === 'closed' ? 'center-closed' : 'center-open';
        symbol = type === 'closed' ? '⊠' : '⌂';
    } else if (layer === 'relief_locations') {
        style = 'relief';
        symbol = type === 'storage' ? '▤' : type === 'both' ? '⊞' : '▣';
    }
    return L.divIcon({
        className: 'map-marker-icon',
        html: '<span class="map-symbol ' + style + '" aria-hidden="true">' + symbol + '</span>',
        iconSize: [28, 28],
        iconAnchor: [14, 34],
        popupAnchor: [0, -32],
    });
}
