<script setup>
defineProps({
  client: {
    type: Object,
    required: true,
  },
});

defineEmits(['close', 'select-appointment']);

function formatDate(date) {
  return date ? new Date(date).toLocaleDateString('ru-RU') : 'Не указана';
}

function formatAppointmentDate(date) {
  return new Date(date).toLocaleString('ru-RU', {
    dateStyle: 'short',
    timeStyle: 'short',
  });
}
</script>

<template>
  <Teleport to="body">
    <div
      class="modal-backdrop"
      role="presentation"
      @click.self="$emit('close')"
    >
      <article
        class="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="client-title"
      >
        <header class="modal-header">
          <div>
            <small>КЛИЕНТ №{{ client.id }}</small>
            <h2 id="client-title">{{ client.full_name }}</h2>
          </div>
          <button
            class="modal-close"
            aria-label="Закрыть карточку"
            @click="$emit('close')"
          >
            ×
          </button>
        </header>

        <dl class="client-details">
          <div>
            <dt>Телефон</dt>
            <dd>{{ client.phone || 'Не указан' }}</dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{{ client.email || 'Не указан' }}</dd>
          </div>
          <div>
            <dt>Дата рождения</dt>
            <dd>{{ formatDate(client.birthday) }}</dd>
          </div>
        </dl>

        <section>
          <h3>Заметки</h3>
          <p class="notes">{{ client.notes || 'Заметок нет.' }}</p>
        </section>

        <section class="client-appointments">
          <h3>Записи клиента</h3>
          <button
            v-for="appointment in client.appointments"
            :key="appointment.id"
            class="appointment-row"
            @click="$emit('select-appointment', appointment)"
          >
            <span>
              <b>{{ appointment.service }}</b>
              <small>{{ formatAppointmentDate(appointment.starts_at) }}</small>
            </span>
            <span>Открыть →</span>
          </button>
          <p
            v-if="!client.appointments?.length"
            class="muted"
          >
            У клиента пока нет записей.
          </p>
        </section>

        <section class="client-comments">
          <h3>Комментарии</h3>
          <div
            v-for="comment in client.comments"
            :key="comment.id"
            class="comment"
          >
            <b>{{ comment.user.name }}</b>
            <p>{{ comment.body }}</p>
          </div>
          <p
            v-if="!client.comments?.length"
            class="muted"
          >
            Комментариев пока нет.
          </p>
        </section>

        <footer class="modal-actions">
          <button
            class="secondary"
            @click="$emit('close')"
          >
            Закрыть
          </button>
        </footer>
      </article>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  padding: 24px;
  background: #111a29a8;
  backdrop-filter: blur(3px);
  place-items: center;
}

.modal-card {
  width: min(700px, 100%);
  max-height: calc(100vh - 48px);
  padding: 26px;
  overflow: auto;
  background: #fff;
  border-radius: var(--radius-large);
  box-shadow: var(--shadow-modal);
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
}

.modal-header h2 {
  margin: 5px 0 0;
}

.modal-header small {
  color: #7c8795;
  font-weight: 700;
  letter-spacing: 1px;
}

.modal-close {
  width: 38px;
  height: 38px;
  color: #536071;
  font-size: 24px;
  background: #f0f2f4;
  border: 0;
  border-radius: 50%;
}

.client-details {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin: 22px 0;
}

.client-details div {
  padding: 14px;
  background: #f7f9fa;
  border-radius: var(--radius-small);
}

.client-details dt {
  margin-bottom: 5px;
  color: #7b8796;
  font-size: 12px;
}

.client-details dd {
  margin: 0;
  font-weight: 650;
}

section {
  margin-top: 24px;
}

section h3 {
  margin-bottom: 10px;
}

.notes,
.comment p {
  color: #536071;
  line-height: 1.5;
}

.appointment-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 12px 0;
  color: #425063;
  text-align: left;
  background: none;
  border: 0;
  border-bottom: 1px solid #edf0f2;
}

.appointment-row small {
  display: block;
  margin-top: 4px;
  color: #7c8795;
}

.appointment-row > span:last-child {
  color: var(--color-primary);
  font-size: 12px;
  font-weight: 700;
}

.comment {
  padding: 10px 0;
  border-bottom: 1px solid #edf0f2;
}

.comment p {
  margin: 4px 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 28px;
}

@media (max-width: 650px) {
  .modal-backdrop {
    padding: 10px;
  }

  .modal-card {
    max-height: calc(100vh - 20px);
    padding: 20px;
  }

  .client-details {
    grid-template-columns: 1fr;
  }
}
</style>
