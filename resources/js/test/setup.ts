import { config } from '@vue/test-utils';
import { h } from 'vue';
import { vi } from 'vitest';

// The real app registers Inertia's <Link>/<Head> as globals in app.ts (not per-component
// imports), and Ziggy's route() helper as a bare global function via the @routes Blade
// directive. Components written against that runtime assume both exist — stub them here
// so components can be mounted in isolation without pulling in Inertia/Ziggy themselves.
config.global.stubs = {
    Link: {
        props: ['href'],
        render() {
            return h('a', { href: this.href }, this.$slots.default?.());
        },
    },
    Head: true,
};

vi.stubGlobal('route', vi.fn((name: string) => `/mock/${name}`));
