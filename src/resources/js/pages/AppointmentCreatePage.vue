<script setup>
import { reactive, watch } from 'vue';

// Справочники клиентов и сотрудников загружаются корневым компонентом.
const props = defineProps({
  clients: {
    type: Array,
    default: () => [],
  },
  employees: {
    type: Array,
    default: () => [],
  },
  resetKey: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['create']);
const form = reactive(createEmptyForm());

// Возвращает начальные значения полей новой записи.
function createEmptyForm() {
  return {
    client_id: '',
    employee_id: '',
    service: '',
    starts_at: '',
    ends_at: '',
    notes: '',
  };
}

// Передаёт копию данных родителю, сохраняя локальные поля до успешного ответа API.
function submit() {
  emit('create', { ...form });
}

// Родитель изменяет ключ только после успешного создания записи.
watch(
  () => props.resetKey,
  () => Object.assign(form, createEmptyForm()),
);
</script>

<template>
  <section>
    <form
      class="card form appointment"
      @submit.prevent="submit"
    >
      <h3>Детали записи</h3>

      <label>
        Клиент
        <select
          v-model="form.client_id"
          required
        >
          <option
            value=""
            disabled
          >
            Выберите клиента
          </option>
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
            value=""
            disabled
          >
            Выберите сотрудника
          </option>
          <option
            v-for="employee in employees"
            :key="employee.id"
            :value="employee.id"
          >
            {{ employee.name }}
          </option>
        </select>
      </label>

      <label>
        Услуга
        <input
          v-model="form.service"
          placeholder="Например, консультация"
          required
        />
      </label>

      <div class="cols">
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
      </div>

      <label>
        Комментарий
        <textarea v-model="form.notes" />
      </label>
      <button class="primary">Создать запись</button>
    </form>
  </section>
</template>

<style scoped>
.appointment {
  max-width: 720px;
}
</style>
