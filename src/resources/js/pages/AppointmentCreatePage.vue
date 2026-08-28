<script setup>
import { reactive } from 'vue';

// Справочники клиентов и сотрудников загружаются корневым компонентом.
defineProps({
  clients: {
    type: Array,
    default: () => [],
  },
  employees: {
    type: Array,
    default: () => [],
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

// Передаёт данные записи родителю и сбрасывает форму после отправки события.
function submit() {
  emit('create', { ...form });
  Object.assign(form, createEmptyForm());
}
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
