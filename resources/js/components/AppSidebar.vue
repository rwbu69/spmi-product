<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    Building2,
    CalendarRange,
    ChevronDown,
    ClipboardCheck,
    ClipboardList,
    Download,
    FileSearch,
    FileText,
    FolderOpen,
    LayoutGrid,
    Settings,
    Shield,
    Target,
    Upload,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
    useSidebar
} from '@/components/ui/sidebar';

interface NavChild {
    title: string;
    href: string;
    icon?: unknown;
}

interface NavGroup {
    title: string;
    icon: unknown;
    children: NavChild[];
    roles?: string[]; // roles that can see this group. Empty = all roles.
}

const openGroups = ref<Record<string, boolean>>({});
const page = usePage();

const userRoles = computed<string[]>(() => {
    const roles = (page.props.auth as any)?.roles ?? [];
    return Array.isArray(roles) ? roles : [roles];
});

const hasRole = (...roles: string[]) => roles.some(r => userRoles.value.includes(r));
const isAdmin = computed(() => hasRole('Admin'));
const isAuditor = computed(() => hasRole('Auditor'));
const isFakultas = computed(() => hasRole('Fakultas'));
const isAuditee = computed(() => hasRole('Auditee'));

const isActive = (href: string) => {
    if (page.url === href || page.url.startsWith(href + '?')) return true;
    return page.url.startsWith(href + '/');
};
const { state } = useSidebar();

// Flyout Logic
const hoveredGroup = ref<string | null>(null);
const flyoutStyle = ref({ top: '0px', left: '0px' });
let hideTimeout: ReturnType<typeof setTimeout> | null = null;

const showFlyout = (e: MouseEvent, groupTitle: string) => {
    if (hideTimeout) clearTimeout(hideTimeout);
    if (hoveredGroup.value !== groupTitle) {
        const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
        flyoutStyle.value = {
            top: `${rect.top}px`,
            left: `${rect.right + 8}px`
        };
        hoveredGroup.value = groupTitle;
    }
};

const hideFlyout = () => {
    hideTimeout = setTimeout(() => {
        hoveredGroup.value = null;
    }, 150);
};

const keepFlyout = () => {
    if (hideTimeout) clearTimeout(hideTimeout);
};

const toggleGroup = (title: string) => {
    openGroups.value[title] = !openGroups.value[title];
};

// ───────────────────────────────────────────────────────────
// Navigation definitions per role
// ───────────────────────────────────────────────────────────

// ADMIN nav groups
const adminNavGroups: NavGroup[] = [
    {
        title: 'Manajemen Referensi',
        icon: BookOpen,
        children: [
            { title: 'Tahun Periode', href: '/referensi/tahun-periode' },
            { title: 'Lembaga Akreditasi', href: '/referensi/lembaga-akreditasi' },
            { title: 'Auditee Pusat', href: '/referensi/auditee-pusat' },
            { title: 'Auditee', href: '/referensi/auditee' },
            { title: 'Unit Penunjang', href: '/referensi/unit-penunjang' },
        ],
    },
    {
        title: 'Manajemen Dokumen',
        icon: FolderOpen,
        children: [
            { title: 'Kategori Dokumen', href: '/dokumen/kategori' },
            { title: 'Jenis Dokumen', href: '/dokumen/jenis' },
            { title: 'Manajemen Dokumen', href: '/dokumen/manajemen' },
        ],
    },
    {
        title: 'Penetapan',
        icon: Target,
        children: [
            { title: 'Daftar Nilai Mutu', href: '/penetapan/nilai-mutu' },
            { title: 'Daftar Standar Mutu', href: '/penetapan/standar-mutu' },
        ],
    },
    {
        title: 'Pelaksanaan',
        icon: CalendarRange,
        children: [
            { title: 'Pengaturan Periode', href: '/pelaksanaan/pengaturan-periode' },
            { title: 'Target Nilai Mutu', href: '/pelaksanaan/target-nilai' },
            { title: 'Evaluasi Diri', href: '/pelaksanaan/evaluasi-diri' },
        ],
    },
    {
        title: 'Evaluasi AMI',
        icon: FileSearch,
        children: [
            { title: 'Manajemen Auditor', href: '/ami/auditor' },
            { title: 'Jenis Temuan', href: '/ami/jenis-temuan' },
            { title: 'Kategori Temuan', href: '/ami/kategori-temuan' },
            { title: 'Daftar Temuan Kolektif', href: '/ami/temuan-kolektif' },
            { title: 'Rekap Desk Evaluation', href: '/ami/rekap-desk-eval' },
            { title: 'Laporan AMI', href: '/ami/laporan-ami' },
        ],
    },
    {
        title: 'Pengendalian & Peningkatan',
        icon: ClipboardCheck,
        children: [
            { title: 'Daftar Temuan', href: '/pengendalian/daftar-temuan' },
            { title: 'Daftar Kesesuaian', href: '/pengendalian/kesesuaian' },
            { title: 'Draft Laporan RTM', href: '/pengendalian/draft-rtm' },
            { title: 'Upload Laporan RTM', href: '/pengendalian/upload-rtm' },
        ],
    },
    {
        title: 'Pengaturan Sistem',
        icon: Settings,
        children: [
            { title: 'Manajemen Pengguna', href: '/pengaturan/pengguna-backoffice' },
        ],
    },
];

