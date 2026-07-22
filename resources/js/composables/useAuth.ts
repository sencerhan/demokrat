import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const roles = computed<string[]>(() => page.props.auth.roles ?? []);
    const isAuthenticated = computed(() => user.value !== null);

    const hasRole = (role: string): boolean => roles.value.includes(role);
    const hasAnyRole = (candidates: string[]): boolean =>
        candidates.some((role) => roles.value.includes(role));

    const isModerator = computed(() =>
        hasAnyRole(['moderator', 'admin', 'superadmin']),
    );
    const isAdmin = computed(() => hasAnyRole(['admin', 'superadmin']));

    return {
        user,
        roles,
        isAuthenticated,
        hasRole,
        hasAnyRole,
        isModerator,
        isAdmin,
    };
}
