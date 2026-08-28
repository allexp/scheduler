<script setup>
import { computed, ref } from 'vue';

// Записи и показатели загружаются корневым компонентом и передаются странице через props.
const props = defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

defineEmits(['cancel']);

const month = ref(new Date());
const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

// Локализованное название отображаемого месяца для заголовка календаря.
const monthLabel = computed(() =>
  month.value.toLocaleDateString('ru-RU', {
    month: 'long',
    year: 'numeric',
  }),
);

// Формирует календарную сетку с пустыми ячейками до первого дня месяца.
const days = computed(() => {
  const year = month.value.getFullYear();
  const monthIndex = month.value.getMonth();
  const firstDay = new Date(year, monthIndex, 1);
  const emptyDays = (firstDay.getDay() + 6) % 7;
  const dayCount = new Date(year, monthIndex + 1, 0).getDate();

  return [
    ...Array(emptyDays).fill(null),
    ...Array.from({ length: dayCount }, (_, index) => index + 1),
  ];
});

// Выбирает записи, относящиеся к конкретному дню текущего месяца.
function appointmentsForDay(day) {
  if (!day) {
    return [];
  }

  return props.appointments.filter((appointment) => {
    const date = new Date(appointment.starts_at);

    return (
      date.getFullYear() === month.value.getFullYear() &&
      date.getMonth() === month.value.getMonth() &&
      date.getDate() === day
    );
  });
}

// Переключает календарь на соседний месяц.
function moveMonth(offset) {
  month.value = new Date(month.value.getFullYear(), month.value.getMonth() + offset, 1);
}

// Форматирует время записи без отображения даты.
function formatTime(date) {
  return new Date(date).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>

<template>
  <section>
    <div class="stats">
      <article>
        <span>Клиентов</span>
        <b>{{ stats.clients }}</b>
      </article>
      <article>
        <span>Сегодня записей</span>
        <b>{{ stats.today }}</b>
      </article>
      <article>
        <span>Предстоящих</span>
        <b>{{ stats.upcoming }}</b>
      </article>
    </div>

    <div class="calendar card">
      <div class="calendar-head">
        <button @click="moveMonth(-1)">←</button>
        <h3>{{ monthLabel }}</h3>
        <button @click="moveMonth(1)">→</button>
      </div>

      <div class="week">
        <b
          v-for="day in weekDays"
          :key="day"
        >
          {{ day }}
        </b>
      </div>

      <div class="grid">
        <div
          v-for="(day, index) in days"
          :key="`${day}-${index}`"
          :class="{ empty: !day }"
          class="day"
        >
          <span>{{ day }}</span>
          <button
            v-for="appointment in appointmentsForDay(day)"
            :key="appointment.id"
            :class="appointment.status"
            :title="appointment.service"
            @click="$emit('cancel', appointment)"
          >
            <time>{{ formatTime(appointment.starts_at) }}</time>
            {{ appointment.client.full_name || appointment.client.last_name }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
