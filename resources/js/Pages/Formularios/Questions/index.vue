<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import Resource from '@/api/resource';
import QuestionResource from '@/api/question';
import { ElMessage } from 'element-plus'
import { EditPen, Hide, View } from '@element-plus/icons-vue';

const emit = defineEmits(['set-header-color']);

const questionResource = new Resource('questions');
const quadroResource = new Resource('quadros');
const questionResources = new QuestionResource();


// -------------------------------------------- VARIABLES --------------------------------------------

let loading = ref(false);
let savingData = ref(false);
let title = ref('');
let questions = ref({
        description: '',
        type_question: null,
        answers: [
            {
                description: ''
            }
        ],
        questions: []
    });
let questionData = ref([]);
let quadrosData = ref([]);
let quadrosDataFinal = ref([]);
let dialogVisible = ref(false);
// Variable to set if the question is multiple or not (Can add more options. It's not text or number)
let isMultiple = ref(false);
// variable to check if is update
let isUpdate = ref(false);

let queryQuadro = ref('');
let queryQuadroRemove = ref('');

let loading_visibility = ref(false)
// Variable to store the multiple types of possible answers
let possible_types = ref({
    'text': 'Texto',
    'phone': 'telefone',
    'number': 'Número',
    'image': 'Fotografia',
    'date': 'Selecionar data',
    'email': 'Introduzir email',
    'textarea': 'Área de texto',
    'country': 'Selecionar de lista (Países)',
    'rancho': 'Texto (Sugerir Ranchos) ',
    'district': 'Selecionar de lista (Distritos)',
    'concelho': 'Selecionar de lista (Concelhos)',
    'freguesia': 'Selecionar de lista (Freguesias)',
    'select': 'Selecionar de lista (Menu suspenso)',
    'checkbox': 'Selecionar de lista (Caixas de seleção)',
});

// Rules to the form
let rules = ref({
    description: [{ required: true, message: 'Campo obrigatório', trigger: 'blur' } ],
    type_question: [{ required: true, message: 'Campo obrigatório', trigger: 'blur' } ],
});


const quadrosAddTable = ref(null)
const quadrosRemoveTable = ref(null)


// -------------------------------------------- WATCH --------------------------------------------

watch(queryQuadro, (newValue) => {
    quadrosData.value = quadrosDataFinal.value;
    quadrosData.value = newValue ? quadrosData.value.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : quadrosData.value
})

// Assista a mudanças em queryTrajeRemove e atualize trajeRemoveDataFiltered
watch(queryQuadroRemove, (newValue) => {
    questions.value.questions = newValue ? questions.value.questions.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : questions.value.questions
})

// -------------------------------------------- COMPUTED --------------------------------------------
const addButton = computed(() => selectedRowsAdd.value.length <= 0);
const removeButton = computed(() => selectedRowsRemove.value.length <= 0);

// -------------------------------------------- METHODS --------------------------------------------

// Mounted event
onMounted(() => {
    emit('set-header-color', '#7BAFDE');
    getList()
    getQuadros()
})

