<script setup>
import { computed, onMounted, ref } from 'vue';
import http, { TOKEN_STORAGE_KEY } from './api/http.js';
import AppointmentDetailsModal from './components/AppointmentDetailsModal.vue';
import CabinetLayout from './layouts/CabinetLayout.vue';
import AppointmentCreatePage from './pages/AppointmentCreatePage.vue';
import AuthPage from './pages/AuthPage.vue';
import CalendarPage from './pages/CalendarPage.vue';
import ClientsPage from './pages/ClientsPage.vue';
import HistoryPage from './pages/HistoryPage.vue';
import NotificationsPage from './pages/NotificationsPage.vue';

// Корневой компонент хранит общее состояние кабинета и координирует дочерние страницы.
const user = ref(null);
const page = ref('calendar');
const error = ref('');
const clients = ref([]);
const appointments = ref([]);
const employees = ref([]);
const notifications = ref([]);
const stats = ref({});
const selectedAppointment = ref(null);
const appointmentFormResetKey = ref(0);

// Количество непрочитанных уведомлений используется для индикатора в боковом меню.
const unreadCount = computed(
  () => notifications.value.filter((notification) => !notification.read_at).length,
);

// Преобразует разные форматы ошибок Laravel API в сообщение для пользователя.
function extractError(exception) {
  return (
    Object.values(exception.response?.data?.errors ?? {}).flat()[0] ??
    exception.response?.data?.message ??
    'Ошибка запроса'
  );
}

// Выполняет API-операцию и централизованно обновляет состояние ошибки.
async function executeRequest(callback) {
  error.value = '';

  try {
    return await callback();
  } catch (exception) {
    error.value = extractError(exception);
    throw exception;
  }
}

// Авторизует или регистрирует пользователя в зависимости от режима формы.
async function authenticate({ registerMode, form }) {
  await executeRequest(async () => {
    const endpoint = registerMode ? '/register' : '/login';
    const { data } = await http.post(endpoint, form);

    localStorage.setItem(TOKEN_STORAGE_KEY, data.token);
    user.value = data.user;
    await loadWorkspace();
  });
}

// Завершает серверную сессию API и удаляет локальный токен доступа.
async function logout() {
  await http.post('/logout');
  localStorage.removeItem(TOKEN_STORAGE_KEY);
  user.value = null;
}

// Загружает данные, необходимые всем основным страницам рабочего пространства.
async function loadWorkspace() {
  const [meResponse, clientsResponse, appointmentsResponse, employeesResponse, statsResponse] =
    await Promise.all([
      http.get('/me'),
      http.get('/clients'),
      http.get('/appointments'),
      http.get('/employees'),
      http.get('/dashboard'),
    ]);

  const notificationsResponse = await http.get('/notifications');

  user.value = meResponse.data;
  clients.value = clientsResponse.data.data;
  appointments.value = appointmentsResponse.data;
  employees.value = employeesResponse.data;
  stats.value = statsResponse.data;
  notifications.value = notificationsResponse.data.data;
}

// Создаёт карточку клиента и синхронизирует состояние кабинета с сервером.
async function createClient(form) {
  await executeRequest(async () => {
    await http.post('/clients', form);
    await loadWorkspace();
    page.value = 'clients';
  });
}

// Создаёт запись клиента и возвращает пользователя к календарю.
async function createAppointment(form) {
  await executeRequest(async () => {
    await http.post('/appointments', form);
    await loadWorkspace();
    appointmentFormResetKey.value += 1;
    page.value = 'calendar';
  });
}

// Загружает полную карточку выбранной записи вместе с комментариями.
async function openAppointment(appointment) {
  await executeRequest(async () => {
    const { data } = await http.get(`/appointments/${appointment.id}`);
    selectedAppointment.value = data;
  });
}

// Переводит выбранную запись в статус «отменена».
async function cancelAppointment(appointment) {
  await executeRequest(async () => {
    await http.patch(`/appointments/${appointment.id}`, {
      status: 'cancelled',
    });
    await loadWorkspace();
    selectedAppointment.value = null;
  });
}

// При открытии приложения восстанавливает авторизацию по сохранённому токену.
onMounted(async () => {
  if (!localStorage.getItem(TOKEN_STORAGE_KEY)) {
    return;
  }

  try {
    await loadWorkspace();
  } catch {
    localStorage.removeItem(TOKEN_STORAGE_KEY);
    user.value = null;
  }
});
</script>

<template>
  <AuthPage
    v-if="!user"
    :error="error"
    @submit="authenticate"
  />

  <CabinetLayout
    v-else
    :page="page"
    :user="user"
    :unread-count="unreadCount"
    @navigate="page = $event"
    @logout="logout"
  >
    <div
      v-if="error"
      class="error banner"
    >
      {{ error }}
    </div>

    <CalendarPage
      v-if="page === 'calendar'"
      :appointments="appointments"
      :stats="stats"
      @select="openAppointment"
    />
    <ClientsPage
      v-else-if="page === 'clients'"
      :clients="clients"
      @create="createClient"
    />
    <AppointmentCreatePage
      v-else-if="page === 'new'"
      :clients="clients"
      :employees="employees"
      :reset-key="appointmentFormResetKey"
      @create="createAppointment"
    />
    <NotificationsPage
      v-else-if="page === 'notifications'"
      :notifications="notifications"
    />
    <HistoryPage v-else-if="page === 'history'" />

    <AppointmentDetailsModal
      v-if="selectedAppointment"
      :appointment="selectedAppointment"
      @close="selectedAppointment = null"
      @cancel="cancelAppointment"
    />
  </CabinetLayout>
</template>
