<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { EditPen, Hide, View, Plus, Delete } from '@element-plus/icons-vue';
import Resource from '@/api/resource';
import ConditionResource from '@/api/condition';
import FormularioResource from '@/api/formulario';
import { ElMessageBox, ElMessage } from 'element-plus';

const emit = defineEmits(['set-header-color']);

const trajeResource = new Resource('trajes');
const formResource = new Resource('formularios');
const questionsResource = new Resource('questions');
const conditionResource = new ConditionResource();
const formularioResource = new FormularioResource();

let loading = ref(false);
let title = ref('');
let checklist = ref({
    questions: []
});
let formData = ref([]);
let dialogVisible = ref(false);

let queryQuadro = ref('');
let queryQuadroRemove = ref('');

let questionsData = ref([]);
let questionsDataFinal = ref([]);

let trajeData = ref([])

let savingData = ref(false);

// variable to check if is update
let isUpdate = ref(false);

// Variables to store conditions selected
let questionConditions = ref({status: null, conditions: [{value: null, select_condition: 'E'}]})
let answerConditions = ref({status: null, conditions: [{value: null}]})

const rules = ref({
    name: [{ required: true, message: 'Campo obrigatório', trigger: ['blur', 'change'] } ]
});

const displayOptions = ref([
    {
        name: 'Mostrar',
        value: 1
    },
    {
        name: 'Ocultar',
        value: 0
    }
])

// -------------------------------------------- WATCH --------------------------------------------

watch(queryQuadro, (newValue) => {
    questionsData.value = questionsDataFinal.value;
    questionsData.value = newValue ? questionsData.value.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : questionsData.value
})

// Assista a mudanças em queryTrajeRemove e atualize trajeRemoveDataFiltered
watch(queryQuadroRemove, (newValue) => {
    checklist.value.questions = newValue ? checklist.value.questions.filter(item =>
    item.description.toLowerCase().includes(newValue.toLowerCase())
  ) : checklist.value.questions
})

// -------------------------------------------- COMPUTED --------------------------------------------
const addButton = computed(() => selectedRowsAdd.value.length <= 0);
const removeButton = computed(() => selectedRowsRemove.value.length <= 0);

// -------------------------------------------- METHODS --------------------------------------------

onMounted(() => {
    emit('set-header-color', '#F69679C4');
    getList()
    getQuestions()
    getTrajes()
})

