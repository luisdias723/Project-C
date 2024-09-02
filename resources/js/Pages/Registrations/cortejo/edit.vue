<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { CircleCloseFilled, WarningFilled, SuccessFilled, EditPen, Delete } from '@element-plus/icons-vue';

import { useRoute } from 'vue-router';

import QRCode from '../../RegisterForm/Components/QRCode/inscricoes.vue';
// import QRCode from '../RegisterForm/Components/QRCode/inscricoes.vue';

import { ElMessage, ElMessageBox } from 'element-plus'

import Resource from '@/api/resource';
import FormResource from '@/api/formulario';
import RegistrationResource from '@/api/registration';

const countryResource = new FormResource();

const registrationsResource = new Resource('registrations');
const regResource = new RegistrationResource();

let loading = ref(false);
let title = ref('');
let formData = ref({});
let dialogVisible = ref(false);
let query = {
    total: 0,
    page: 1,
    keyword: '',
    status: null,
    limit: 25
}
const route = useRoute();

let form = ref({
  template: null,
  obs: '',
  isTemplate: false,
  notify: 0,
  urllink: true,
  registration_id: 0
})

let imageDialog = ref(false);
let obsDialog = ref(false);

let status = ref();

let historyData = ref();

let templates = ref();

let oldStatus_id = null;

let obsRules = ref({
  notify: [{ required: true, message: 'Campo Obrigatório', trigger: ['change', 'blur']}],
});

let generateQR = ref(false);
let saveQR = ref(false);

let textQR = ref("");

let questions = ref({});

let conditions = ref({});

let answers = ref({});

let rules = ref({});

let selectedCountry = ref([]);

let codes = ref({});

let formGroup = ref([]);

let formGroupData = ref({});

let questionsForm = ref({});

let question_quadro = ref(null);

let question_trajes = ref(null);

let formGroupDataRules = ref([]);

let activeNames = ref(['1']);

// ---------------------------------------------- Computed ----------------------------------------------
const filteredAnswers = computed(() => {
  return formData.value.answers.filter(answer => answer.answer !== '' && answer.images.length <= 0);
});

// ---------------------------------------------- Watch ----------------------------------------------
watch(answers, (newValue, oldValue) => {
    checkCondition();
    generateRules();
    // sua lógica para lidar com as alterações no form
}, { deep: true });

// ---------------------------------------------- Methods ----------------------------------------------

onMounted(() => {
    getList()
    getStatus()
    getTemplates()
    getCodes()
})

const disabledDate = (time) => {
    const start = new Date(1924, 6, 31).getTime(); // 30 de junho de 1924
    const end = new Date(2010, 11, 31).getTime(); // 31 de dezembro de 2010
    return time.getTime() < start || time.getTime() > end;
  };

const getList = () => {
    // loading.value = true;
    let id = route.params.id;
    registrationsResource.get(id).then(response => {
      if (response) {
        formData.value = response ?? [];
        answers.value = response.answers ?? [];
        questions.value = response.questions ?? [];
        oldStatus_id = response.status_id;
        form.value.registration_id = formData.value.id;
        formGroup.value = response.props;
        formGroupData.value = response.participant_answers;
        questionsForm.value = response.participant_questions;
        question_quadro.value = response.question_board ?? 0;
        question_trajes.value = response.question_trajes ?? 0;
        getHistory();
        if (!formData.value.qrCode) {
          textQR.value = formData.value.qrCodeText;
          generateQR.value = true;
        }
      }
      // loading.value = false;
    }).catch(error => {
        // loading.value = false;
        console.log(error);
    });
}

