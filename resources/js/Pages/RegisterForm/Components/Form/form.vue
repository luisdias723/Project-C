<script setup>
import { ref, watch, computed, onMounted } from 'vue';

import { Plus } from '@element-plus/icons-vue';

// Getting the props
const props = defineProps(['questions', 'steps', 'childForm', 'conditionsvalue'])

// let questions = ref(props.questions);
// let steps = ref(props.steps);
// let childForm = ref(props.childForm);

let conditions = ref(props.conditionsvalue)

let activeNames = ref(['criança 1']);

let num_child = ref(0);

// let conditions = ref({})

// Variable to store the data from form
let form = ref({});

// ----------------------------------   COMPUTED    ----------------------------------
const getRules = computed(() => {
    let rules = {}
    let qt = props.questions;
    for (var j in qt) {
        if (qt[j].mandatory) {
            var values = [{
                required: true,
                message: 'Campo Obrigatório',
                trigger: ['change', 'blur']
            }]
            rules[qt[j].id] = values
        }
    }
    return rules
});

// ----------------------------------   WATCH    ----------------------------------
watch(form, (newValue, oldValue) => {
    console.log(conditions);
    // checkCondition
    // sua lógica para lidar com as alterações no form
}, { deep: true });

watch(conditions, (newValue, oldValue) => {
    console.log('aqui camarata!');
    // checkCondition
    // sua lógica para lidar com as alterações no form
}, { deep: true });

// ----------------------------------   METHODS    ----------------------------------
// onMounted(() => {
//     checkCondition()
// })


// Methods to get and store the uploaded images
const handleAvatarSuccess = () => {
}

const beforeAvatarUpload = () => {
  return true
}

// Emmit event, this case to emmit the go back
const emit = defineEmits(['goBack', 'submit']);

// Variable to get the current active step
let active = ref(0);

// Variable for loading in the buttons
let loading = ref(false);

// Move backward in the steps
const previous = () => {
  active.value = active.value - 1;
}

// Move forward in the steps
const next = () => {
  active.value = active.value + 1;
}

// Event to go back. From form to the begin
const goBack = () => {
    emit('goBack');
}

// Method to submit the QRCode
const submit = () => {
    emit('submit');
}

const checkFilterable = (item) => {
    if (item) {
        if (item.type_question === 'country' || item.type_question === 'district' || item.type_question === 'concelho' || item.type_question === 'freguesia' || item.isTraje) {
            return true;
        }
    }
    return false;
}

// const checkCondition = () => {
//     for (var i in props.questions) {
//         let question = props.questions[i]
//         for (var j in question.conditions) {
//             console.log(form.value, question.conditions[j].question_id);
//             conditions.value[question.id] = false;
//         }
//         conditions.value[question.id] = true;
//     }
// }

</script>

