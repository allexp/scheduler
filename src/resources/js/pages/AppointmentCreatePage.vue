<script setup>
import { useForm } from '@inertiajs/vue3';

// Справочники клиентов и сотрудников передаются Laravel-контроллером.
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

const form = useForm(createEmptyForm());

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
  form.post('/appointments');
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
      <div
        v-if="Object.keys(form.errors).length"
        class="error"
      >
        {{ Object.values(form.errors)[0] }}
      </div>
    </form>
  </section>
</template>

<style scoped>
.appointment {
  max-width: 720px;
}
</style>