const generateRules = () => {
//   let qt = questions.value;
//   for (var j in qt) {
//       if (qt[j].mandatory && conditions.value[qt[j].id]) {
//           var values = [];
//           values.push({
//               required: true,
//               message: 'Campo Obrigatório',
//               trigger: ['change', 'blur']
//           });
//           if (qt[j].type_question == 'number') {
//             if (qt[j].description == 'Nº CC') {
//               values.push({ validator: validateNumberCC, trigger: ['blur', 'change'] });
//             } else {
//               values.push({ validator: validateNumber, trigger: ['blur', 'change'] });
//               if (qt[j].num_digits > 0) {
//                 const numDigits = parseInt(qt[j].num_digits, 10);
//                 values.push({ validator: (rule, value, callback) => {
//                   if (value.length > numDigits) {
//                     return callback(new Error(`Este campo não pode conter mais que ${numDigits} caracteres`));
//                   }
//                   else {
//                     return callback();
//                   }
//                 }, trigger: ['blur', 'change'] });
//               }
//             }
//           }
//           if (qt[j].type_question == 'phone') {
//             values.push({ validator: validatePhone, trigger: ['blur', 'change'] });
//           }
//           if (qt[j].type_question == 'email') {
//             values.push({type: 'email', message: 'Insira um endereço de email válido', trigger: ['blur', 'change'] });
//           }
//           if (qt[j].type_question == 'date') {
//             values.push({ validator: validateDateRange, trigger: ['blur', 'change'] });
//           }
//           rules.value[qt[j].id] = values;
//       }else {
//         rules.value[qt[j].id] = [];
//       }
//   }
}
const checkCondition = () => {
  for (var i in questions.value) {
    let question = questions.value[i];
    conditions.value[question.id] = false;
    // let final_value = false;
    // if (question.conditions.length <= 0) final_value = true;
    // for (var j in question.conditions) {
    //   if (question.conditions[j].rule == '==') {
    //     let value = answers.value[question.conditions[j].question_id] == question.conditions[j].answer_id;
    //     if (!question.conditions[j].status) value = !value;
    //     final_value = value;
    //   }
    //   if (question.conditions[j].rule == '!=') {
    //     let value = answers.value[question.conditions[j].question_id] != question.conditions[j].answer_id;
    //     if (!question.conditions[j].status) value = !value;
    //     final_value = value;
    //   }
    //   if (question.conditions[j].rule == '<=') {
    //     let value = answers.value[question.conditions[j].question_id] <= question.conditions[j].answer_id;
    //     if (!question.conditions[j].status) value = !value;
    //     if (question.conditions[j].concat_condition && question.conditions[j].concat_condition == '&&'){
    //       value = final_value == true && value == true ? true : false;
    //     } else {
    //       value = final_value == true ? true : value;
    //     }
    //     final_value = value;
    //   }
    //   if (question.conditions[j].rule == '>=') {
    //     let value = answers.value[question.conditions[j].question_id] >= question.conditions[j].answer_id;
    //     if (!question.conditions[j].status) value = !value;
    //     if (question.conditions[j].concat_condition && question.conditions[j].concat_condition == '&&'){
    //       value = final_value == true && value == true ? true : false;
    //     } else {
    //       value = final_value == true ? true : value;
    //     }
    //     final_value = value;
    //   }
    // }
    if (answers.value[question.id] != '' && answers.value[question.id] != '0' && answers.value[question.id] != 0 && answers.value[question.id] != 'null' && answers.value[question.id] != null) {
      conditions.value[question.id] = true;
    } else {
      conditions.value[question.id] = false;
    }
    if (question.isSpecialBoards) {
      conditions.value[question.id] = true;
    }
  }
}

const validateNumber = (rule, value, callback) => {
  const numberPattern = /^[0-9]+$/;
  if (!value) {
    return callback(new Error('Campo obrigatório'));
  } else if (!numberPattern.test(value)) {
    return callback(new Error('Este campo deve conter apenas números'));
  }
  else {
    return callback();
  }
};

const validateNumberCC = (rule, value, callback) => {
  const citizenCardPattern = /^[0-9]{8}[0-9][0-9A-Za-z]{2}[0-9]$/;
  if (!value) {
    return callback(new Error('Campo obrigatório'));
  } else if (!citizenCardPattern.test(value)) {
    return callback(new Error('Número inválido. Deve conter 12 caracteres (Sem espaçamentos)'));
  } else {
    return callback();
  }
};

const validateMaxDigits = (rule, value, callback, max_digits) => {
  if (value.length > max_digits) {
    return callback(new Error(`Este campo não pode conter mais que ${max_digits} caracteres`));
  }
  else {
    return callback();
  }
};

const validatePhone = (rule, value, callback) => {
  const numberPattern = /^[0-9]+$/;
  if (!value) {
    return callback(new Error('Campo obrigatório'));
  } else if (!numberPattern.test(value)) {
    return callback(new Error('Este campo deve conter apenas números'));
  } else {
    return callback();
  }
};

