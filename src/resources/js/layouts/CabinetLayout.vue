<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebar from '../components/AppSidebar.vue';

const inertiaPage = usePage();

// Заголовки страниц хранятся рядом с layout, потому что отображаются в общей шапке.
const pageMeta = {
  CalendarPage: ['calendar', 'Календарь записей'],
  AppointmentListPage: ['appointments', 'Список записей'],
  ClientsPage: ['clients', 'Клиенты'],
  AppointmentCreatePage: ['new', 'Новая запись'],
  NotificationsPage: ['notifications', 'Уведомления'],
  HistoryPage: ['history', 'История изменений'],
  UsersPage: ['users', 'Пользователи'],
};

const page = computed(() => pageMeta[inertiaPage.component]?.[0] ?? 'calendar');
const title = computed(() => pageMeta[inertiaPage.component]?.[1] ?? 'Расписание');
const user = computed(() => inertiaPage.props.auth.user);
const unreadCount = computed(() => inertiaPage.props.unreadNotificationsCount ?? 0);

// Завершает сессию через Laravel и переводит пользователя на форму входа.
function logout() {
  router.post('/logout');
}
</script>

<template>
  <Head :title="title" />
  <div class="layout">
    <AppSidebar
      :page="page"
      :user="user"
      :unread-count="unreadCount"
      @logout="logout"
    />

    <main>
      <header>
        <div>
          <small>РАБОЧЕЕ ПРОСТРАНСТВО</small>
          <h2>{{ title }}</h2>
        </div>

        <Link
          href="/appointments/create"
          class="primary"
        >
          ＋ Добавить запись
        </Link>
      </header>

      <slot />
    </main>
  </div>
</template>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: 240px 1fr;
  min-height: 100vh;
}

main {
  width: 100%;
  max-width: 1500px;
  padding: 35px 44px;
  margin: auto;
}

header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
}

header h2 {
  margin: 5px 0;
  font-size: 28px;
}

header small {
  color: #8894a5;
  font-weight: 700;
  letter-spacing: 1.5px;
}

@media (max-width: 900px) {
  .layout {
    grid-template-columns: 1fr;
  }

  main {
    padding: 24px 15px;
  }
}
</style>
