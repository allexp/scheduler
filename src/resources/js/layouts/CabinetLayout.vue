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