const validateDateRange = (rule, value, callback) => {
  if (!value) {
    callback(new Error('Campo obrigatório'));
  } else {
    const date = new Date(value);
    const start = new Date(1924, 5, 30); // 30 de junho de 1924
    const end = new Date(2010, 11, 31); // 31 de dezembro de 2010

    if (date < start || date > end) {
      callback(new Error(`Data não cumpre as Condições de Participação`));
    } else {
      callback();
    }
  }
}

const checkFilterable = (item) => {
    if (item) {
        if (item.type_question === 'country' || item.type_question === 'district' || item.type_question === 'concelho' || item.type_question === 'freguesia' || item.isTraje) {
            return true;
        }
    }
    return false;
}

const getCodes = () => {
  countryResource.getCountries().then(data => {
    codes.value = data;
  }).catch(error => {
      console.log(error);
  });
}

const getTemplates = () => {
  regResource.getTemplates().then(response => {
    if (response) {
      templates.value = response;
    }
  }).catch(error => {
    // loading.value = false;
    console.log(error);
  });
}

const getStatus = () => {
  regResource.getStatus().then(response => {
    if (response && response.data) {
      status.value = response.data;
    }
  }).catch(error => {
    // loading.value = false;
    console.log(error);
  });
}

const getHistory = () => {
  regResource.getHistory({id: formData.value.id}).then(response => {
    if (response) {
      historyData.value = response;
    }
  }).catch(error => {
    console.log(error);
  });
}

const getTagType = (status_id) => {
    switch (status_id) {
        case 3:
          return 'success';
        case 2:
          return 'danger';
        default:
          return 'warning';
    }
}

const openPreview = () => {
  imageDialog.value = true;
}

const handleClose = () => {
  imageDialog.value = false;
}

const openObs = () => {
  // Se for reprovada ou aprovada deve abrir a modal e só depois guardar tudo
  if (formData.value.status_id == 2 || formData.value.status_id == 3) {
    obsDialog.value = true;
    // Se for 'Em análise' guarda automaticamente o estado!!!
  } else {
    canChange();
  }
}

const closeObsDialog = () => {
  obsDialog.value = false;
  form.value = {
    template: null,
    obs: '',
    isTemplate: false,
    notify: 0,
    urllink: true,
    registration_id: 0
  };
  loading.value = false;
}

const editTraje = async (selectedAnswer) => {
  // Quando não edita a resposta tem de voltar ao valor antigo!
  try {
    await ElMessageBox.confirm(
      'Pretende alterar o valor da resposta dada?',
      'Confirmação',
      {
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        type: 'warning',
      }
    );
    updateAnswerGiven(selectedAnswer);
  } catch (error) {
    data.answer = data.old_answer;
    displayMessage('info', 'Operação cancelada')
  }
}

const canChange = async () => {
  const selectedStatus = status.value.find(status => status.id === formData.value.status_id);
  if (selectedStatus) {
    const newStatus = selectedStatus.description;

    try {
      await ElMessageBox.confirm(
        'O estado da inscrição será alterado para ' + newStatus,
        'Confirmação',
        {
          confirmButtonText: 'Confirmar',
          cancelButtonText: 'Cancelar',
          type: 'warning',
        }
      );
      updateData();
    } catch (error) {
      formData.value.status_id = oldStatus_id;
      displayMessage('info', 'Operação cancelada')
    }
  }
}

const updateData = () => {
  regResource.updateCortejoStatus(formData.value).then(response => {
    if (response) {
      displayMessage('success', 'Estado alterado')
      formData.value.status_des = response;
      oldStatus_id = formData.value.status_id;
      if (obsDialog.value == true) closeObsDialog()
    } else {
      formData.value.status_id = oldStatus_id;
      displayMessage('error', 'Não foi possível alterar o estado');
      loading.value = false;
    }
  }).catch(error => {
    console.log(error);
    displayMessage('error', 'Não foi possível alterar o estado');
    loading.value = false;
  });
}

const updateAnswerGiven = (data) => {
  regResource.updateAnswer(data).then(response => {
    if (response) {
      displayMessage('success', 'Resposta editada');
      data.old_answer = data.answer;
    } else {
      data.answer = data.old_answer;
      displayMessage('error', 'Não foi possível alterar o estado')
    }
  }).catch(error => {
    console.log(error);
    displayMessage('error', 'Não foi possível alterar o estado')
  });
}

