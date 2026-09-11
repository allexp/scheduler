<script setup>
import { Link } from '@inertiajs/vue3';

// Компонент отображает ссылки Inertia и передаёт действие выхода layout-компоненту.
defineProps({
  page: {
    type: String,
    required: true,
  },
  user: {
    type: Object,
    required: true,
  },
  unreadCount: {
    type: Number,
    default: 0,
  },
});

defineEmits(['logout']);
</script>

<template>
  <aside>
    <div class="logo">
      <span>Р</span>
      <b>Расписание</b>
    </div>

    <nav>
      <Link
        href="/calendar"
        :class="{ active: page === 'calendar' }"
      >
        ▦ Календарь
      </Link>
      <Link
        href="/appointments"
        :class="{ active: page === 'appointments' }"
      >
        ☷ Список записей
      </Link>
      <Link
        href="/clients"
        :class="{ active: page === 'clients' }"
      >
        ♙ Клиенты
      </Link>
      <Link
        href="/appointments/create"
        :class="{ active: page === 'new' }"
      >
        ＋ Новая запись
      </Link>
      <Link
        href="/notifications"
        :class="{ active: page === 'notifications' }"
      >
        ◇ Уведомления
        <i v-if="unreadCount">{{ unreadCount }}</i>
      </Link>
      <Link
        v-if="user.role === 'admin'"
        href="/users"
        :class="{ active: page === 'users' }"
      >
        ♙ Пользователи
      </Link>
      <Link
        v-if="user.role === 'admin'"
        href="/history"
        :class="{ active: page === 'history' }"
      >
        ↺ История
      </Link>
    </nav>

    <div class="profile">
      <strong>{{ user.name }}</strong>
      <small>{{ user.role === 'admin' ? 'Администратор' : 'Сотрудник' }}</small>
      <button @click="$emit('logout')">Выйти</button>
    </div>
  </aside>
</template>

<style scoped>
aside {
  position: sticky;
  top: 0;
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 28px 18px;
  color: #dbe3ef;
  background: var(--color-sidebar);
}

.logo {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 0 10px 35px;
}

.logo span {
  display: grid;
  width: 40px;
  height: 40px;
  color: #102136;
  font-size: 23px;
  font-weight: 800;
  background: #63c6b4;
  border-radius: 13px;
  place-items: center;
}

.logo b {
  color: #fff;
  line-height: 1.05;
}

nav {
  display: grid;
  gap: 6px;
}

nav a {
  display: block;
  padding: 13px;
  color: #aeb9ca;
  font-weight: 600;
  text-align: left;
  background: transparent;
  border: 0;
  border-radius: 9px;
  text-decoration: none;
}

nav a.active,
nav a:hover {
  color: #fff;
  background: var(--color-sidebar-active);
}

nav i {
  float: right;
  padding: 1px 7px;
  color: var(--color-sidebar);
  font-style: normal;
  background: #63c6b4;
  border-radius: 99px;
}

.profile {
  display: grid;
  gap: 3px;
  padding: 20px 10px 0;
  margin-top: auto;
  border-top: 1px solid #334156;
}

.profile small {
  color: #8997aa;
}

.profile button {
  padding: 8px 0;
  color: #e8998d;
  text-align: left;
  background: none;
  border: 0;
}

@media (max-width: 900px) {
  aside {
    position: static;
    height: auto;
  }

  nav {
    grid-template-columns: repeat(3, 1fr);
  }

  .profile {
    display: none;
  }
}
</style>
