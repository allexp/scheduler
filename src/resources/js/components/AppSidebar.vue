<script setup>
// Компонент отвечает только за отображение навигации и передаёт действия родителю через события.
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

defineEmits(['navigate', 'logout']);
</script>

<template>
  <aside>
    <div class="logo">
      <span>Л</span>
      <b>Линия<br />времени</b>
    </div>

    <nav>
      <button
        :class="{ active: page === 'calendar' }"
        @click="$emit('navigate', 'calendar')"
      >
        ▦ Календарь
      </button>
      <button
        :class="{ active: page === 'clients' }"
        @click="$emit('navigate', 'clients')"
      >
        ♙ Клиенты
      </button>
      <button
        :class="{ active: page === 'new' }"
        @click="$emit('navigate', 'new')"
      >
        ＋ Новая запись
      </button>
      <button
        :class="{ active: page === 'notifications' }"
        @click="$emit('navigate', 'notifications')"
      >
        ◇ Уведомления
        <i v-if="unreadCount">{{ unreadCount }}</i>
      </button>
      <button
        v-if="user.role === 'admin'"
        :class="{ active: page === 'history' }"
        @click="$emit('navigate', 'history')"
      >
        ↺ История
      </button>
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

nav button {
  padding: 13px;
  color: #aeb9ca;
  font-weight: 600;
  text-align: left;
  background: transparent;
  border: 0;
  border-radius: 9px;
}

nav button.active,
nav button:hover {
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