<template>
    <div class="stepsContainer">
        <!-- <el-steps
            :space="200"
            :active="active"
            finish-status="success"
            align-center
        >
            <el-step v-for="item in steps" :key="item" :title="item" />
        </el-steps> -->
    </div>
    <!-- <el-form v-for="(question, idx) in questions[steps[active]]" :key="idx" ref="registerForm" class="formContainer" :model="form" label-width="150px"> -->
    <el-form v-for="(question, idx) in questions" :rules="getRules" :key="idx" ref="registerForm" class="formContainer" :model="form" label-width="200px">
        <el-form-item v-if="question.type_question === 'text' && conditions[question.id]" :label="question.description" :prop="question.id.toString()">
            <el-input v-model="form[question.id]" :placeholder="question.description" :required="question.mandatory" autofocus />
        </el-form-item>
        <el-form-item v-if="question.type_question === 'number' && conditions[question.id]" :label="question.description" :prop="question.id.toString()">
            <el-input-number v-model="form[question.id]" :min="1" />
        </el-form-item>
        <el-form-item v-if="question.type_question === 'date' && conditions[question.id]" :label="question.description" :prop="question.id.toString()">
            <el-date-picker
                v-model="form[question.id]"
                type="date"
                placeholder="Selecione um dia"
            />
        </el-form-item>
        <el-form-item v-if="(question.type_question === 'country' || question.type_question === 'district' || question.type_question === 'concelho' || question.type_question === 'freguesia' || question.type_question === 'select')  && conditions[question.id]" :label="question.description" :prop="question.id.toString()">
            <el-select v-model="form[question.id]" :filterable="checkFilterable(question)" clearable placeholder="Selecionar">
                <el-option
                    v-for="answer in question.answers"
                    :key="answer.id"
                    :label="answer.description"
                    :value="answer.id"
                />
            </el-select>
        </el-form-item>
        <!-- <el-form-item v-if="question.type_question === 'checkbox'" :class="question.prop=='child' ? 'childClass' : question.isTerms==true ? 'classTerms' : ''" :label="question.description" :prop="question.id">
            <el-checkbox v-model="form[question.prop]" :label="question.placeholder" size="large" />
        </el-form-item> -->
        <!-- Form item to add children - Collapse -->
            <!-- <el-collapse v-if="question.inputType === 'collapse' && question.isCollapse === true && form[question.condition]" v-model="activeNames">
                <el-collapse-item title="Adicionar Criança" name="addChild">
                    <el-form-item v-for="(child_form, indx) in childForm" :key="indx" :label="child_form.label">
                        <el-input v-if="child_form.inputType === 'text'" v-model="form[child_form.prop]" :placeholder="child_form.placeholder" :required="child_form  .required" autofocus />
                        <el-upload
                            v-if="child_form.inputType === 'image'"
                            class="avatar-uploader"
                            action=""
                            :show-file-list="false"
                            :on-success="handleAvatarSuccess"
                            :before-upload="beforeAvatarUpload"
                        >
                            <img v-if="form[child_form.prop]" :src="form[child_form.prop]" class="avatar" />
                            <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                        </el-upload>
                        <el-date-picker
                            v-if="child_form.inputType === 'date'"
                            v-model="form[child_form.prop]"
                            type="date"
                            placeholder="Selecione um dia"
                        />
                    </el-form-item>
                </el-collapse-item>
            </el-collapse> -->

            <!-- Add card to try to put the child form inside of it -->
            <!-- <el-card  v-if="question.inputType === 'collapse' && question.isCollapse === true && form[question.condition]" class="childContainer">
                <el-form-item v-for="(child_form, indx) in childForm" :key="indx" :label="child_form.label">
                    <el-input v-if="child_form.inputType === 'text'" v-model="form[child_form.prop]" :placeholder="child_form.placeholder" :required="child_form  .required" autofocus />
                    <el-upload
                        v-if="child_form.inputType === 'image'"
                        class="avatar-uploader"
                        action=""
                        :show-file-list="false"
                        :on-success="handleAvatarSuccess"
                        :before-upload="beforeAvatarUpload"
                    >
                        <img v-if="form[child_form.prop]" :src="form[child_form.prop]" class="avatar" />
                        <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                    </el-upload>
                    <el-date-picker
                        v-if="child_form.inputType === 'date'"
                        v-model="form[child_form.prop]"
                        type="date"
                        placeholder="Selecione um dia"
                    />
                </el-form-item>
            </el-card> -->
        
        <el-form-item v-if="question.type_question === 'image'" :label="question.description" :prop="question.id.toString()">
            <el-upload
                class="avatar-uploader"
                action=""
                :show-file-list="false"
                :on-success="handleAvatarSuccess"
                :before-upload="beforeAvatarUpload"
            >
                <img v-if="form[question.id]" :src="form[question.id]" class="avatar" />
                <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
            </el-upload>
        </el-form-item>
        <el-form-item v-if="question.type_question === 'textarea'" :label="question.description" :prop="question.id.toString()">
            <el-input
                v-model="form[question.id]"
                :autosize="{ minRows: 2, maxRows: 4 }"
                type="textarea"
                placeholder="Please input"
            />
        </el-form-item>
    </el-form>

    <div class="btnSelect">
        <el-button
            type=""
            size="large" 
            :loading="loading"
            round
            @click="goBack"
        >
            Voltar/Cancelar
        </el-button>
        <el-button v-if="active < 1"
            type="primary"
            size="large" 
            :loading="loading"
            round
            @click="next">
            Seguinte
        </el-button>
        <el-button v-if="active >= 1"
            type="primary"
            size="large" 
            :loading="loading"
            round
            @click="previous">
            Anterior
        </el-button>
        <el-button
            v-if="active >= 1"
            type="primary"
            size="large" 
            :loading="loading"
            round
            @click="submit"
        >
            Inscrever
        </el-button>
    </div>
</template>

<style scoped>
  .register-container{
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    background: rgb(238, 239, 240);
    background: linear-gradient(180deg, rgb(87, 87, 87) 0%, rgb(141, 147, 151) 100%);
  }

  h1 {
      text-align: center;
  }
  
  .log-in {
    width: 100%;
  }
  
  .el-card__body {
      padding-bottom: 2px;
  }
  
  @media only screen and (max-width: 768px) {
    .el-card {
      width: 95%;
      padding: 15px 25px;
    }
  }
  
  @media only screen and (min-width: 768px) {
    .el-card {
      width: 100%;
      max-width: 35rem;
      padding: 15px 25px;
    }
  }
  
  .forgot-password-link {
      font-size: 12px;
      margin-top: 10px;
  }
  
  .logo-container {
    background: white; 
    text-align: center;
    width: 100%;
    margin: -20px 0 20px;
  }

  .formContainer {
    margin-top: 20px !important;
  }

  .avatar-uploader .avatar {
  width: 178px;
  height: 178px;
  display: block;
}

.stepsContainer {
    max-width: 55% !important;
    margin: auto !important;
}

.btnSelect {
    text-align: center;
}
</style>

<style>
.avatar-uploader .el-upload {
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: var(--el-transition-duration-fast);
}

.avatar-uploader .el-upload:hover {
  border-color: var(--el-color-primary);
}

.el-icon.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  text-align: center;
}

.childContainer {
    margin-bottom: 0px !important;
    max-width: 100% !important;
    width: 100% !important;
}

.classTerms>div {
    margin-left: 0px !important;
}
</style>