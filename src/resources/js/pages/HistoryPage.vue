<script setup>
defineProps({
  history: {
    type: Object,
    required: true,
  },
});

// Приводит серверную дату события к локальному формату пользователя.
function formatDate(date) {
  return new Date(date).toLocaleString('ru-RU');
}
</script>

<template>
  <section class="card">
    <h3>Журнал действий</h3>
    <div
      v-for="entry in history.data"
      :key="entry.id"
      class="history-entry"
    >
      <b>{{ entry.action }}</b>
      <span>{{ entry.user?.name || 'Система' }}</span>
      <small>{{ formatDate(entry.created_at) }}</small>
    </div>
    <div
      v-if="!history.data.length"
      class="empty-state"
    >
      История изменений пока пуста
    </div>
  </section>
</template>

<style scoped>
.history-entry {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 1px solid #edf0f2;
}

.history-entry small {
  color: var(--color-text-muted);
}
</style>
