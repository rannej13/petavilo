import { defineBehavior } from '../core/behavior';

interface FundingChoices {
    callbackQueue?: unknown[];
    showRevocationMessage?: () => void;
}

interface ConsentWindow {
    googlefc?: FundingChoices;
}

export const consentSettings = defineBehavior<HTMLButtonElement>({
    name: 'consent-settings',
    selector: '[data-consent-settings]',
    mount(button) {
        const host = window as unknown as ConsentWindow;
        const choices: FundingChoices = host.googlefc ?? {};
        const queue: unknown[] = choices.callbackQueue ?? [];

        choices.callbackQueue = queue;
        host.googlefc = choices;

        queue.push(() => {
            button.hidden = false;
        });

        button.addEventListener('click', () => {
            host.googlefc?.showRevocationMessage?.();
        });
    },
});