const getList = () => {
    loading.value = true;
    formResource.list().then(data => {
        formData.value = data;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

const getQuestions = () => {
    loading.value = true;
    questionsResource.list().then(data => {
        questionsDataFinal.value = data;
        questionsData.value = questionsDataFinal.value;
        loading.value = false
    }).catch(error => {
        console.log(error);
        loading.value = false
    });
}

const getTrajes =() => {
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
    title.value = 'Novo Formulário'
    dialogVisible.value = true
    widthDialog.value = '40%'
}

function handleEdit(item) {
    isUpdate.value = true
    title.value = 'Editar Formulário'
    checklist.value = item
    dialogVisible.value = true
    questionsData.value = questionsData.value.filter(item => {
            return !checklist.value.questions.some(question => question.id === item.id);
        }
    )
    widthDialog.value = '40%'
}

let isCondition = ref(false)
let widthDialog = ref('30%')

const handleConditions = (item) => {
    isCondition.value = true
    checklist.value = item
    title.value = item.name
    dialogVisible.value = true
    widthDialog.value = '30%'
}

function handleCloseModal () {
    dialogVisible.value = false
    isUpdate.value = false
    isCondition.value = false
    title.value = ''
    checklist.value = {
    questions: []}
    questionsData.value = questionsDataFinal.value
    editing.value = false
    validationData.value = {}
    widthDialog.value = '30%'
    questionConditions.value = {status: null, conditions: [{value: null, select_condition: 'E'}]}
    answerConditions.value = {conditions: [{}]}
}

const cancelButton = () => {
    ElMessageBox.confirm(
        'Poderão existir dados por guardar. Continuar?',
        'Continuar?',
    {
      confirmButtonText: 'Continuar',
      cancelButtonText: 'Cancelar',
      type: 'warning',
    }
  )
    .then(() => {
        handleCloseModal()
    })
    .catch(() => {
      ElMessage({
        type: 'info',
        message: 'Delete canceled',
      })
    })
}

function addData() {
    checklist.value.questions.push(...selectedRowsAdd.value);
    questionsData.value = questionsData.value.filter(item => !selectedRowsAdd.value.includes(item));
}

function removeData() {
    questionsData.value.push(...selectedRowsRemove.value);
    checklist.value.questions = checklist.value.questions.filter(item => !selectedRowsRemove.value.includes(item));
}

let selectedRowsAdd = ref([]);
let selectedRowsRemove = ref([]);

const handleSelectionChangeAdd = (selection) => {
    selectedRowsAdd.value = selection;
};

const handleSelectionChangeRemove = (selection) => {
    selectedRowsRemove.value = selection;
};

const handleSaveData = () => {
    savingData.value = true;
    formResource.store(checklist.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Formulário adicionado com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            savingData.value = false;
            ElMessage({
                message: 'Não foi possível adicionar o formulário',
                type: 'error',
            })
        }
    }).catch(error => {
        savingData.value = false;
        ElMessage({
            message: 'Não foi possível adicionar o formulário',
            type: 'error',
        })
        console.log(error);
    });
}

const handleUpdateData = () => {
    savingData.value = true;
    formResource.update(checklist.value.id, checklist.value).then(response => {
        if (response && response.data) {
            ElMessage({
                message: 'Formulário atualizado com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            ElMessage({
                message: 'Não foi possível atualizar o formulário',
                type: 'error',
            })
        }
    }).catch(error => {
        ElMessage({
            message: 'Não foi possível atualizar o formulário',
            type: 'error',
        })
        console.log(error);
    });
}

const handleUpdateCondition = () => {
    let question = {}
    question.id = validationData.value.id
    question.form = checklist.value.id


    let q_conditions = {}
    if (questionConditions.value.conditions.length > 0 && questionConditions.value.conditions[0].selectedQuestion && questionConditions.value.conditions[0].selectedQuestion.id) {
        q_conditions.status = questionConditions.value.status;
        for (var j in questionConditions.value.conditions) {
                let aux = {};
                aux.answer = questionConditions.value.conditions[j].answer
                aux.rule = questionConditions.value.conditions[j].rule
                aux.question = questionConditions.value.conditions[j].selectedQuestion.id
                aux.select_condition = questionConditions.value.conditions[j].select_condition == 'E' ? '&&' : '||'
                if (!q_conditions.conditions) {
                    q_conditions.conditions = []
                }
                q_conditions.conditions.push(aux)
        }
    }

    question.question = q_conditions;

    let a_conditions = {}
    if (answerConditions.value.conditions.length > 0 && answerConditions.value.conditions[0].selectedQuestion && answerConditions.value.conditions[0].selectedQuestion.id) {
        a_conditions.currentQuestion = validationData.value.id
        for (var j in answerConditions.value.conditions) {
            let aux = {};
            aux.answerToApply = answerConditions.value.conditions[j].answer
            aux.question = answerConditions.value.conditions[j].selectedQuestion.id
            aux.rule = answerConditions.value.conditions[j].rule
            aux.selectedAnswer = answerConditions.value.conditions[j].selectedAnswer
            aux.status = answerConditions.value.conditions[j].status
            if (!a_conditions.conditions) {
                a_conditions.conditions = []
            }
            a_conditions.conditions.push(aux)
        }
    }

    question.answers = a_conditions;

    savingData.value = true;
    formularioResource.saveConditions(question).then(response => {
        if (response) {
            ElMessage({
                message: 'Validações adicionadas com sucesso',
                type: 'success',
            })
            savingData.value = false;
            handleCloseModal()
            getList()
        } else {
            savingData.value = false;
            ElMessage({
                message: 'Não foi possível adicionar validações',
                type: 'error',
            })
        }
    }).catch(error => {
        savingData.value = false;
        ElMessage({
            message: 'Não foi possível adicionar validações',
            type: 'error',
        })
        console.log(error);
    });
}

let loading_visibility = ref(false);

const handleVisibility = (item, value) => {
    loading_visibility.value = true;
    formularioResource.changeVisibility({ id: item.id }).then(response => {
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

let editing = ref(false)
let validationData = ref({})

// Variable to store the questions not selected
let questionsNotSelected = ref({})

const displayCondition = (questionSeleted) => {
    conditionResource.getConditions({ id: questionSeleted.id, form: checklist.value.id}).then(data => {
        let newQuestions = checklist.value.questions.filter(item => {
            return questionSeleted.id !== item.id;
        })
        questionsNotSelected.value = newQuestions

        if (data && (data.questions.conditions.length > 0 || data.answers.conditions.length > 0)) {
            if (data.questions.conditions.length > 0) {
                displayQuestionConditions(data.questions)
            }
            if (data.answers.conditions.length > 0) {
                displayAnswersConditions(data.answers)
            }
        } else {
            questionConditions.value = {status: null, conditions: [{value: null, select_condition: 'E'}]}
            answerConditions.value = {status: null, conditions: [{value: null}]}

        }

        validationData.value = questionSeleted
        if (!validationData.value.conditions) validationData.value.conditions = []
        editing.value = true
        widthDialog.value = '60%'
    }).catch(error => {
        console.log(error);
    });
}

const displayQuestionConditions = (data) => {
    questionConditions.value = data
    
    for (var j in questionConditions.value.conditions) {
        if (questionConditions.value.conditions[j].selectedQuestion.type_question !== 'date') {
            questionConditions.value.conditions[j].answer = parseInt(questionConditions.value.conditions[j].answer)
        }
        let conditionId = questionConditions.value.conditions[j].selectedQuestion.id;
        let index = questionsNotSelected.value.findIndex(item => item.id == conditionId);
        questionConditions.value.conditions[j].value = index;
        displayRules(index, j)
        displayAnswers(j)
    }
}

const displayAnswersConditions = (data) => {
    answerConditions.value = data
    
    for (var j in answerConditions.value.conditions) {
        let conditionId = answerConditions.value.conditions[j].selectedQuestion.id;
        let index = questionsNotSelected.value.findIndex(item => item.id == conditionId);
        answerConditions.value.conditions[j].value = index;
        displayOnRules(index, j)
        displayOnAnswers(j)
    }
}

const hideCondition = () => {
    ElMessageBox.confirm(
    'Poderão existir dados por guardar. Continuar?',
    'Continuar?',
    {
      confirmButtonText: 'Continuar',
      cancelButtonText: 'Cancelar',
      type: 'warning',
    }
  )
    .then(() => {
        editing.value = false
        validationData.value = {}
        questionsNotSelected.value = {}
        // questionConditions.value = {status: null, conditions: [{value: null, select_condition: 'E'}]}
        answerConditions.value = {conditions: [{}]}
        widthDialog.value = '30%'
    })
    .catch(() => {
    })
}


// Methods for validation Form
let answersToDisplay = ref({})
let answersToDisplayOnA = ref({})
let rulesToDisplay = ref({})
let rulesToDisplayOnA = ref({})

const displayRules = (val, index) => {
    if (val === ''){
        rulesToDisplay.value = {}
        return
    }
    questionConditions.value.conditions[index].selectedQuestion = questionsNotSelected.value[val];
    let q_id = questionConditions.value.conditions[index].selectedQuestion.id
    if (questionConditions.value.conditions[index].selectedQuestion.type_question == 'select' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'checkbox' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'district' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'concelho' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'freguesia' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'country') {
        rulesToDisplay.value[q_id] = {'!=': 'Diferente de', '==': 'Igual a'};
    }
    if (questionConditions.value.conditions[index].selectedQuestion.type_question == 'number' || questionConditions.value.conditions[index].selectedQuestion.type_question == 'date') {
        rulesToDisplay.value[q_id] = {
            '<': 'Menor que',
            '<=': 'Menor ou igual a',
            '==': 'Igual a',
            '>=': 'Maior ou igual a',
            '>': 'Maior que',
        }
    }
}

// TODO: VER PORQUE É QUE NÃO CONSIGO ACEDER AO TIPO DE QUESTÃO
let isFilterable

// Questions
const displayAnswers = (index, teste = null) => {
    let q_id = questionConditions.value.conditions[index].selectedQuestion.id
    answersToDisplay.value[q_id] = questionConditions.value.conditions[index].selectedQuestion.answers
    if (questionConditions.value.conditions[index].selectedQuestion.isSpecialBoards) {
        answersToDisplay.value[q_id] = trajeData.value
    }
}

const addCondition = (cond) => {
    questionConditions.value.conditions.push({
        value: null, select_condition: cond
    });
}

const removeCondition = (index) => {
    questionConditions.value.conditions.splice(index, 1); 
}

const removeConditionAnswer = (index) => {
    answerConditions.value.conditions.splice(index, 1); 
}

// Answers
const addAnswerCondition = () => {
    answerConditions.value.conditions.push({
        value: null
    });
}

const displayOnAnswers = (index) => {
    let q_id = answerConditions.value.conditions[index].selectedQuestion.id
    answersToDisplayOnA.value[q_id] = answerConditions.value.conditions[index].selectedQuestion.answers
    if (answerConditions.value.conditions[index].selectedQuestion.isSpecialBoards) {
        answersToDisplayOnA.value[q_id] = trajeData.value
    }
}

const displayOnRules = (val, index) => {
    if (val === ''){
        rulesToDisplayOnA.value = {}
        return
    }

    answerConditions.value.conditions[index].selectedQuestion = questionsNotSelected.value[val];
    let q_id = answerConditions.value.conditions[index].selectedQuestion.id

    if (answerConditions.value.conditions[index].selectedQuestion.type_question == 'select' || answerConditions.value.conditions[index].selectedQuestion.type_question == 'checkbox') {
        rulesToDisplayOnA.value[q_id] = {'!=': 'Diferente de', '==': 'Igual a'};
    }
    
    if (answerConditions.value.conditions[index].selectedQuestion.type_question == 'number') {
        rulesToDisplayOnA.value[q_id] = {
            '<': 'Menor que',
            '<=': 'Menor ou igual a',
            '==': 'Igual a',
            '>=': 'Maior ou igual a',
            '>': 'Maior que',
        };
    }
}

const checkFilterable = (item) => {
    if (item && item.selectedQuestion) {
        if (item.selectedQuestion.type_question === 'country' || item.selectedQuestion.type_question === 'district' || item.selectedQuestion.type_question === 'concelho' || item.selectedQuestion.type_question === 'freguesia') {
            return true;
        }
    }
    return false;
}

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <div>
            <el-button type="primary" @click="handleAdd">Adicionar Formulário</el-button>
        </div>
        <el-table :data="formData" :loading="loading" style="width: 100%">
            <el-table-column prop="id" label="#" width="180" />
            <el-table-column prop="name" label="Descrição" />
            <el-table-column prop="questions" label="Questões associadas">
                <template #default="scope">
                    <el-popover
                        v-if="scope.row.questions_array.length > 0"
                        placement="top-start"
                        title="Questões associadas"
                        :width="200"
                        trigger="hover"
                    >
                        <template #default>
                            <div>
                                <ul>
                                    <li v-for="(question, idx_question) in scope.row.questions_array" :key="idx_question">{{ question }}</li>
                                </ul>
                            </div>
                        </template>
                        <template #reference>
                            <el-tag>{{ scope.row.questions.length }} associada(s)</el-tag>
                        </template>
                    </el-popover>
                    <span v-else >Sem trajes adicionados</span>
                </template>
            </el-table-column>
            <el-table-column align="center" label="Ações" width="360">
                <template #default="scope">
                    <el-button class="primaryRo" v-loading="loading_visibility" type="primary" size="small" @click="handleConditions(scope.row)" >
                        <el-icon class="el-icon--left"><EditPen /></el-icon>
                        Ver
                    </el-button>
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
        :close-on-click-modal="false"
        v-model="dialogVisible"
        :title="title"
        :before-close="handleCloseModal"
        :width="widthDialog"
    >
    <el-form v-if="!isCondition" :model="checklist" :rules="rules" label-width="auto">
        <el-form-item prop="name" label="Nome do formulário">
            <el-input v-model="checklist.name" />
        </el-form-item>
        <el-divider content-position="left">Questões</el-divider>
        <el-row class="fullWidth" :gutter="10">
            <el-col :span="11">
                <el-row>
                    <el-col :span="16">
                        <el-input v-model="queryQuadro"></el-input>
                    </el-col>
                    <el-col :span="8" class="alignTableButtons">
                        <el-button type="primary" :disabled="addButton" @click="addData">Adicionar</el-button>
                    </el-col>
                </el-row>
                <el-table :data="questionsData" ref="quadrosAddTable" style="width: 100%" @selection-change="handleSelectionChangeAdd">
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="#" width="55">
                        <template #default="scope">
                            <span class="opensansName">{{ scope.row.id }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="description" label="Descrição">
                        <template #default="scope">
                            <span class="opensansName">{{ scope.row.description }}</span>
                        </template>
                    </el-table-column>
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
                <el-table :data="checklist.questions" ref="quadrosRemoveTable" style="width: 100%" @selection-change="handleSelectionChangeRemove">
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="id" label="#" width="55">
                        <template #default="scope">
                            <span class="opensansName">{{ scope.row.id }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="description" label="Descrição">
                        <template #default="scope">
                            <span class="opensansName">{{ scope.row.description }}</span>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
    </el-form>
    <el-form v-if="isCondition && !editing" :model="checklist" label-width="auto">
        <el-form-item v-for="(qst, index_qst) in checklist.questions" :key="index_qst" :label="qst.description">
            <!-- Input TEXT -->
            <!-- <el-input v-if="qst.type_question == 'text'" v-model="qst.condition" /> -->
            <!-- Input Number -->
            <!-- <el-input-number v-if="qst.type_question == 'number'" v-model="qst.condition" :min="1" :max="10" /> -->
            <!-- Input Email -->
            <!-- Input Checkbox -->
            <!-- Input Select -->
            <!-- <el-select
                v-if="qst.type_question == 'select'"
                :multiple="qst.isMultiple ? true : false"
                v-model="qst.condition"
                placeholder="Select"
                size="large"
                style="width: 240px"
            >
                <el-option
                    v-for="(ans, idx_ans) in qst.answers"
                    :key="idx_ans"
                    :label="ans.description"
                    :value="ans.id"
                />
            </el-select> -->
            <el-popover
                popper-class="popoverTest"
                placement="right"
                title="Opções"
                trigger="click"
            >
                <template #default>
                    <div>
                        <el-checkbox v-model="qst.mandatory" label="Resposta obrigatória" size="large" />
                    </div>
                </template>
                <template #reference>
                    <el-button class="primaryBlue" type="primary" >Opções</el-button>
                </template>
            </el-popover>
            <el-button type="primary" @click="displayCondition(qst)">Validações</el-button>
        </el-form-item>
    </el-form>
    <!-- Validações referentes às questões (Esconder ou apresentar e afins...) -->
    <el-button v-if="editing" type="primary" @click="hideCondition">Voltar</el-button>
    <el-row class="addMarginConditions" v-if="editing">
        <el-col :span="12">
            <el-card>
                <template #header>
                    <div class="card-header">
                        <span class="opensansName">Validações da Pergunta</span>
                    </div>
                </template>
                <el-row :gutter="5">
                    <el-col :span="16">
                        <el-select v-model="questionConditions.status" class="opensansName" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="(display, idx_display) in displayOptions"
                                :key="idx_display"
                                :label="display.name"
                                :value="display.value"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="8">
                        <span class="opensansName">Se as seguintes condições ocorrerem</span>
                    </el-col>
                </el-row>
                <el-row class="addMarginConditions" v-for="(qst_cond, idx_qst_cond) in questionConditions.conditions" :key="idx_qst_cond" :gutter="5">
                    <el-divider v-if="idx_qst_cond > 0" class="opensansName">{{ qst_cond.select_condition }}</el-divider>
                    <el-col v-if="idx_qst_cond > 0" :span="3">
                        <el-button class="opensansName" v-if="questionConditions.status !== null && questionConditions.status !== '' && idx_qst_cond > 0" type="danger" @click="removeCondition(idx_qst_cond)"><el-icon><Delete /></el-icon></el-button>    
                    </el-col>
                    <el-col :span="idx_qst_cond > 0 ? 6 : 7">
                        <el-select class="opensansName" v-if="questionConditions.status !== null && questionConditions.status !== ''" v-model="qst_cond.value" clearable placeholder="Selecionar" @change="displayRules($event, idx_qst_cond)">
                            <el-option
                                class="opensansName"
                                v-for="(qt, idx_qt) in questionsNotSelected"
                                :key="qt.id"
                                :label="qt.description"
                                :value="idx_qt"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="idx_qst_cond > 0 ? 6 : 7">
                        <el-select class="opensansName" v-if="questionConditions.status !== null && questionConditions.status !== '' && qst_cond.value" v-model="qst_cond.rule" clearable placeholder="Selecionar" @change="displayAnswers(idx_qst_cond, qst_cond.selectedQuestion)">
                            <el-option
                                class="opensansName"
                                v-for="(rl, idx_rl) in rulesToDisplay[qst_cond.selectedQuestion.id]"
                                :key="rl"
                                :label="rl"
                                :value="idx_rl"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="idx_qst_cond > 0 ? 6 : 7">
                        <el-select class="opensansName" v-if="questionConditions.status !== null && questionConditions.status !== '' && qst_cond.rule && qst_cond.selectedQuestion && qst_cond.selectedQuestion.type_question !== 'date'" v-model="qst_cond.answer" :filterable="checkFilterable(qst_cond)" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="an in answersToDisplay[qst_cond.selectedQuestion.id]"
                                :key="an.id"
                                :label="an.description"
                                :value="an.id"
                            />
                        </el-select>
                        <el-date-picker
                            v-if="qst_cond.selectedQuestion && qst_cond.selectedQuestion.type_question === 'date'"
                            v-model="qst_cond.answer"
                            class="maxDateWidth opensansName"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            type="date"
                            placeholder="Selecione um dia"
                        />
                    </el-col>
                    <el-col :span="3">
                        <div v-if="questionConditions.status !== null && questionConditions.status !== '' && qst_cond.rule && qst_cond.answer" class="conditionsBtn">
                            <el-button class="opensansName" type="primary" @click="addCondition('E')">e</el-button>
                            <el-button class="removeBtnMargin opensansName primaryYellow" type="primary" @click="addCondition('OU')">ou</el-button>
                        </div>
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
        <el-col :span="12">
            <el-card>
                <template #header>
                    <div class="card-header">
                        <span class="opensansName">Validações das Respostas</span>
                    </div>
                </template>
                <el-row class="opensansName" v-for="(ans_cond, idx_ans_cond) in answerConditions.conditions" :key="idx_ans_cond" :gutter="5">
                    <el-divider class="opensansName" v-if="validationData.isSpecialBoards && idx_ans_cond > 0"></el-divider>
                    <el-col :span="12">
                        <el-select class="opensansName" v-model="ans_cond.status" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="(display, idx_display) in displayOptions"
                                :key="idx_display"
                                :label="display.name"
                                :value="display.value"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="12">
                        <el-select class="opensansName" v-if="validationData.isSpecialBoards || validationData.isTraje" v-model="ans_cond.answer" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="rl in trajeData"
                                :key="rl.id"
                                :label="rl.description"
                                :value="rl.id"
                            />
                        </el-select>
                        <el-select class="opensansName" v-if="validationData.isSpecial" v-model="ans_cond.answer" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="rl in validationData.answers"
                                :key="rl.id"
                                :label="rl.description"
                                :value="rl.id"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="3">
                        <el-button class="opensansName" v-if="ans_cond.answer && idx_qst_cond > 0" type="primary" @click="removeConditionAnswer(idx_ans_cond)"><el-icon><Delete /></el-icon></el-button>    
                    </el-col>
                    <el-col :span="6">
                        <span class="opensansName" v-if="ans_cond.answer">Se</span>
                        <el-select class="opensansName" v-if="ans_cond.answer" v-model="ans_cond.selectedQuestion" clearable placeholder="Selecionar" @change="displayOnRules($event, idx_ans_cond)">
                            <el-option
                                class="opensansName"
                                v-for="(qt, idx_qt) in questionsNotSelected"
                                :key="qt.id"
                                :label="qt.description"
                                :value="idx_qt"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="6">
                        <el-select class="opensansName" v-if="ans_cond.answer && ans_cond.selectedQuestion" v-model="ans_cond.rule" clearable placeholder="Selecionar" @change="displayOnAnswers(idx_ans_cond)">
                            <el-option
                                class="opensansName"
                                v-for="(rl, id_rl) in rulesToDisplayOnA[ans_cond.selectedQuestion.id]"
                                :key="rl"
                                :label="rl"
                                :value="id_rl"
                            />
                        </el-select>
                    </el-col>
                    <el-col :span="6">
                        <el-select class="opensansName" v-if="ans_cond.answer && ans_cond.rule" v-model="ans_cond.selectedAnswer" clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName"
                                v-for="an in answersToDisplayOnA[ans_cond.selectedQuestion.id]"
                                :key="an.id"
                                :label="an.description"
                                :value="an.id"
                            />
                        </el-select>
                    </el-col>
                    <el-col v-if="ans_cond.answer && ans_cond.rule && ans_cond.selectedAnswer" :span="3">
                        <div class="conditionsBtn">
                            <el-button class="opensansName" type="primary" @click="addAnswerCondition()"><el-icon><Plus /></el-icon></el-button>
                        </div>
                    </el-col>
                </el-row>
            </el-card>
        </el-col>
    </el-row>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="cancelButton">Cancelar</el-button>
        <el-button v-if="isUpdate" :loading="savingData" type="primary" @click="handleUpdateData">
          Atualizar
        </el-button>
        <el-button v-if="isCondition && validationData.id" :loading="savingData" type="primary" @click="handleUpdateCondition">
          Guardar
        </el-button>
        <el-button v-if="!isUpdate && !isCondition" :loading="savingData" type="primary" @click="handleSaveData">
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
.dividerHeight {
    height: 100% !important;
}
.alignContent {
    text-align: center !important;
}
.alignTableButtons {
    text-align: end !important;
}
.conditionsBtn {
    display: grid !important;
}
.removeBtnMargin {
    margin-left: 0px !important;
}
.addMarginConditions{
    margin-top: 10px !important;
}
</style>
<style>
.maxDateWidth {
    max-width: 100% !important;
}
.popoverTest {
    width: auto !important;
}
</style>