const saveObs = async () => {
  loading.value = true;
  if (!obsFormRef) {
    displayMessage('error', 'Não foi possível alterar o estdo da inscrição. Tente novamente.');
    loading.value = false;
    return;
  }
  await obsFormRef.value.validate(async (valid, fields) => {
    if (valid) {
      try {
        await ElMessageBox.confirm(
          'Pretende atualizar o estado da inscrição?',
          'Confirmação',
          {
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            type: 'warning',
          }
        );
        formData.value = {
                      ...formData.value,
                      ...form.value
                    }
        updateData();
      } catch (error) {
        closeObsDialog()
      }
    } else {
      displayMessage('error','Devem ser preenchidos todos os campos obrigatórios.');
      loading.value = false;
    }
  });
}

const obsFormRef = ref(null);

const displayMessage = (type, message) => {
  ElMessage({
    type: type,
    message: message,
  })
}

const selectTemplate = () => {
  const selectedTemplate = templates.value.find(template => template.id === form.value.template);
  if (selectedTemplate) {
    form.value.obs = selectedTemplate.obs;
  }


  const descriptions = form.value.template.map(option => {
    const foundOption = templates.value.find(o => o.id === option);
    return foundOption ? foundOption.obs : '';
  }).join('\n');

  const currentContent = form.value.obs.split('\n').filter(line => {
    return !templates.value.some(option => line === option.obs);
  }).join('\n');

  form.value.obs = currentContent + (currentContent ? '\n' : '') + descriptions;
}

const storeQR = (img) => {
  let dataToPass = new FormData();
  dataToPass.append('file', img);
  dataToPass.append('id', form.value.registration_id);
  dataToPass.append('data', JSON.stringify(formData.value));

  regResource.saveQRCodeCortejo(dataToPass).then(response => {
    if (response && response != 0) {
      formData.value.qrCode = response;
      saveQR.value = false;
    } else {
      generateQR.value = false;
    }
  }).catch(error => {
    console.log(error);
  });
}

const submit = () => {
  loading.value = true;
  let dataToPass = {};
  dataToPass['participations'] = {};
  for (let j in questions.value) {
    let question = questions.value[j];
    let value = answers.value[question.id];
    if (answers.value[question.id] && answers.value[question.id].prefix) {
      value = '+' + answers.value[question.id].prefix + ' ' + answers.value[question.id].number;
      answers.value[question.id] = value;
    }
    for (let l in formGroup.value) {
      if (!dataToPass['participations'][l]) dataToPass['participations'][l] = {};
      let val = formGroupData.value[l + question.id] ?? null;
      if (formGroupData.value[l + question.id] && formGroupData.value[l + question.id].prefix) {
        val = '+' + formGroupData.value[l + question.id].prefix + ' ' + formGroupData.value[l + question.id].number;
      }
      dataToPass['participations'][l][question.id] = val;
    }
  }
  dataToPass['answers'] = answers.value;
  // dataToPass['participations'] = formGroupData.value;
  dataToPass['id'] = form.value.registration_id;
  console.log(dataToPass);
  // TODO: Fazer um pedido para guardar os dados! Registar sempre quem fez a atualização!
  regResource.editCortejoAnswer(dataToPass).then(response => {
    if (response && response != 0) {
      displayMessage('success', 'Inscrição guardada com sucesso.');
      loading.value = false;
      getList();
    } else {
      displayMessage('error', 'Não foi possível guardar a inscrição');
      loading.value = false;
    }
  }).catch(error => {
    loading.value = false;
    displayMessage('error', 'Não foi possível guardar a inscrição');
  });
}

