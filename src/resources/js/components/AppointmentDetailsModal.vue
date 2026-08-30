<script setup>
import { reactive, ref, watch } from 'vue';

const props = defineProps({
  appointment: {
    type: Object,
    required: true,
  },
  clients: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
  error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'cancel', 'save']);
const editing = ref(false);
const form = reactive({});

const statusLabels = {
  scheduled: 'Запланирована',
  completed: 'Завершена',
  cancelled: 'Отменена',
  no_show: 'Клиент не пришёл',
};

// Преобразует серверную дату в значение datetime-local без сдвига часового пояса.
function toLocalDateTime(date) {
  const value = new Date(date);
  const offset = value.getTimezoneOffset() * 60_000;
  return new Date(value.getTime() - offset).toISOString().slice(0, 16);
}

// Заполняет форму актуальными значениями открытой записи.
function resetForm() {
  Object.assign(form, {
    client_id: props.appointment.client_id ?? props.appointment.client.id,
    employee_id: props.appointment.employee_id ?? props.appointment.employee.id,
    service: props.appointment.service,
    starts_at: toLocalDateTime(props.appointment.starts_at),
    ends_at: toLocalDateTime(props.appointment.ends_at),
    status: props.appointment.status,
    notes: props.appointment.notes ?? '',
  });
}

function startEditing() {
  resetForm();
  editing.value = true;
}

function stopEditing() {
  editing.value = false;
  resetForm();
}

function submit() {
  emit('save', { id: props.appointment.id, data: { ...form } });
}

watch(() => props.appointment, resetForm, { immediate: true });

