<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import Resource from '@/api/resource';
import QuadroResource from '@/api/quadro';
import { ElMessage } from 'element-plus'
import { EditPen, Hide, View } from '@element-plus/icons-vue';

const quadroResource = new Resource('quadros');
const trajeResource = new Resource('trajes');
const quadroResources = new QuadroResource();
// TODO: Ver as tabelas quando editamos e não guardamos os dados!

const emit = defineEmits(['set-header-color']);

// -------------------------------------------- VARIABLES --------------------------------------------

let loading = ref(false);
let savingData = ref(false);
let title = ref('');
let boards = ref({
        description: '',
        trajes: []
    });
let boardData = ref([]);
let trajeData = ref([]);
let fullTrajeData = ref([]);

// Variable to query the traje data
let queryTraje = ref('')
let queryTrajeRemove = ref('')

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

let selectedRows = ref([])
let selectedRemoveRows = ref([])

// -------------------------------------------- WATCH --------------------------------------------

watch(queryTraje, (newValue) => {
  trajeDataFiltered.value = newValue ? trajeData.value.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : trajeData.value
})

// Assista a mudanças em queryTrajeRemove e atualize trajeRemoveDataFiltered
watch(queryTrajeRemove, (newValue) => {
  trajeRemoveDataFiltered.value = newValue ? boards.value.trajes.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : boards.value.trajes
})

// -------------------------------------------- COMPUTED --------------------------------------------
// Computed property to enable/disable the button to add elements from trajes table
const addButton = computed(() => {
  return selectedRows.value.length === 0
})

// Computed property to enable/disable the button to remove elements from trajes table
const removeButton = computed(() => {
  return selectedRemoveRows.value.length === 0
})

// const trajeDataFiltered = computed(() => {
//     if (!queryTraje.value) {
//         return trajeData.value
//     }
//     return trajeData.value.filter(item => {
//         return item.description.toLowerCase().includes(queryTraje.value.toLowerCase())
//     })
// })

// const trajeRemoveDataFiltered = computed(() => {
//     if (!queryTrajeRemove.value) {
//             return boards.value.trajes
//     }
//     return boards.value.trajes.filter(item => {
//         return item.description.toLowerCase().includes(queryTrajeRemove.value.toLowerCase())
//     })
// })

// -------------------------------------------- METHODS --------------------------------------------

// Mounted event
onMounted(() => {
    emit('set-header-color', '#F69679');
    getTrajes()
    getList()
})

function getList() {
    loading.value = true;
    quadroResource.list().then(data => {
        boardData.value = data;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

function getTrajes() {
    trajeResource.list().then(data => {
        fullTrajeData.value = data;
        trajeData.value = fullTrajeData.value;
    }).catch(error => {
        console.log(error);
    });
}

function handleAdd() {
    isUpdate.value = false
    isMultiple.value = false
    title.value = 'Novo Quadro'
    dialogVisible.value = true
}

function handleEdit(dados) {
    let editData = dados;
    isUpdate.value = true
    isMultiple.value = false
    title.value = 'Editar quadro'
    boards.value = editData    
    dialogVisible.value = true
}

function handleCloseModal () {
    isUpdate.value = false
    title.value = ''
    boards.value = {
        description: '',
        trajes: []
    }
    dialogVisible.value = false
    trajeData.value = fullTrajeData.value;
}

function handleSaveData() {
    savingData.value = true;
    quadroResource.store(boards.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Quadro adicionado com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível adicionar o quadro',
                type: 'error',
            })
            savingData.value = false;
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível adicionar o quadro',
            type: 'error',
        })
        savingData.value = false;
        console.log(error);
    });
}

function handleUpdateData() {
    savingData.value = true;
    quadroResource.update(boards.value.id, boards.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Quadro atualizado com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível atualizar o quadro',
                type: 'error',
            })
            savingData.value = false;
        }
    }).catch(error => {
        savingData.value = false;

        ElMessage({
            message: 'Não foi possível atualizar o quadro',
            type: 'error',
        })
        console.log(error);
    });
}