function getList() {
    loading.value = true;
    questionResource.list().then(data => {
        questionData.value = data;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

const getQuadros = () => {
    loading.value = true;
    quadroResource.list().then(data => {
        quadrosDataFinal.value = data;
        quadrosData.value = quadrosDataFinal.value;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

// Function to display fields according selected values
function checkSelected() {
    isMultiple.value = false;
    if (questions.value.type_question == "select" || questions.value.type_question == "checkbox") {
        isMultiple.value = true;
    }
}

function handleAdd() {
    isUpdate.value = false
    isMultiple.value = false
    title.value = 'Nova questão'
    dialogVisible.value = true
}

function handleEdit(item) {
    isUpdate.value = true
    isMultiple.value = false
    title.value = 'Editar questão'
    questions.value = item
    questions.value.isTraje = questions.value.isTraje == 1 ? true : false
    questions.value.isTraje = questions.value.isTraje == 1 ? true : false
    questions.value.isTraje = questions.value.isTraje == 1 ? true : false
    questions.value.isTraje = questions.value.isTraje == 1 ? true : false
    checkSelected()
    dialogVisible.value = true
}

function handleCloseModal () {
    isUpdate.value = false
    title.value = ''
    questions.value = {}
    dialogVisible.value = false
}

function addQuestion() {
    questions.value.answers.push({
        description: '',
    });
}

function handleSaveData() {
    savingData.value = true;
    questionResource.store(questions.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Questão adicionada com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            savingData.value = false;
            ElMessage({
                message: 'Não foi possível adicionar a questão',
                type: 'error',
            })
        }
    }).catch(error => {
        savingData.value = true;
        ElMessage({
            message: 'Não foi possível adicionar a questão',
            type: 'error',
        })
        console.log(error);
    });
}

function handleUpdateData() {
    savingData.value = true;
    questionResource.update(questions.value.id, questions.value).then(response => {
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

function handleVisibility(item, value) {
    loading_visibility.value = true;
    questionResources.changeVisibility({ id: item.id }).then(response => {
        if (response && response == 1) {
            ElMessage({
                message: 'Questão adicionada com sucesso',
                type: 'success',
            })
            loading_visibility.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível adicionar a questão',
                type: 'error',
            })
            loading_visibility.value = false;
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível adicionar a questão',
            type: 'error',
        })
        console.log(error);
        loading_visibility.value = false;
    });
}

function addData() {
    console.log(questions.value);
    questions.value.questions.push(...selectedRowsAdd.value);
    quadrosData.value = quadrosData.value.filter(item => !selectedRowsAdd.value.includes(item));
}

function removeData() {
    quadrosData.value.push(...selectedRowsRemove.value);
    questions.value.questions = questions.value.questions.filter(item => !selectedRowsRemove.value.includes(item));
}

const selectedRowsAdd = ref([]);
const selectedRowsRemove = ref([]);

const handleSelectionChangeAdd = (selection) => {
    selectedRowsAdd.value = selection;
};

const handleSelectionChangeRemove = (selection) => {
    selectedRowsRemove.value = selection;
};

const setQuestions = () => {
    if (questions.value && !questions.value.questions) {
        questions.value.questions = [];
    }
}

const tableRowClassName = ({ row }) => {
    if (row.isSpecial) {
        return 'special-row';
    }
    if (row.isSpecialBoards) {
        return 'special-row-board';
    }
    if (row.isTraje) {
        return 'special-row-traje';
    }
    return ''
};

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Questão</el-button>
        </div>
        <el-table :data="questionData" v-loading="loading" style="width: 100%" :row-class-name="tableRowClassName">
            <el-table-column prop="id" label="#" width="180" />
            <el-table-column prop="description" label="Descrição" />
            <el-table-column prop="type_question" label="Tipo da questão">
                <template #default="scope">
                    <span>{{ possible_types[scope.row.type_question] ?? 'N/A' }}</span>
                </template>
            </el-table-column>
            <el-table-column prop="answers" label="Respostas Possíveis">
                <template #default="scope">
                    <span>{{ scope.row.answers_string ?? 'N/A' }}</span>
                </template>
            </el-table-column>
            <el-table-column align="center" label="Ações" width="300">
                <template #default="scope">
                    <el-button class="primaryOrange" v-loading="loading_visibility" type="primary" size="small" @click="handleEdit(scope.row)" >
                        <el-icon class="el-icon--left"><EditPen /></el-icon>
                        Editar
                    </el-button>
                    <el-button v-if="scope.row.active" v-loading="loading_visibility" type="danger" size="small" @click="handleVisibility(scope.row)" >
                        <el-icon class="el-icon--left"><Hide /></el-icon>
                        Desativar
                    </el-button>
                    <el-button v-else v-loading="loading_visibility" type="primary" size="small" @click="handleVisibility(scope.row)" >
                        <el-icon class="el-icon--left"><View /></el-icon>
                        Ativar
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <p class="notesStyle"><strong>Nota:</strong> As linhas a cinza representam perguntas cujas respostas são os quaros disponíveis</p>
        <p class="notesStyle">As linhas a vermelho representam perguntas em que os trajes são dependentes do quadro selecionado</p>
        <p class="notesStyle">As linhas a amarelo representam perguntas em que é possível selecionar qualquer traje disponível</p>
    </el-card>
    <!-- Dialog to add or edit Checklists -->
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="50%"
        :before-close="handleCloseModal"
    >
    <el-form :model="questions" :rules="rules" label-width="auto">
        <el-form-item label="Descrição" prop="description">
            <el-input v-model="questions.description" placeholder="Descrição" />
        </el-form-item>
        <el-form-item label="Tipo de resposta" prop="type_question">
            <el-select
                v-model="questions.type_question"
                placeholder="Select"
                size="large"
                style="width: 80%"
                @change="checkSelected"
            >
                <el-option
                    v-for="(item, index) in possible_types"
                    :key="index"
                    :label="item"
                    :value="index"
                />
            </el-select>
            <el-popover
                v-if="questions.type_question == 'select' || questions.type_question == 'checkbox' || questions.type_question == 'number'"
                popper-class="popoverTest"
                placement="bottom"
                title="Opções"
                trigger="click"
            >
                <template #default>
                    <div class="isMultiple" v-if="isMultiple" >
                        <el-checkbox v-model="questions.isMultiple" label="Selecionar várias respostas" size="large" />
                    </div>
                    <div class="isMultiple" v-if="isMultiple" >
                        <el-checkbox v-model="questions.isSpecial" :disabled="questions.isSpecialBoards" @change="setQuestions" label="Associar os quadros às possíveis respostas" size="large" />
                    </div>
                    <div class="isMultiple" v-if="isMultiple" >
                        <el-checkbox v-model="questions.isSpecialBoards" :disabled="questions.isSpecial" label="As respostas dependem de outra questão" size="large" />
                    </div>
                    <div class="isMultiple" v-if="!isMultiple && questions.type_question == 'number'" >
                        <el-checkbox v-model="questions.isSpecialBoards" :disabled="questions.isSpecial" label="Selecionar Trajes - A resposta depende do quadro" size="large" />
                    </div>
                    <div class="isMultiple" v-if="isMultiple" >
                        <el-checkbox v-model="questions.isTraje" :disabled="questions.isSpecial" label="Apresentar todos os trajes como respostas" size="large" />
                    </div>
                </template>
                <template #reference>
                    <el-button class="addMarginOpc" type="primary" >Opções</el-button>
                </template>
            </el-popover>
            
            <div v-if="isMultiple && !questions.isSpecial && !questions.isSpecialBoards && !questions.isTraje" class="multipleGroup">
                <div v-for="(answer, idx) in questions.answers" :key="idx" class="divMultiple" >
                    <el-form-item class="formItemClass" :label="'Resposta ' + (idx + 1)">
                        <el-input v-model="answer.description" class="inputMultiple" />
                    </el-form-item>
                </div>
                <el-button v-if="isMultiple" @click="addQuestion" type="primary" plain>Adicionar questão</el-button>
            </div>
        </el-form-item>
        <el-row v-if="questions.isSpecial && !questions.isSpecialBoards" class="fullWidth" :gutter="10">
            <el-col :span="11">
                <el-row>
                    <el-col :span="16">
                        <el-input v-model="queryQuadro"></el-input>
                    </el-col>
                    <el-col :span="8" class="alignTableButtons">
                        <el-button type="primary" :disabled="addButton" @click="addData">Adicionar</el-button>
                    </el-col>
                </el-row>
                <el-table :data="quadrosData" ref="quadrosAddTable" style="width: 100%" @selection-change="handleSelectionChangeAdd">
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
                        <el-input v-model="queryQuadroRemove"></el-input>
                    </el-col>
                    <el-col class="alignTableButtons" :span="8">
                        <el-button type="danger" :disabled="removeButton" @click="removeData">Remover</el-button>
                    </el-col>
                </el-row>
                <el-table :data="questions.questions" ref="quadrosRemoveTable" style="width: 100%" @selection-change="handleSelectionChangeRemove">
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="#" width="100" />
                    <el-table-column prop="description" label="Descrição" />
                </el-table>
            </el-col>
        </el-row>
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
.isMultiple {
    width: 100% !important;
}
.multipleGroup {
    width: 100% !important;
}
.divMultiple {
    display: flex !important;
    width: 100% !important;
}
.formItemClass {
    margin-bottom: 10px !important;
    width: 100% !important;
}
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
.addMarginOpc {
    margin-left: 10px !important;
}
</style>
<style>
.popoverTest {
    width: auto !important;
}
.special-row {
    background-color: #c4c4c4 !important;
}
.special-row-board {
    background-color: #ab081259 !important;
}
.special-row-traje {
    background-color: #ffffbd82 !important;
}
</style>