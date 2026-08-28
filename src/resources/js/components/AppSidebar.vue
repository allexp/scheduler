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
