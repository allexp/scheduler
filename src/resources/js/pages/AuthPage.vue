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
  remember: false,
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
      <div class="brand">Расписание</div>
      <h1>{{ registerMode ? 'Регистрация' : 'Вход в кабинет' }}</h1>

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

      <label
        v-if="!registerMode"
        class="remember"
      >
        <input
          v-model="form.remember"
          type="checkbox"
        />
        <span>Запомнить меня</span>
      </label>

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

<style scoped>
.auth-shell {
  display: grid;
  min-height: 100vh;
  background: radial-gradient(circle at top right, #d8f1eb, transparent 38%), #f3f6f7;
  place-items: center;
}

.auth-card {
  display: grid;
  gap: 14px;
  width: min(420px, 92vw);
  padding: 35px;
  background: #fff;
  border-radius: var(--radius-large);
  box-shadow: 0 20px 60px #1b3e4b1c;
}

.auth-card h1 {
  margin: 5px 0 0;
}

.auth-card p {
  margin: 0 0 12px;
  color: var(--color-text-muted);
}

.auth-card input {
  width: 100%;
  padding: 11px 12px;
  background: #fff;
  border: 1px solid #d8dee5;
  border-radius: var(--radius-small);
}

.remember {
  display: flex;
  gap: 9px;
  align-items: center;
  color: var(--color-text-muted);
  cursor: pointer;
}

.remember input {
  width: auto;
  margin: 0;
}

.auth-card button {
  padding: 12px 18px;
  color: #fff;
  font-weight: 700;
  background: var(--color-primary);
  border: 0;
  border-radius: 9px;
}

.auth-card a {
  color: var(--color-primary);
  text-align: center;
}

.brand {
  color: var(--color-primary);
  font-weight: 800;
  letter-spacing: 1px;
}
</style>
