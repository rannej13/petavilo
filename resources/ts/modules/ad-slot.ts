import { defineBehavior } from '../core/behavior';

type AdQueue = Record<string, unknown>[];

interface AdWindow {
    adsbygoogle?: AdQueue;
}

export const adSlot = defineBehavior<HTMLElement>({
    name: 'ad-slot',
    selector: 'ins.adsbygoogle[data-ad-slot]',
    mount(slot) {
        if (slot.dataset['adMounted'] === '1') {
            return;
        }

        slot.dataset['adMounted'] = '1';

        const host = window as unknown as AdWindow;
        const queue: AdQueue = host.adsbygoogle ?? [];

        host.adsbygoogle = queue;
        queue.push({});
    },
});
