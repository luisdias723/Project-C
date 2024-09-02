<script setup>
import { ref } from 'vue';

let loading = ref(false);
let title = ref('');
let checklist = ref({
    questions: [
        {
            name: '',
            type: ''
        }
    ]});
let formData = ref([]);
let dialogVisible = ref(false);

function handleAdd() {
    title.value = 'Novo Formulário'
    dialogVisible.value = true
}

function handleEdit(item) {
    title.value = 'Editar Formulário'
    checklist.value = item
    dialogVisible.value = true
}

function handleCloseModal () {
    title.value = ''
    checklist.value = {}
    dialogVisible.value = false
}

function addQuestion() {
    checklist.value.questions.push({
            name: '',
            type: ''
        });
}

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Formulário</el-button>
        </div>
        <el-table :data="formData" style="width: 100%">
            <el-table-column prop="id" label="#" width="180" />
            <el-table-column prop="description" label="Description" />
            <el-table-column label="Actions" width="180" />
        </el-table>
    </el-card>
    <!-- Dialog to add or edit Checklists -->
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        :before-close="handleCloseModal"
    >
    <el-form :model="checklist" label-width="auto" style="max-width: 600px">
        <el-form-item label="Nome da checklist">
            <el-input v-model="checklist.name" />
        </el-form-item>
        <el-divider content-position="left">Questões</el-divider>
        <el-form-item label="Perguntas">
            <div v-for="(question, index) in checklist.questions" :key="index">
                <el-input v-model="question.name" />
            </div>
            <el-button @click="addQuestion">Adicionar questão</el-button>
        </el-form-item>

    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleCloseModal">Cancel</el-button>
        <el-button type="primary" @click="handleCloseModal">
          Confirm
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<style scoped>
  
</style>