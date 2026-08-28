<script setup>
// Страница только отображает уведомления; получение данных выполняется в App.vue.
defineProps({
  notifications: {
    type: Array,
    default: () => [],
  },
});

// Приводит серверную дату к локальному формату пользователя.
function formatDate(date) {
  return new Date(date).toLocaleString('ru-RU');
}
</script>

<template>
  <!-- Непрочитанные элементы выделяются до подтверждения пользователем. -->
  <section class="card">
    <div
      v-for="notification in notifications"
      :key="notification.id"
      class="notice"
      :class="{ unread: !notification.read_at }"
    >
      <span>◇</span>
      <div>
        <b>{{ notification.title }}</b>
        <p>{{ notification.message }}</p>
        <small>{{ formatDate(notification.created_at) }}</small>
      </div>
    </div>

    <div
      v-if="!notifications.length"
      class="empty-state"
    >
      Новых уведомлений нет
    </div>
  </section>
</template>
