import { mount } from '@vue/test-utils';
import { describe, expect, it, vi, beforeEach } from 'vitest';
import axios from 'axios';
import FavoriteButton from './FavoriteButton.vue';

vi.mock('axios', () => ({
    default: { post: vi.fn() },
}));

describe('FavoriteButton', () => {
    beforeEach(() => {
        vi.mocked(axios.post).mockReset();
    });

    it('renders the unfavorited state from props', () => {
        const wrapper = mount(FavoriteButton, {
            props: { type: 'page', id: 42, isFavorited: false },
        });

        expect(wrapper.text()).toContain('Favorite');
        expect(wrapper.text()).not.toContain('Favorited');
        expect(wrapper.attributes('aria-pressed')).toBe('false');
    });

    it('renders the favorited state from props', () => {
        const wrapper = mount(FavoriteButton, {
            props: { type: 'page', id: 42, isFavorited: true },
        });

        expect(wrapper.text()).toContain('Favorited');
        expect(wrapper.attributes('aria-pressed')).toBe('true');
    });

    it('optimistically toggles and posts the right payload on click', async () => {
        vi.mocked(axios.post).mockResolvedValueOnce({ data: { favorited: true } });

        const wrapper = mount(FavoriteButton, {
            props: { type: 'section', id: 7, isFavorited: false },
        });

        await wrapper.find('button').trigger('click');

        expect(axios.post).toHaveBeenCalledWith('/mock/favorites.toggle', { type: 'section', id: 7 });
        await vi.waitFor(() => expect(wrapper.text()).toContain('Favorited'));
    });

    it('rolls back the optimistic toggle if the request fails', async () => {
        vi.mocked(axios.post).mockRejectedValueOnce(new Error('network error'));

        const wrapper = mount(FavoriteButton, {
            props: { type: 'page', id: 1, isFavorited: false },
        });

        await wrapper.find('button').trigger('click');
        await vi.waitFor(() => expect(wrapper.text()).toContain('Favorite'));
        expect(wrapper.text()).not.toContain('Favorited');
    });
});