// AUDITOR nav groups
const auditorNavGroups: NavGroup[] = [
    {
        title: 'Audit',
        icon: FileSearch,
        children: [
            { title: 'Desk Evaluation', href: '/auditor/desk-evaluation' },
            { title: 'Visitasi', href: '/auditor/visitasi' },
            { title: 'Download Laporan AMI', href: '/auditor/download-laporan-ami' },
            { title: 'Upload Laporan AMI', href: '/auditor/upload-laporan-ami' },
        ],
    },
    {
        title: 'Laporan',
        icon: BarChart3,
        children: [
            { title: 'Rekap Daftar Temuan', href: '/auditor/rekap-temuan' },
            { title: 'Rekap Daftar Kesesuaian', href: '/auditor/rekap-kesesuaian' },
        ],
    },
];

// AUDITEE nav groups
const auditeeNavGroups: NavGroup[] = [
    {
        title: 'Evaluasi Diri',
        icon: ClipboardList,
        children: [
            { title: 'Evaluasi Diri', href: '/pelaksanaan/evaluasi-diri' },
        ],
    },
    {
        title: 'Daftar Kesesuaian',
        icon: ClipboardCheck,
        children: [
            { title: 'Daftar Kesesuaian', href: '/pengendalian/kesesuaian' },
        ],
    },
    {
        title: 'Daftar Temuan',
        icon: FileText,
        children: [
            { title: 'Daftar Temuan', href: '/pengendalian/daftar-temuan' },
        ],
    },
    {
        title: 'Manajemen Dokumen',
        icon: FolderOpen,
        children: [
            { title: 'Manajemen Dokumen', href: '/dokumen/manajemen' },
        ],
    },
    {
        title: 'Laporan AMI',
        icon: FileSearch,
        children: [
            { title: 'Laporan AMI', href: '/ami/laporan-ami' },
        ],
    },
];

// FAKULTAS nav groups (same as admin minus pengaturan sistem)
const fakultasNavGroups: NavGroup[] = [
    {
        title: 'Evaluasi AMI',
        icon: FileSearch,
        children: [
            { title: 'Rekap Desk Evaluation', href: '/ami/rekap-desk-eval' },
            { title: 'Laporan AMI', href: '/ami/laporan-ami' },
        ],
    },
    {
        title: 'Pengendalian & Peningkatan',
        icon: ClipboardCheck,
        children: [
            { title: 'Daftar Temuan', href: '/pengendalian/daftar-temuan' },
            { title: 'Daftar Kesesuaian', href: '/pengendalian/kesesuaian' },
        ],
    },
    {
        title: 'Manajemen Dokumen',
        icon: FolderOpen,
        children: [
            { title: 'Manajemen Dokumen', href: '/dokumen/manajemen' },
        ],
    },
];

// Pick nav groups based on role
const navGroups = computed<NavGroup[]>(() => {
    if (isAdmin.value) return adminNavGroups;
    if (isAuditor.value) return auditorNavGroups;
    if (isAuditee.value) return auditeeNavGroups;
    if (isFakultas.value) return fakultasNavGroups;
    return adminNavGroups; // fallback
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r border-gray-200">
        <SidebarContent class="mt-4">
            <SidebarMenu>
                <!-- Beranda -->
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :tooltip="'Beranda'"
                        :isActive="isActive('/dashboard')"
                    >
                        <Link href="/dashboard">
                            <LayoutGrid />
                            <span>Beranda</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Grouped nav items -->
                <SidebarMenuItem v-for="group in navGroups" :key="group.title">
                    <SidebarMenuButton
                        @click="state === 'expanded' ? toggleGroup(group.title) : null"
                        @mouseenter="(e) => state === 'collapsed' && showFlyout(e, group.title)"
                        @mouseleave="hideFlyout"
                        :isActive="group.children.some(c => isActive(c.href))"
                    >
                        <component :is="group.icon" />
                        <span>{{ group.title }}</span>
                        <ChevronDown
                            class="ml-auto transition-transform duration-200"
                            :class="{ 'rotate-180': openGroups[group.title] }"
                        />
                    </SidebarMenuButton>

                    <SidebarMenuSub v-show="state === 'expanded' && openGroups[group.title]">
                        <SidebarMenuSubItem v-for="child in group.children" :key="child.href">
                            <SidebarMenuSubButton as-child :isActive="isActive(child.href)">
                                <Link :href="child.href">{{ child.title }}</Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    </SidebarMenuSub>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarContent>

        <SidebarFooter>
            <!-- User is now in the top header -->
        </SidebarFooter>
    </Sidebar>

    <!-- Flyout Menu (Teleport) -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="hoveredGroup && state === 'collapsed'"
                class="fixed z-[100] w-56 flex-col rounded-lg bg-[#2d2d2d] p-2 text-sm shadow-xl border border-[#3d3d3d] pointer-events-auto origin-left"
                :style="flyoutStyle"
                @mouseenter="keepFlyout"
                @mouseleave="hideFlyout"
            >
                <template v-for="group in navGroups" :key="group.title">
                    <div v-if="hoveredGroup === group.title" class="flex flex-col">
                        <div class="px-3 pb-2 pt-1 font-semibold text-white">{{ group.title }}</div>
                        <div class="h-px bg-[#3d3d3d] mb-1"></div>
                        <Link
                            v-for="child in group.children"
                            :key="child.href"
                            :href="child.href"
                            class="rounded-md px-3 py-2 text-gray-300 transition-colors hover:bg-[#3d3d3d] hover:text-white"
                            :class="isActive(child.href) ? 'text-white bg-[#4d4d4d] font-medium' : ''"
                        >
                            {{ child.title }}
                        </Link>
                    </div>
                </template>
            </div>
        </Transition>
    </Teleport>

    <slot />
</template>
