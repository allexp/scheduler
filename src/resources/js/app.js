import { createInertiaApp } from '@inertiajs/vue3';
import CabinetLayout from './layouts/CabinetLayout.vue';

// Создаёт Inertia-приложение и назначает общий layout защищённым страницам кабинета.
createInertiaApp({
  resolve: async (name) => {
    const pages = import.meta.glob('./pages/**/*.vue');
    const page = await pages[`./pages/${name}.vue`]();

    if (name !== 'AuthPage') {
      page.default.layout ??= CabinetLayout;
    }

    return page;
  },
});
