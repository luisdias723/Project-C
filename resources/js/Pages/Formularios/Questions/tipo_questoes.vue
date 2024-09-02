<script setup>
import { ref } from 'vue';

let loading = ref(false);
let title = ref('');
let question_type = ref([]);
let formData = ref([]);
let dialogVisible = ref(false);
// Variable to set if the question is multiple or not (Can add more options. It's not text or number)
let isMultiple = ref(false);
// Variable to store the multiple types of possible answers
let possible_types = ref({
    'text': 'Texto',
    'number': 'Número',
    'select': 'Selecionar de lista (Menu suspenso)',
    'checkbox': 'Selecionar de lista (Caixas de seleção)',
});

function handleAdd() {
    title.value = 'Novo tipo de questões'
    dialogVisible.value = true
}

function handleEdit(item) {
    title.value = 'Editar tipo de questões'
    question_type.value = item
    dialogVisible.value = true
}

function handleCloseModal () {
    title.value = ''
    question_type.value = {}
    dialogVisible.value = false
}

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Tipo de Questão</el-button>
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
    <el-form :model="question_type" label-width="auto" style="max-width: 600px">
        <el-form-item label="Tipo de resposta">
            <el-select
                v-model="question_type.description"
                placeholder="Select"
                size="large"
                style="width: 240px"
            >
                <el-option
                    v-for="(item, index) in possible_types"
                    :key="index"
                    :label="item"
                    :value="index"
                />
            </el-select>
            <el-checkbox v-if="isMultiple" v-model="question_type.isMultiple" label="Selecionar várias respostas" size="large" />
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