<script setup>
import { reactive } from 'vue';

// Список клиентов загружается на уровне App.vue и передаётся странице через props.
defineProps({
  clients: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['create', 'select']);
const form = reactive(createEmptyForm());

// Возвращает независимое начальное состояние формы клиента.
function createEmptyForm() {
  return {
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    birthday: '',
    notes: '',
  };
}

// Передаёт заполненные данные родителю и очищает локальную форму.
function submit() {
  emit('create', { ...form });
  Object.assign(form, createEmptyForm());
}
</script>

<template>
  <section class="split">
    <div class="card">
      <h3>Карточки клиентов</h3>

      <button
        v-for="client in clients"
        :key="client.id"
        class="client"
        type="button"
        @click="emit('select', client)"
      >
        <div class="avatar">{{ client.first_name[0] }}{{ client.last_name[0] }}</div>
        <div>
          <b>{{ client.full_name }}</b>
          <small>
            {{ client.phone || 'Телефон не указан' }} · {{ client.email || 'Email не указан' }}
          </small>
        </div>
      </button>

      <div
        v-if="!clients.length"
        class="empty-state"
      >
        Клиентов пока нет
      </div>
    </div>

    <form
      class="card form"
      @submit.prevent="submit"
    >
      <h3>Новый клиент</h3>
      <div class="cols">
        <input
          v-model="form.first_name"
          placeholder="Имя"
          required
        />
        <input
          v-model="form.last_name"
          placeholder="Фамилия"
          required
        />
      </div>
      <input
        v-model="form.phone"
        placeholder="Телефон"
      />
      <input
        v-model="form.email"
        type="email"
        placeholder="Email"
      />
      <input
        v-model="form.birthday"
        type="date"
      />
      <textarea
        v-model="form.notes"
        placeholder="Заметки"
      />
      <button class="primary">Сохранить клиента</button>
    </form>
  </section>
</template>

<style scoped>
.split {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 18px;
}

.client {
  display: flex;
  width: 100%;
  gap: 14px;
  align-items: center;
  padding: 14px 0;
  color: inherit;
  text-align: left;
  background: none;
  border: 0;
  border-bottom: 1px solid #edf0f2;
}

.client:hover {
  background: #f7f9fa;
}

.client small {
  display: block;
  margin-top: 4px;
  color: #7c8795;
}

.avatar {
  display: grid;
  width: 42px;
  height: 42px;
  color: #267269;
  font-weight: 800;
  background: #dff2ee;
  border-radius: 12px;
  place-items: center;
}

@media (max-width: 900px) {
  .split {
    grid-template-columns: 1fr;
  }
}
</style>