const changeBoard = () => {
  answers.value[question_trajes.value] = null;
  for (var j in formGroup.value) {
    formGroupDataRules.value[j.toString() + question_trajes.value.toString()] = [];
    formGroupData.value[j.toString() + question_trajes.value.toString()] = null;
    formGroupDataRules.value[j.toString() + question_trajes.value.toString()].push({
      required: true,
      message: 'Campo Obrigatório',
      trigger: ['change', 'blur']
    });
  }
}
</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <el-row>
            <el-col :span="12">
                <el-card v-if="formData && formData.answers">
                  <template #header>
                    <div class="card-header">
                      <h3>Dados da inscrição - <b>{{ formData.code ?? '' }}</b><el-tag v-if="formData.status_id" class="addLMargin" :type="getTagType(formData.status_id)">{{ formData.status_des }}</el-tag></h3>
                      <!-- <h5 v-if="!formData.isOlder"><el-tag type="danger">Esta inscrição deve conter responsável!</el-tag></h5> -->
                    </div>
                  </template>
                  <el-form ref="formRef" class="formContainer" :model="answers" label-width="auto" label-position="left">
                    <div v-for="(question, idx) in questions" :key="idx">
                      <el-form-item v-if="conditions[question.id] && question.type_question !== 'image'" :label="question.description" :prop="question.id.toString()" class="opensansName">
                          <!-- Text -->
                          <el-input class="opensansName" v-if="(question.type_question === 'text' || question.type_question === 'email') && conditions[question.id]" v-model="answers[question.id]" :placeholder="question.description" :required="question.mandatory" autofocus />
                          <!-- Phone number -->
                          <div class="displayPhoneNumber" v-if="question.type_question === 'phone' && conditions[question.id]">
                            <el-select class="opensansName" v-model="answers[question.id].prefix" filterable clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName" 
                                v-for="code in codes"
                                :key="code.id"
                                :label="code.country  + ' +' + code.code"
                                :value="code.code"
                            />
                          </el-select>
                          <el-input
                            class="opensansName"
                            v-model="answers[question.id].number"
                          />
                          </div>
                          <!-- Number -->
                          <el-input class="opensansName" v-model="answers[question.id]" v-if="question.type_question === 'number' && conditions[question.id]" />
                          <!-- Date -->
                          <el-date-picker
                              v-if="question.type_question === 'date' && conditions[question.id]"
                              class="opensansName"
                              v-model="answers[question.id]"
                              format="YYYY-MM-DD"
                              value-format="YYYY-MM-DD"
                              type="date"
                              placeholder="Selecione um dia"
                              :disabled-date="disabledDate"
                          />
                          <!-- Country -->
                          <el-select style="width: 100%" class="opensansName" v-if="(question.type_question === 'country' || question.type_question === 'district' || question.type_question === 'concelho' || question.type_question === 'freguesia' || question.type_question === 'select')  && conditions[question.id] && !question.isSpecialBoards" v-model="answers[question.id]" :filterable="checkFilterable(question)" clearable placeholder="Selecionar" @change="changeBoard">
                            <el-option
                                v-for="answer in question.answers"
                                class="opensansName"
                                :key="answer.id"
                                :label="answer.description"
                                :value="answer.id"
                            />
                          </el-select>
                          <el-select style="width: 100%" class="opensansName" v-if="question.isSpecialBoards && question.type_question === 'select'  && conditions[question.id]" v-model="answers[question.id]" :filterable="checkFilterable(question)" clearable placeholder="Selecionar">
                            <el-option
                                v-for="answer in question.answers[answers[question_quadro]]"
                                class="opensansName"
                                :key="answer.id"
                                :label="answer.description"
                                :value="answer.id"
                            />
                          </el-select>
                          <!-- Checkbox -->
                          <el-checkbox class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && question.multiple" v-model="answers[question.id]" label="Option 1" />
                          <el-radio-group class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && !question.multiple" v-model="answers[question.id]">
                            <el-radio
                              v-for="answer in question.answers"
                              class="opensansName"
                              :key="answer.id"
                              :label="answer.id">{{ answer.description }}</el-radio>
                          </el-radio-group>
                          <!-- Fazer o autocomplete para o nome dos ranchos -->
                          <el-autocomplete
                            v-if="question.type_question === 'rancho'"
                            v-model="answers[question.id]"
                            :fetch-suggestions="(queryString, cb) => querySearch(queryString, cb, question.answers)"
                            :trigger-on-focus="false"
                            clearable
                            class="opensansName"
                            :placeholder="question.description"
                          />
                          <!-- Textarea -->
                          <el-input
                              v-if="question.type_question === 'textarea'"
                              class="opensansName"
                              v-model="answers[question.id]"
                              :autosize="{ minRows: 2, maxRows: 4 }"
                              type="textarea"
                              disabled
                              :placeholder="question.description"
                          />
                      </el-form-item>
                    </div>
                  </el-form>
                  <div v-if="formGroup.length > 0" class="demo-collapse">
                    <el-collapse v-model="activeNames">
                      <el-collapse-item :title="formGroup.length > 0 && formGroup.length < 2 ? 'Participante' : 'Participantes'" name="1">
                        <el-tabs type="border-card">
                          <el-tab-pane v-for="(group, idx_group) in formGroup" :key="idx_group" :label="'Participante ' + (idx_group + 2)">
                            <div v-for="(qt_form, idx_form) in questionsForm" :key="idx_form">
                              <el-form ref="formRef" class="formContainer" :rules="formGroupDataRules" :model="formGroupData" label-width="auto" label-position="left">
                                <el-form-item v-if="(formGroupData[idx_group.toString() + qt_form.id.toString()] != null && formGroupData[idx_group.toString() + qt_form.id.toString()] != 0 && formGroupData[idx_group.toString() + qt_form.id.toString()] != 'null' && formGroupData[idx_group.toString() + qt_form.id.toString()] != '0') || qt_form.isSpecialBoards" :label="qt_form.description" :prop="idx_group.toString() + qt_form.id.toString()" class="opensansName" >
                                        <!-- Text -->
                                    <el-input class="opensansName"
                                        v-if="(qt_form.type_question === 'text' || qt_form.type_question === 'email')"
                                        v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :placeholder="qt_form.description"
                                        :required="qt_form.mandatory" autofocus />

                                        <!-- Phone number -->
                                        <div class="displayPhoneNumber"
                                          v-if="qt_form.type_question === 'phone'">
                                          <el-select class="opensansName" v-model="formGroupData[idx_group.toString() + qt_form.id.toString()].prefix"
                                              filterable clearable placeholder="Selecionar">
                                              <el-option v-for="code in codes" class="opensansName"
                                                  :key="code.id" :label="code.country + ' +' + code.code"
                                                  :value="code.code" />
                                          </el-select>
                                          <el-input class="opensansName" v-model="formGroupData[idx_group.toString() + qt_form.id.toString()].number" />
                                        </div>

                                        <!-- Date -->
                                        <el-date-picker v-if="qt_form.type_question === 'date'"
                                            class="opensansName" v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" format="YYYY-MM-DD"
                                            value-format="YYYY-MM-DD" type="date" placeholder="Selecione um dia" @change="(val) => checkMaiorIdade(val, idx_group.toString() + qt_form.id.toString())" />
                                            <!-- Country -->
                                        <el-select style="width:75%" class="opensansName"
                                            v-if="(qt_form.type_question === 'country' || qt_form.type_question === 'district' || qt_form.type_question === 'concelho' || qt_form.type_question === 'freguesia' || qt_form.type_question === 'select') && !qt_form.isSpecialBoards && !qt_form.isSpecial"
                                            v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :filterable="checkFilterable(qt_form)" clearable
                                            placeholder="Selecionar">
                                            <el-option v-for="answer in qt_form.answers" class="opensansName"
                                                :key="answer.id" :label="answer.description"
                                                :value="answer.id" />
                                        </el-select>

                                        <!-- Trajes when board selected -->
                                        <el-select style="width:75%" class="opensansName"
                                            v-if="(qt_form.type_question === 'select') && qt_form.isSpecialBoards"
                                            v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :filterable="checkFilterable(qt_form)" clearable
                                            placeholder="Selecionar">
                                            <el-option v-for="answer_p in qt_form.answers[answers[question_quadro]]" class="opensansName"
                                                :key="answer_p.id" :label="answer_p.description"
                                                :value="answer_p.id" />
                                        </el-select>
                                </el-form-item>
                              </el-form>
                            </div>
                          </el-tab-pane>         
                        </el-tabs>
                      </el-collapse-item>
                    </el-collapse>
                  </div>
                  <div class="btnSelect">
                    <el-button
                        type="primary"
                        size="large" 
                        :loading="loading || saveQR"
                        @click="submit"
                    >
                        Guardar Dados
                    </el-button>
                  </div>
                </el-card>
              <el-card v-else><h3>Não existem dados relativos a esta inscrição</h3></el-card>
            </el-col>
            <el-col :span="12">
              <el-card>
                <el-row :gutter="20" class="alignRight">
                  <!-- <el-button type="primary" class="addMargin" @click="openObs">Adicionar Observação</el-button> -->
                  <el-select
                    v-model="formData.status_id"
                    placeholder="Selecionar"
                    style="width: 240px"
                    @change="openObs"
                    :disabled="saveQR"
                  >
                    <el-option
                      v-for="item in status"
                      :key="item.id"
                      :label="item.description"
                      :value="item.id"
                    />
                  </el-select>
                  <el-carousel
                    v-if="formData.images"
                      :interval="4000"
                      type="card"
                      style="margin-top: 20px"
                    >
                    <!-- Mudar a imagem recebida para ser armazenada dentro do public e não dentro do storage/app/public -->
                      <el-carousel-item v-for="item in formData.images" :key="item">
                        <img 
                        :src="item"
                        class="carousel-img"
                        @click="openPreview(image)"
                        >
                        <!-- <p>{{item}}</p> -->
                      </el-carousel-item>
                    </el-carousel> 
                </el-row>
              </el-card>
              <el-card style="max-height: 425px; overflow-y: auto">
                <template #header>
                  <div class="card-header">
                    <span>Histórico</span>
                  </div>
                </template>
                <el-timeline>
                  <el-timeline-item
                    v-for="(history, index_h) in historyData"
                    :key="index_h"
                    :timestamp="history.created_at"
                  >
                  <el-card v-if="history.isObs">
                    <p class="opensansName"><b>{{ history.description }}</b></p>
                  </el-card>
                    <p class="opensansName" v-else>{{ history.description }}</p>
                  </el-timeline-item>
                </el-timeline>
              </el-card>
              <el-card v-if="formData && formData.answers">
                <template #header>
                  <div class="card-header">
                    <h3>Código QR da Inscrição</h3>
                  </div>
                </template>
                <el-image v-if="formData.qrCode" :src="formData.qrCode" />
                <QRCode v-else :generate="generateQR" @store-Image="storeQR" :text="textQR"></QRCode>
                <p v-if="!generateQR && !formData.qrCode">O código ainda não foi gerado para esta inscrição. Deve ser alterado o estado.</p>
              </el-card>
            </el-col>
        </el-row>
    </el-card>
    <el-dialog
    v-model="obsDialog"
    title="Adicionar Observações"
    width="50%"
    :before-close="closeObsDialog"
  >
    <el-form :model="form" ref="obsFormRef" :rules="obsRules" label-width="auto" style="max-width: 600px">
      <el-form-item label="Selecionar template">
        <el-select
          v-model="form.template"
          filterable
          multiple
          placeholder="Selecionar"
          @change="selectTemplate"
        >
          <el-option
            v-for="item in templates"
            :key="item.id"
            :label="item.subject"
            :value="item.id"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="Observações" prop="obs">
        <el-input v-model="form.obs" type="textarea"  :rows="6"/>
      </el-form-item>
      <el-form-item v-if="formData.status_id == 2" label="Enviar Link?" prop="notify">
        <el-switch
          v-model="form.urllink"
          active-text="Sim"
          inactive-text="Não"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button :loading="loading || saveQR" @click="closeObsDialog">Cancelar</el-button>
        <el-button :loading="loading || saveQR" type="primary" @click="saveObs">
          Guardar
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<style scoped>
.el-carousel__item h3 {
  color: #475669;
  opacity: 0.75;
  line-height: 200px;
  margin: 0;
  text-align: center;
}

