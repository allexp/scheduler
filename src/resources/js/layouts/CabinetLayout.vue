<script setup>
import AppSidebar from '../components/AppSidebar.vue';

// Layout получает состояние навигации от корневого компонента и не загружает данные самостоятельно.
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

// Заголовки страниц хранятся рядом с layout, потому что отображаются в общей шапке.
const pageTitles = {
  calendar: 'Календарь записей',
  clients: 'Клиенты',
  new: 'Новая запись',
  notifications: 'Уведомления',
  history: 'История изменений',
};
</script>

<template>
  <div class="layout">
    <AppSidebar
      :page="page"
      :user="user"
      :unread-count="unreadCount"
      @navigate="$emit('navigate', $event)"
      @logout="$emit('logout')"
    />

    <main>
      <header>
        <div>
          <small>РАБОЧЕЕ ПРОСТРАНСТВО</small>
          <h2>{{ pageTitles[page] }}</h2>
        </div>

        <button
          class="primary"
          @click="$emit('navigate', 'new')"
        >
          ＋ Добавить запись
        </button>
      </header>

      <slot />
    </main>
  </div>
</template>
