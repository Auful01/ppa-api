// === Disable dark mode globally ===

// Remove dark class from <html>
document.documentElement.classList.remove('dark');

// Remove saved theme preference (vueuse-color-scheme)
localStorage.removeItem('vueuse-color-scheme');

// Monkey patch useDark to always return false
globalThis.useDark = () => {
    return {
        value: false
    };
};

// Optional: Prevent adding the "dark" class again
const originalAdd = DOMTokenList.prototype.add;
DOMTokenList.prototype.add = function (...args) {
    if (args.includes('dark')) return;
    return originalAdd.apply(this, args);
};


import './bootstrap';
import '../css/app.css';
import 'select2/dist/css/select2.min.css';
import 'select2';


import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';


dayjs.extend(utc);
dayjs.extend(timezone);

import DataTable from 'datatables.net-vue3';
import DataTablesLib from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5';
import jszip from 'jszip';

DataTable.use(DataTablesLib);
DataTablesLib.Buttons.jszip(jszip);

import $ from 'jquery';
window.$ = window.jQuery = $;
import 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

import { createApp, h, nextTick, onUnmounted } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueDatePicker from '@vuepic/vue-datepicker';
import VueMultiselect from 'vue-multiselect';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const scrollToPageBottom = () => {
    window.scrollTo({
        top: document.documentElement.scrollHeight,
        behavior: "smooth",
    });
};

const scrollToPageTop = () => {
    window.scrollTo({
        top: 100,
        behavior: "smooth",
    });
};

const watchTableScroll = () => {
    const tableContainer = $('#tableData_wrapper .dt-scroll-body');

    tableContainer.off("scroll").on("scroll", function () {
        const { scrollHeight, scrollTop, clientHeight } = tableContainer[0];
        // Check if the user has scrolled near the bottom of the table (within 1-2 pixels of the bottom)
        if (scrollHeight - scrollTop - clientHeight <= 2) {
            scrollToPageBottom();
        }

        // Check if the user has scrolled to the top of the table
        if (scrollTop === 0) {
            scrollToPageTop();
        }
    });
};

// Function to synchronize DataTable scroll head with body
const syncDataTableScroll = () => {
    nextTick(() => {
        const dataTableScrollBody = document.querySelector("#tableData_wrapper .dt-scroll-body");
        const dataTableScrollHead = document.querySelector("#tableData_wrapper .dt-scroll-head");

        if (dataTableScrollBody && dataTableScrollHead) {
            const onScroll = () => {
                dataTableScrollHead.scrollLeft = dataTableScrollBody.scrollLeft;
            };

            dataTableScrollBody.addEventListener("scroll", onScroll);

            // Cleanup when the component is unmounted
            onUnmounted(() => {
                dataTableScrollBody.removeEventListener("scroll", onScroll);
            });
        }
    });
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('DataTable', DataTable)
            .component('vueDatePicker', VueDatePicker)
            .component('VueMultiselect', VueMultiselect)
            .mount(el);

        setTimeout(() => {
            watchTableScroll();
            syncDataTableScroll();
        }, 1000);

        const observer = new MutationObserver(() => {
            watchTableScroll();
            syncDataTableScroll();
        });

        observer.observe(document.body, { childList: true, subtree: true });

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
