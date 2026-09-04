<script setup lang="ts">
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { Star } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    type: string;
    id: number;
    isFavorited: boolean;
}>();

const favorited = ref(props.isFavorited);
const pending = ref(false);

async function toggle() {
    if (pending.value) {
        return;
    }

    pending.value = true;
    const previous = favorited.value;
    favorited.value = !favorited.value;

    try {
        const { data } = await axios.post(route('favorites.toggle'), {
            type: props.type,
            id: props.id,
        });
        favorited.value = data.favorited;
    } catch {
        favorited.value = previous;
    } finally {
        pending.value = false;
    }
}
</script>

<template>
    <Button
        variant="outline"
        size="sm"
        class="print:hidden gap-1.5"
        :aria-pressed="favorited"
        :disabled="pending"
        @click="toggle"
    >
        <Star :class="['size-4', favorited ? 'fill-primary text-primary' : 'text-muted-foreground']" />
        {{ favorited ? 'Favorited' : 'Favorite' }}
    </Button>
</template>
