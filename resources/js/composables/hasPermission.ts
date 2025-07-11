import { usePage } from '@inertiajs/vue3';
const page = usePage();


export function hasPermission(searchPermission: string): boolean {
    return page.props.auth.permissions.some(item => item.toLowerCase() === searchPermission.toLowerCase());
}
