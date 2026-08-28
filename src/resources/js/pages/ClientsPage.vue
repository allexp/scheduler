<script setup>
import { reactive } from 'vue';

// Список клиентов загружается на уровне App.vue и передаётся странице через props.
defineProps({
  clients: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['create']);
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

      <div
        v-for="client in clients"
        :key="client.id"
        class="client"
      >
        <div class="avatar">{{ client.first_name[0] }}{{ client.last_name[0] }}</div>
        <div>
          <b>{{ client.full_name }}</b>
          <small>
            {{ client.phone || 'Телефон не указан' }} · {{ client.email || 'Email не указан' }}
          </small>
        </div>
      </div>

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
