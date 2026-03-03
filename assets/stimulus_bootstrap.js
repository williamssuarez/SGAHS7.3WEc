import { startStimulusApp } from '@symfony/stimulus-bridge';

import { TurboGlobal } from "@hotwired/turbo";
Turbo.session.drive = false; // This disables the AJAX page transitions globally

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
