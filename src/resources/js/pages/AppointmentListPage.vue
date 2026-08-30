<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import http from '../api/http.js';

const props = defineProps({
  refreshKey: {
    type: Number,
    default: 0,
  },
});

defineEmits(['select-appointment', 'select-client']);

const appointments = ref([]);
const loading = ref(false);
const error = ref('');
const pagination = ref(null);
const filters = reactive({
  date: '',
  search: '',
});
let searchTimer;

const statusLabels = {
  scheduled: 'Запланирована',
  completed: 'Завершена',
  cancelled: 'Отменена',
  no_show: 'Не пришёл',
};

// Загружает одну страницу записей с текущими фильтрами.
async function loadAppointments(page = 1) {
  loading.value = true;
  error.value = '';

  try {
    const { data } = await http.get('/appointments', {
      params: {
        paginate: 1,
        page,
        date: filters.date || undefined,
        search: filters.search.trim() || undefined,
      },
    });

    appointments.value = data.data;
    pagination.value = data;
  } catch (exception) {
    error.value = exception.response?.data?.message ?? 'Не удалось загрузить список записей.';
  } finally {
    loading.value = false;
  }
}

// Поиск запускается с небольшой задержкой, чтобы не отправлять запрос после каждого нажатия клавиши.
watch(
  () => filters.search,
  () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadAppointments(), 350);
  },
);

watch(
  () => filters.date,
  () => loadAppointments(),
);

// После изменения записи в карточке перечитывает текущую страницу списка.
watch(
  () => props.refreshKey,
  () => loadAppointments(pagination.value?.current_page ?? 1),
);

onMounted(() => loadAppointments());
onBeforeUnmount(() => clearTimeout(searchTimer));

function formatDate(date) {
  return new Date(date).toLocaleString('ru-RU', {
    dateStyle: 'short',
    timeStyle: 'short',
  });
}

function clearFilters() {
  filters.date = '';
  filters.search = '';
}
</script>

<template>
  <section class="appointment-list card">
    <div class="filters">
      <label>
        Дата записи
        <input
          v-model="filters.date"
          type="date"
        />
      </label>
      <label class="search-field">
        Поиск клиента
        <input
          v-model="filters.search"
          type="search"
          placeholder="Имя, фамилия или телефон"
        />
      </label>
      <button
        class="secondary clear-button"
        type="button"
        @click="clearFilters"
      >
        Сбросить
      </button>
    </div>

    <div
      v-if="error"
      class="error"
    >
      {{ error }}
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Дата и время</th>
            <th>Клиент</th>
            <th>Телефон</th>
            <th>Услуга</th>
            <th>Сотрудник</th>
            <th>Статус</th>
            <th><span class="visually-hidden">Действия</span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td
              colspan="7"
              class="state-cell"
            >
              Загрузка записей…
            </td>
          </tr>
          <tr v-else-if="!appointments.length">
            <td
              colspan="7"
              class="state-cell"
            >
              По заданным условиям записей не найдено.
            </td>
          </tr>
          <tr
            v-for="appointment in appointments"
            v-else
            :key="appointment.id"
            :class="`status-${appointment.status}`"
          >
            <td>{{ formatDate(appointment.starts_at) }}</td>
            <td>
              <button
                class="link-button client-link"
                @click="$emit('select-client', appointment.client)"
              >
                {{ appointment.client.full_name }}
              </button>
            </td>
            <td>{{ appointment.client.phone || '—' }}</td>
            <td>{{ appointment.service }}</td>
            <td>{{ appointment.employee.name }}</td>
            <td>
              <span
                class="status-badge"
                :class="appointment.status"
              >
                {{ statusLabels[appointment.status] ?? appointment.status }}
              </span>
            </td>
            <td class="actions-cell">
              <button
                class="secondary details-button"
                @click="$emit('select-appointment', appointment)"
              >
                Открыть
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <footer
      v-if="pagination?.last_page > 1"
      class="pagination"
    >
      <button
        class="secondary"
        :disabled="pagination.current_page === 1"
        @click="loadAppointments(pagination.current_page - 1)"
      >
        Назад
      </button>
      <span>Страница {{ pagination.current_page }} из {{ pagination.last_page }}</span>
      <button
        class="secondary"
        :disabled="pagination.current_page === pagination.last_page"
        @click="loadAppointments(pagination.current_page + 1)"
      >
        Далее
      </button>
    </footer>
  </section>
</template>

<style scoped>
.appointment-list {
  padding: 0;
  overflow: hidden;
}

.filters {
  display: grid;
  grid-template-columns: 210px minmax(280px, 1fr) auto;
  gap: 14px;
  align-items: end;
  padding: 20px 24px;
  border-bottom: 1px solid var(--color-border);
}

.filters label {
  display: grid;
  gap: 7px;
  color: #596677;
  font-size: 13px;
  font-weight: 700;
}

.filters input {
  width: 100%;
  padding: 10px 12px;
  background: #fff;
  border: 1px solid #d8dee5;
  border-radius: var(--radius-small);
}

.clear-button {
  margin-bottom: 1px;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid #edf0f2;
}

th {
  color: #738091;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.3px;
  background: #f8f9fa;
}

td {
  color: #3f4c5d;
  font-size: 14px;
}

tbody tr:hover {
  background: #f8fbfb;
}

.status-cancelled,
.status-completed {
  opacity: 0.75;
}

.link-button {
  padding: 0;
  color: var(--color-primary);
  font-weight: 700;
  text-align: left;
  background: none;
  border: 0;
}

.link-button:hover {
  text-decoration: underline;
}

.status-badge {
  display: inline-block;
  padding: 5px 9px;
  color: #266e66;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
  background: #ddf2ee;
  border-radius: 99px;
}

.status-badge.completed {
  color: #4f6088;
  background: #e9edf7;
}

.status-badge.cancelled,
.status-badge.no_show {
  color: #a3473c;
  background: #fbe8e5;
}

.actions-cell {
  width: 1%;
  white-space: nowrap;
}

.details-button {
  padding: 7px 11px;
  font-size: 12px;
}

.state-cell {
  padding: 45px;
  color: #8792a1;
  text-align: center;
}

.pagination {
  display: flex;
  gap: 14px;
  align-items: center;
  justify-content: flex-end;
  padding: 16px 24px;
}

.pagination span {
  color: #738091;
  font-size: 13px;
}

.pagination button:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (max-width: 800px) {
  .filters {
    grid-template-columns: 1fr;
  }

  .clear-button {
    justify-self: start;
  }
}
</style>