// Форматирует серверную дату с учётом локального часового пояса пользователя.
function formatDate(date) {
  return new Date(date).toLocaleString('ru-RU', {
    dateStyle: 'long',
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
        aria-labelledby="appointment-title"
      >
        <header class="modal-header">
          <div>
            <small>ЗАПИСЬ №{{ appointment.id }}</small>
            <h2 id="appointment-title">{{ appointment.service }}</h2>
          </div>
          <button
            class="modal-close"
            type="button"
            aria-label="Закрыть карточку"
            @click="$emit('close')"
          >
            ×
          </button>
        </header>

        <form
          v-if="editing"
          class="appointment-edit-form"
          @submit.prevent="submit"
        >
          <div
            v-if="error"
            class="error edit-error"
          >
            {{ error }}
          </div>
          <div class="form-grid">
            <label>
              Клиент
              <select
                v-model="form.client_id"
                required
              >
                <option
                  v-for="client in clients"
                  :key="client.id"
                  :value="client.id"
                >
                  {{ client.full_name }}
                </option>
              </select>
            </label>
            <label>
              Сотрудник
              <select
                v-model="form.employee_id"
                required
              >
                <option
                  v-for="employee in employees"
                  :key="employee.id"
                  :value="employee.id"
                >
                  {{ employee.name }}
                </option>
              </select>
            </label>
            <label class="full-width">
              Услуга
              <input
                v-model="form.service"
                required
                maxlength="255"
              />
            </label>
            <label>
              Начало
              <input
                v-model="form.starts_at"
                type="datetime-local"
                required
              />
            </label>
            <label>
              Окончание
              <input
                v-model="form.ends_at"
                type="datetime-local"
                required
              />
            </label>
            <label class="full-width">
              Статус
              <select
                v-model="form.status"
                required
              >
                <option
                  v-for="(label, status) in statusLabels"
                  :key="status"
                  :value="status"
                >
                  {{ label }}
                </option>
              </select>
            </label>
            <label class="full-width">
              Заметки
              <textarea
                v-model="form.notes"
                rows="4"
                maxlength="5000"
              />
            </label>
          </div>
          <footer class="modal-actions">
            <button
              type="button"
              class="secondary"
              @click="stopEditing"
            >
              Отмена
            </button>
            <button
              type="submit"
              class="primary"
            >
              Сохранить изменения
            </button>
          </footer>
        </form>

        <div v-else>
          <div
            class="appointment-status"
            :class="appointment.status"
          >
            {{ statusLabels[appointment.status] ?? appointment.status }}
          </div>

          <dl class="appointment-details">
            <div>
              <dt>Клиент</dt>
              <dd>{{ appointment.client.full_name }}</dd>
            </div>
            <div>
              <dt>Телефон</dt>
              <dd>{{ appointment.client.phone || 'Не указан' }}</dd>
            </div>
            <div>
              <dt>Email</dt>
              <dd>{{ appointment.client.email || 'Не указан' }}</dd>
            </div>
            <div>
              <dt>Сотрудник</dt>
              <dd>{{ appointment.employee.name }}</dd>
            </div>
            <div>
              <dt>Начало</dt>
              <dd>{{ formatDate(appointment.starts_at) }}</dd>
            </div>
            <div>
              <dt>Окончание</dt>
              <dd>{{ formatDate(appointment.ends_at) }}</dd>
            </div>
          </dl>

          <section class="appointment-notes">
            <h3>Заметки</h3>
            <p>{{ appointment.notes || 'Заметок нет.' }}</p>
          </section>

          <section class="appointment-comments">
            <h3>Комментарии</h3>
            <div
              v-for="comment in appointment.comments"
              :key="comment.id"
              class="appointment-comment"
            >
              <b>{{ comment.user.name }}</b>
              <p>{{ comment.body }}</p>
            </div>
            <p
              v-if="!appointment.comments?.length"
              class="muted"
            >
              Комментариев пока нет.
            </p>
          </section>

          <footer class="modal-actions">
            <button
              type="button"
              class="secondary"
              @click="$emit('close')"
            >
              Закрыть
            </button>
            <button
              type="button"
              class="primary"
              @click="startEditing"
            >
              Редактировать
            </button>
            <button
              v-if="appointment.status === 'scheduled'"
              type="button"
              class="danger"
              @click="$emit('cancel', props.appointment)"
            >
              Отменить запись
            </button>
          </footer>
        </div>
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
  width: min(680px, 100%);
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
  margin-bottom: 18px;
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

.appointment-status {
  display: inline-block;
  padding: 7px 11px;
  color: #266e66;
  font-size: 13px;
  font-weight: 700;
  background: #ddf2ee;
  border-radius: 99px;
}

.appointment-status.cancelled {
  color: #a3473c;
  background: #fbe8e5;
}

.appointment-status.completed {
  color: #4f6088;
  background: #e9edf7;
}

.appointment-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin: 25px 0;
}

.appointment-details div {
  padding-bottom: 12px;
  border-bottom: 1px solid #e8ebee;
}

.appointment-details dt {
  margin-bottom: 5px;
  color: #7b8796;
  font-size: 12px;
}

.appointment-details dd {
  margin: 0;
  font-weight: 650;
}

.appointment-notes,
.appointment-comments {
  margin-top: 24px;
}

.appointment-notes h3,
.appointment-comments h3 {
  margin-bottom: 10px;
}

.appointment-notes p,
.appointment-comment p {
  color: #536071;
  line-height: 1.5;
}

.appointment-comment {
  padding: 12px 0;
  border-bottom: 1px solid #edf0f2;
}

.appointment-comment p {
  margin: 4px 0;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 28px;
}

.appointment-edit-form {
  margin-top: 8px;
}

.edit-error {
  margin-bottom: 18px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.form-grid label {
  display: grid;
  gap: 7px;
  color: #596677;
  font-size: 13px;
  font-weight: 700;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
  width: 100%;
  padding: 10px 12px;
  background: #fff;
  border: 1px solid #d8dee5;
  border-radius: var(--radius-small);
}

.form-grid textarea {
  resize: vertical;
}

.full-width {
  grid-column: 1 / -1;
}

@media (max-width: 600px) {
  .modal-backdrop {
    padding: 10px;
  }

  .modal-card {
    max-height: calc(100vh - 20px);
    padding: 20px;
  }

  .appointment-details {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: auto;
  }
}
</style>
