import '../css/app.css';

import { startBehaviors, type Behavior } from './core/behavior';
import { characterCounter, confirmAction, dependentSelect, imagePreview, ratingInput, revealPanel } from './modules/forms';
import { adSlot } from './modules/ad-slot';
import { consentSettings } from './modules/consent-settings';
import { favoriteToggle } from './modules/favorite-toggle';
import { filterDrawer, listingFilters, viewModeToggle } from './modules/listing-filters';
import { inboxBadge, inboxPane, inboxThread } from './modules/inbox';
import { listingGallery } from './modules/gallery';
import { locationPicker } from './modules/location-picker';
import { contactReveal, shareAction } from './modules/reveal';
import { disclosureGroup, navigationDrawer, searchSuggest, stickyHeader } from './modules/navigation';
import { installVideoCompressor } from './modules/video-compress';
import { renderQrCodes } from './modules/qr-code';

const behaviors: readonly Behavior<never>[] = [
    navigationDrawer,
    disclosureGroup,
    stickyHeader,
    searchSuggest,
    locationPicker,
    listingFilters,
    filterDrawer,
    viewModeToggle,
    listingGallery,
    favoriteToggle,
    contactReveal,
    shareAction,
    inboxThread,
    inboxPane,
    inboxBadge,
    characterCounter,
    dependentSelect,
    imagePreview,
    confirmAction,
    ratingInput,
    revealPanel,
    adSlot,
    consentSettings,
];

function boot(): void {
    installVideoCompressor();
    renderQrCodes();
    startBehaviors(behaviors);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}

document.addEventListener('livewire:navigated', boot);
