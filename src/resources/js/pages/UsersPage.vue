<script setup>
import { reactive, ref } from 'vue';

defineProps({
  users: { type: Array, default: () => [] },
  currentUserId: { type: Number, required: true },
});

const emit = defineEmits(['save', 'remove']);
const editingId = ref(null);
const form = reactive(emptyForm());

// Создаёт начальное состояние формы пользователя.
function emptyForm() {
  return { name: '', email: '', role: 'employee', password: '', password_confirmation: '' };
}

// Переключает форму в режим редактирования выбранного пользователя.
function edit(user) {
  editingId.value = user.id;
  Object.assign(form, emptyForm(), { name: user.name, email: user.email, role: user.role });
}

// Возвращает форму в режим создания.
function reset() {
  editingId.value = null;
  Object.assign(form, emptyForm());
}

// Передаёт данные формы родительскому компоненту.
function submit() {
  emit('save', { id: editingId.value, data: { ...form } });
}

// Запрашивает подтверждение перед удалением учётной записи.
function remove(user) {
  if (window.confirm(`Удалить пользователя «${user.name}»?`)) emit('remove', user.id);
}
</script>

<template>
  <section class="users-grid">
    <div class="card users-list">
      <div class="section-heading">
        <div><h3>Пользователи</h3><p class="muted">Управление доступом сотрудников к кабинету</p></div>
        <button class="secondary" @click="reset">＋ Добавить</button>
      </div>
      <div v-for="item in users" :key="item.id" class="user-row">
        <div class="user-avatar">{{ item.name.slice(0, 1).toUpperCase() }}</div>
        <div class="user-info"><b>{{ item.name }}</b><small>{{ item.email }}</small></div>
        <span :class="['role-badge', item.role]">{{ item.role === 'admin' ? 'Администратор' : 'Сотрудник' }}</span>
        <div class="row-actions">
          <button class="secondary" @click="edit(item)">Изменить</button>
          <button class="danger" :disabled="item.id === currentUserId" title="Собственную учётную запись удалить нельзя" @click="remove(item)">Удалить</button>
        </div>
      </div>
      <div v-if="!users.length" class="empty-state">Пользователей пока нет</div>
    </div>

    <form class="card form" @submit.prevent="submit">
      <h3>{{ editingId ? 'Редактирование пользователя' : 'Новый пользователь' }}</h3>
      <label>Имя<input v-model="form.name" required maxlength="100" /></label>
      <label>Email<input v-model="form.email" type="email" required /></label>
      <label>Роль<select v-model="form.role" required><option value="employee">Сотрудник</option><option value="admin">Администратор</option></select></label>
      <label>{{ editingId ? 'Новый пароль (необязательно)' : 'Пароль' }}<input v-model="form.password" type="password" :required="!editingId" minlength="8" /></label>
      <label>Подтверждение пароля<input v-model="form.password_confirmation" type="password" :required="!editingId || Boolean(form.password)" minlength="8" /></label>
      <div class="form-actions">
        <button class="primary">{{ editingId ? 'Сохранить' : 'Создать' }}</button>
        <button v-if="editingId" type="button" class="secondary" @click="reset">Отмена</button>
      </div>
    </form>
  </section>
</template>

<style scoped>
.users-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(300px, .8fr); gap: 18px; }
.section-heading, .user-row, .row-actions, .form-actions { display: flex; align-items: center; }
.section-heading { justify-content: space-between; margin-bottom: 10px; }
.section-heading h3, .section-heading p { margin: 0; }
.section-heading p { margin-top: 4px; }
.user-row { gap: 13px; padding: 14px 0; border-bottom: 1px solid #edf0f2; }
.user-avatar { display: grid; width: 42px; height: 42px; flex: 0 0 42px; color: #267269; font-weight: 800; background: #dff2ee; border-radius: 12px; place-items: center; }
.user-info { min-width: 0; flex: 1; }
.user-info small { display: block; overflow: hidden; margin-top: 4px; color: #7c8795; text-overflow: ellipsis; }
.role-badge { padding: 5px 9px; font-size: 12px; font-weight: 700; border-radius: 99px; }
.role-badge.admin { color: #805717; background: #fff0cc; }
.role-badge.employee { color: #267269; background: #dff2ee; }
.row-actions, .form-actions { gap: 8px; }
.row-actions button { padding: 8px 10px; }
button:disabled { cursor: not-allowed; opacity: .45; }
@media (max-width: 1100px) { .users-grid { grid-template-columns: 1fr; } }
@media (max-width: 650px) { .user-row { align-items: flex-start; flex-wrap: wrap; } .row-actions { width: 100%; padding-left: 55px; } }
</style>
