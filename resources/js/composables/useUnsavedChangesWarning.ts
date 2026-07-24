import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

interface DirtyForm {
    isDirty: boolean;
}

/**
 * Warns before leaving a page with unsaved form changes, via the browser's
 * own beforeunload prompt (tab close/refresh) and Inertia's client-side
 * navigation. `markSubmitting()` must be called synchronously right before
 * the form's own submit visit (form.post/put/patch) — Inertia's global
 * 'before' event fires for that visit too, before `form.processing` flips
 * true, so there's no other reliable way to distinguish "saving" from
 * "navigating away".
 */
export function useUnsavedChangesWarning(form: DirtyForm) {
    let submitting = false;

    const markSubmitting = () => {
        submitting = true;
    };

    const handleBeforeUnload = (event: BeforeUnloadEvent) => {
        if (submitting || !form.isDirty) {
            return;
        }

        event.preventDefault();
    };

    const removeInertiaGuard = router.on('before', () => {
        if (submitting || !form.isDirty) {
            return true;
        }

        return window.confirm('You have unsaved changes. Are you sure you want to leave this page?');
    });

    onMounted(() => {
        window.addEventListener('beforeunload', handleBeforeUnload);
    });

    onUnmounted(() => {
        window.removeEventListener('beforeunload', handleBeforeUnload);
        removeInertiaGuard();
    });

    return { markSubmitting };
}