function handleVisibility(item, value) {
    loading_visibility.value = true;
    quadroResources.changeVisibility({ id: item.id }).then(response => {
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
                message: 'Não foi possível alterar o estado',
                type: 'error',
            })
            loading_visibility.value = false;
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível alterar o estado',
            type: 'error',
        })
        console.log(error);
        loading_visibility.value = false;
    });
}

const handleSelectionChange = (val) => {
  selectedRows.value = val
}

const handleRemoveSelectionChange = (val) => {
  selectedRemoveRows.value = val
}

const addData = () => {
    // Adiciona os itens selecionados ao array removedData
    boards.value.trajes.push(...selectedRows.value)
    
    // Remove os itens selecionados do array trajeData
    trajeData.value = trajeData.value.filter(item => !selectedRows.value.includes(item))
    
    // Limpa a seleção
    selectedRows.value = []
}

const removeData = () => {
    // Adiciona os itens selecionados ao array removedData
    trajeData.value.push(...selectedRemoveRows.value)
    
    // Remove os itens selecionados do array trajeData
    boards.value.trajes = boards.value.trajes.filter(item => !selectedRemoveRows.value.includes(item))
    
    // Limpa a seleção
    selectedRemoveRows.value = []
}

</script>

<template>
    <!-- Card to display all board -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Quadro</el-button>
        </div>
        <el-table :data="boardData" v-loading="loading" style="width: 100%">
            <el-table-column prop="id" label="#" width="180" />
            <el-table-column prop="description" label="Descrição" />
            <el-table-column prop="trajes" label="Trajes associados">
                <template #default="scope">
                    <el-popover
                        v-if="scope.row.count_trajes > 0"
                        placement="top-start"
                        title="Trajes associados"
                        :width="300"
                        trigger="hover"
                    >
                        <template #default>
                            <div>
                                <ul>
                                    <li v-for="(traje, idx_traje) in scope.row.trajes_array" :key="idx_traje">{{ traje }}</li>
                                </ul>
                            </div>
                        </template>
                        <template #reference>
                            <el-tag>{{ scope.row.count_trajes }} associado(s)</el-tag>
                        </template>
                    </el-popover>
                    <span v-else >Sem trajes adicionados</span>
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
                    <el-button v-else v-loading="loading_visibility" size="small" @click="handleVisibility(scope.row, 1)" >
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
        width="60%"
        :before-close="handleCloseModal"
    >
    <el-form :model="boards" :rules="rules" label-width="auto">
        <el-form-item label="Descrição" prop="description">
            <el-input v-model="boards.description" />
        </el-form-item>
        <!-- <el-form-item label="Associar trajes" prop="trajes"> -->
        <el-divider content-position="left">Associar Trajes</el-divider>
        <el-row class="fullWidth" :gutter="10">
            <el-col :span="11">
                <el-row>
                    <el-col :span="16">
                        <el-input v-model="queryTraje"></el-input>
                    </el-col>
                    <el-col :span="8" class="alignTableButtons">
                        <el-button type="primary" :disabled="addButton" @click="addData">Adicionar</el-button>
                    </el-col>
                </el-row>
                <el-table :data="trajeData" v-loading="loading" style="width: 100%" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="#" width="100" />
                    <el-table-column prop="description" label="Descrição" />
                </el-table>
            </el-col>
            <el-col class="alignContent" :span="2">
                <el-divider class="dividerHeight" direction="vertical" />
            </el-col>
            <el-col :span="11">
                <el-row>
                    <el-col :span="16">
                        <el-input v-model="queryTrajeRemove"></el-input>
                    </el-col>
                    <el-col class="alignTableButtons" :span="8">
                        <el-button type="primary" :disabled="removeButton" @click="removeData">Remover</el-button>
                    </el-col>
                </el-row>
                <el-table :data="boards.trajes" v-loading="loading" style="width: 100%" @selection-change="handleRemoveSelectionChange">
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="#" width="100" />
                    <el-table-column prop="description" label="Descrição" />
                </el-table>
            </el-col>
        </el-row>
        <!-- </el-form-item> -->
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
.fullWidth {
    width: 100% !important;
}
.dividerHeight{
    height: 100% !important;
}
.alignContent{
    text-align: center !important;
}
.alignTableButtons {
    text-align: end !important;
}
</style>