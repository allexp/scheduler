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

defineEmits(['select']);

const month = ref(new Date());
const today = month.value.getDate();
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
          :class="{ empty: !day, today: day === today }"
          class="day"
        >
          <span>{{ day }}</span>
          <button
            v-for="appointment in appointmentsForDay(day)"
            :key="appointment.id"
            :class="appointment.status"
            :title="appointment.service"
            @click="$emit('select', appointment)"
          >
            <time>{{ formatTime(appointment.starts_at) }}</time>
            {{ appointment.client.full_name || appointment.client.last_name }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 18px;
}

.stats article {
  padding: 20px;
  background: #fff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-medium);
  box-shadow: var(--shadow-card);
}

.stats span {
  color: var(--color-text-muted);
}

.stats b {
  display: block;
  margin-top: 7px;
  font-size: 30px;
}

.calendar {
  padding: 0;
  overflow: hidden;
}

.calendar-head {
  display: flex;
  gap: 25px;
  align-items: center;
  justify-content: center;
  padding: 18px;
}

.calendar-head h3 {
  min-width: 180px;
  text-align: center;
  text-transform: capitalize;
}

.calendar-head button {
  padding: 7px 12px;
  background: #fff;
  border: 1px solid #dfe4e9;
  border-radius: var(--radius-small);
}

.week,
.grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.week b {
  padding: 12px;
  color: #7a8595;
  font-size: 12px;
  text-align: center;
  border-top: 1px solid #e5e9ed;
  border-bottom: 1px solid #e5e9ed;
}

.day {
  min-height: 120px;
  padding: 8px;
  border-right: 1px solid #e8ebee;
  border-bottom: 1px solid #e8ebee;
}

.day.empty {
  background: #f8f9fa;
}
.day.today {
  border: 2px solid #FF9000;
}

.day > span {
  display: block;
  margin-bottom: 6px;
  color: #667285;
  font-size: 13px;
}

.day button {
  display: block;
  width: 100%;
  padding: 6px;
  margin: 4px 0;
  overflow: hidden;
  font-size: 11px;
  text-align: left;
  text-overflow: ellipsis;
  white-space: nowrap;
  background: var(--color-primary-light);
  border: 0;
  border-left: 3px solid var(--color-primary);
  border-radius: 4px;
}

.day button.cancelled {
  text-decoration: line-through;
  opacity: 0.45;
}

.day time {
  display: block;
  font-weight: 800;
}

@media (max-width: 900px) {
  .stats {
    grid-template-columns: 1fr;
  }

  .day {
    min-height: 90px;
  }

  .day button {
    font-size: 0;
  }

  .day time {
    font-size: 10px;
  }
}
</style>
