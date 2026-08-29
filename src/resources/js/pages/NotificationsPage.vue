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

<style scoped>
.notice {
  display: flex;
  gap: 14px;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #edf0f2;
}

.notice > span {
  padding: 12px;
  background: #e4f3f0;
  border-radius: 50%;
}

.notice p {
  margin: 5px 0;
  color: #4f5d6d;
}

.notice small {
  color: #8792a1;
}

.notice.unread {
  padding: 14px 12px;
  margin: 0 -12px;
  background: #f4fbfa;
  border-radius: var(--radius-small);
}
</style>
