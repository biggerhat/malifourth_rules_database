import { ref, computed, type Ref } from 'vue';
import type { LinkableEntity } from '@/types/content';

export function useFilteredEntities(
    indices: Ref<LinkableEntity[]>,
    sections: Ref<LinkableEntity[]>,
    pages?: Ref<LinkableEntity[]>,
) {
    const indexFilterText = ref('');
    const filteredIndices = computed(() => {
        const filter = indexFilterText.value;
        if (!filter.length) return indices.value;
        return indices.value.filter(index =>
            index.title.toLowerCase().includes(filter.toLowerCase()),
        );
    });

    const sectionFilterText = ref('');
    const filteredSections = computed(() => {
        const filter = sectionFilterText.value;
        if (!filter.length) return sections.value;
        return sections.value.filter(section =>
            section.title.toLowerCase().includes(filter.toLowerCase()),
        );
    });

    const pageFilterText = ref('');
    const filteredPages = computed(() => {
        if (!pages) return [];
        const filter = pageFilterText.value;
        if (!filter.length) return pages.value;
        return pages.value.filter(page =>
            page.title.toLowerCase().includes(filter.toLowerCase()),
        );
    });

    return {
        indexFilterText,
        filteredIndices,
        sectionFilterText,
        filteredSections,
        pageFilterText,
        filteredPages,
    };
}
