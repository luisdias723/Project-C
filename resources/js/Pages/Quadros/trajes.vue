<script setup>
import { ref, onMounted } from 'vue';
import Resource from '@/api/resource';
import TrajeResource from '@/api/traje';
import { ElMessage } from 'element-plus'
import { EditPen, Hide, View } from '@element-plus/icons-vue';

const trajeResource = new Resource('trajes');
const trajeResources = new TrajeResource();

const emit = defineEmits(['set-header-color']);

// -------------------------------------------- VARIABLES --------------------------------------------

let loading = ref(false);
let savingData = ref(false);
let title = ref('');
let trajes = ref({
        description: '',
    });
let trajeData = ref([]);
let dialogVisible = ref(false);
// Variable to set if the question is multiple or not (Can add more options. It's not text or number)
let isMultiple = ref(false);
// variable to check if is update
let isUpdate = ref(false);

let loading_visibility = ref(false)

// Rules to the form
let rules = ref({
    description: [{ required: true, message: 'Campo obrigatório', trigger: 'blur' } ],
});

// -------------------------------------------- METHODS --------------------------------------------

// Mounted event
onMounted(() => {
    emit('set-header-color', '#C797C5');
    getList()
})

function getList() {
    loading.value = true;
    trajeResource.list().then(data => {
        trajeData.value = data;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

function handleAdd() {
    isUpdate.value = false
    isMultiple.value = false
    title.value = 'Novo traje'
    dialogVisible.value = true
}

function handleEdit(item) {
    isUpdate.value = true
    isMultiple.value = false
    title.value = 'Editar traje'
    trajes.value = item
    dialogVisible.value = true
}

function handleCloseModal () {
    isUpdate.value = false
    title.value = ''
    trajes.value = {}
    dialogVisible.value = false
}

function handleSaveData() {
    savingData.value = true;
    trajeResource.store(trajes.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Questão adicionada com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível adicionar a questão',
                type: 'error',
            })
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível adicionar a questão',
            type: 'error',
        })
        console.log(error);
    });
}

function handleUpdateData() {
    savingData.value = true;
    trajeResource.update(trajes.value.id, trajes.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Questão adicionada com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível atualizar o traje',
                type: 'error',
            })
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível atualizar o traje',
            type: 'error',
        })
        console.log(error);
    });
}

function handleVisibility(item, value) {
    loading_visibility.value = true;
    trajeResources.changeVisibility({ id: item.id }).then(response => {
        if (response && response == 1) {
            let status = value === 0 ? 'desativo' : 'ativo'
            ElMessage({
                message: 'Estado alterado para:  ' + status,
                type: 'success',
            })
            loading_visibility.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível alterar o estado do traje',
                type: 'error',
            })
            loading_visibility.value = false;
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível alterar o estado do traje',
            type: 'error',
        })
        console.log(error);
        loading_visibility.value = false;
    });
}

</script>

<template>
    <!-- Card to display all trajes -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Traje</el-button>
        </div>
        <el-table :data="trajeData" v-loading="loading" style="width: 100%">
            <el-table-column prop="id" label="#" width="180" />
            <el-table-column prop="description" label="Descrição" />
            <el-table-column prop="boards" label="Quadros associados">
                <template #default="scope">
                    <span>{{ scope.row.boards_string ?? 'N/A' }}</span>
                </template>
            </el-table-column>
            <el-table-column align="center" label="Ações" width="360">
                <template #default="scope">
                    <el-button class="primaryOrange" v-loading="loading_visibility" type="primary" size="small" @click="handleEdit(scope.row)" >
                        <el-icon class="el-icon--left"><EditPen /></el-icon>
                        Editar
                    </el-button>
                    <el-button v-if="scope.row.active" v-loading="loading_visibility" type="danger" size="small" @click="handleVisibility(scope.row, 0)" >
                        <el-icon class="el-icon--left"><Hide /></el-icon>
                        Desativar
                    </el-button>
                    <el-button v-else v-loading="loading_visibility" type="primary" size="small" @click="handleVisibility(scope.row, 1)" >
                        <el-icon class="el-icon--left"><View /></el-icon>
                        Ativar
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
    </el-card>
    <!-- Dialog to add or edit Checklists -->
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="40%"
        :before-close="handleCloseModal"
    >
    <el-form :model="trajes" :rules="rules" label-width="auto">
        <el-form-item label="Descrição" prop="description">
            <el-input v-model="trajes.description" />
        </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleCloseModal">Cancelar</el-button>
        <el-button v-if="isUpdate" :loading="savingData" type="primary" @click="handleUpdateData">
          Atualizar
        </el-button>
        <el-button v-else :loading="savingData" type="primary" @click="handleSaveData">
          Confirmar
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<style scoped>

</style>