.el-carousel__item:nth-child(2n) {
  background-color: #99a9bf;
}

.el-carousel__item:nth-child(2n + 1) {
  background-color: #d3dce6;
}

.carousel-img {
    width: 100%;
    height: auto;
    max-height: 100%; /* Ajuste conforme necessário */
    object-fit: cover;
}
.alignRight{
  display: block !important;
  text-align: right !important;
  margin: 10px 0px !important;
}
.addMargin {
  margin-right: 10px !important;
}
.addLMargin {
  margin-left: 10px !important;
}
.dangerSpan{
  color: #f56c6c !important;
  margin-left: 10px !important;
}
.successSpan{
  color: #67C23A !important;
  margin-left: 10px !important;
}
.light-gray-background {
  background-color: #f0f0f0; /* Cor cinza claro */
}
.formDataClass .el-form-item {
  margin-bottom: 0px !important;
  padding: 5px 0px !important;
}
.btnSelect {
  text-align: center;
}
.displayPhoneNumber {
  display: flex !important;
  width: 100% !important;
}
</style>
<style>
.el-carousel__item {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%; /* Certifique-se de que o contêiner ocupe toda a altura */
}

.el-timeline-item__timestamp.is-bottom {
  font-family: 'openSans', sans-serif !important;
}
</style>