<script setup>
import { reactive, ref } from 'vue';

// Ошибка приходит из корневого компонента, где централизованно обрабатываются ответы API.
const props = defineProps({
  error: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['submit']);

// Одна форма используется для входа и регистрации, чтобы не дублировать общие поля.
const registerMode = ref(false);
const form = reactive({
  name: '',
  email: 'admin@example.com',
  password: 'password',
  password_confirmation: 'password',
});

// Родитель определяет API-маршрут по переданному режиму формы.
function submit() {
  emit('submit', {
    registerMode: registerMode.value,
    form: { ...form },
  });
}
</script>

<template>
  <div class="auth-shell">
    <form
      class="auth-card"
      @submit.prevent="submit"
    >
      <div class="brand">Линия времени</div>
      <h1>{{ registerMode ? 'Регистрация' : 'Вход в кабинет' }}</h1>
      <p>Управление клиентами и расписанием</p>

      <input
        v-if="registerMode"
        v-model="form.name"
        placeholder="Имя"
        required
      />
      <input
        v-model="form.email"
        type="email"
        placeholder="Email"
        required
      />
      <input
        v-model="form.password"
        type="password"
        placeholder="Пароль"
        required
      />
      <input
        v-if="registerMode"
        v-model="form.password_confirmation"
        type="password"
        placeholder="Повторите пароль"
        required
      />

      <div
        v-if="props.error"
        class="error"
      >
        {{ props.error }}
      </div>

      <button>Продолжить</button>
      <a @click="registerMode = !registerMode">
        {{ registerMode ? 'У меня уже есть аккаунт' : 'Создать аккаунт' }}
      </a>
    </form>
  </div>
</template>
