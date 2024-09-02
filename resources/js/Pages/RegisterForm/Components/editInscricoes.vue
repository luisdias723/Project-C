<script setup>
import { ref, onMounted, computed, watch } from 'vue';
// Import Form Conmponent
// import FormComponent from './Form/form.vue';
// // Import QRCode component
// import QRCodeComponent from './QRCode/inscricoes.vue';

const props = defineProps(['checklist', 'mobile', 'query', 'active']);

import { ElMessage, ElMessageBox } from 'element-plus'

import FormResource  from '@/api/formulario';
import { Delete } from '@element-plus/icons-vue';

import notAvailable from './notAvailable.vue';

import Thanks from './tanks.vue';

const emit = defineEmits(['goBack']);

const countryResource = new FormResource();
const answersResource = new FormResource();

const labelPosition = computed(() => {
  return props.mobile ? 'top' : 'left';
});

const dialogWidth = computed(() => {
  return props.mobile ? '100%' : '50%';
});

const uploadClass = computed(() => {
  return props.mobile ? 'mobileUpload opensansName upload-demo' : 'desktopUpload opensansName upload-demo';
});

let privacyDialog = ref(false);

// const formRef = ref<FormInstance>();
const formRef = ref(null);


// Variable to pass to qrcodecomponent
let displayQRCode = ref(false);
let message = ref("Obrigado por se registar no desfile de mordomia da romaria da Sra d'Agonia. Irá necessitar deste Código QR para realizar a participação.");

// Variable to store the data of steps
// let steps = ['Dados Pessoais', 'Dados Participação']
let steps = [];

let progress = ref(0);

// Variable to store the data from form
let form = ref({});

let editForm = ref({});

// Variable to store tehe rules
let rules = ref({});

const editFormRef = ref(null);

let canEdit = ref(false);

let submitted = ref(false);

const termsConditions = '<span class="opensansName">Dou o meu consentimento e autorizo a recolha e tratamento dos meus dados pessoais, tendo por finalidade o objetivo deste formulário e li e aceito tanto a <el-link :underline="false" href="" download="Politica-privacidade.pdf">Política de privacidade</el-link> como a política de participação. ATUALMENTE EM FALTA ESTAS POLÍTICAS!</span>';

// ----------------------------------   COMPUTED    ----------------------------------

// ----------------------------------   WATCH    ----------------------------------
watch(form, (newValue, oldValue) => {
    checkCondition();
    generateRules();
    // sua lógica para lidar com as alterações no form
}, { deep: true });

let questions = ref({});
let loading = ref(false);

let codes = ref({});

let conditions = ref({});

let editRules = ref({});

onMounted(() => {
    // getList();
    editRules.value = {
        code: [{ required: true, message: 'Campo Obrigatório', trigger: ['blur']}, { validator: validateFourDigits, trigger: ['change', 'blur'] }],
        identification: [{ required: true, message: 'Campo Obrigatório', trigger: ['blur']}],
        email: [{ required: true, message: 'Campo Obrigatório', trigger: ['blur']}, {type: 'email', message: 'Insira um endereço de email válido', trigger: ['blur'] }],
    };
    getCodes();
    getForm();
})

const getForm = () => {
  progress.value += 5;
  loading.value = true;
  editForm.value.id = props.query;
  answersResource.editForm(editForm.value).then(data => {
    if (data && data.questions && data.answers) {
      loading.value = false;

      questions.value = data.questions;
      form.value = data.answers;
      registration.value = data.registration;
      canEdit.value = true;
      progress.value += 20;
      incrementProgress();
    } else {
      ElMessage({
        message: 'Não foi possível obter a sua inscrição',
        type: 'error',
        duration: 5000
      })
      loading.value = false;
      goBack();
    }
  }).catch(error => {
      console.log(error);
      if (error && error.request && error.request.status && error.request.status == '403') {
        goBack();
      } else {
        ElMessage({
          message: 'Não foi possível obter a sua inscrição',
          type: 'error',
          duration: 5000
        });
        goBack();
      }
      loading.value = false;
  });
}

const goBack = () => {
    emit('goBack');
}

const validateFourDigits = (rule, value, callback) => {
  if (!value) {
    return callback(new Error('Campo obrigatório'));
  } else if (!/^\d{4,7}$/.test(value)) {
    callback(new Error('Inserir somente os dígitos do código fornecido. Exemplo: 0000'));
  } else {
    callback();
  }
};

const getCodes = () => {
  countryResource.getCountries().then(data => {
    codes.value = data;
  }).catch(error => {
      console.log(error);
  });
}

const incrementProgress = () => {
    if (progress.value < 100) {
        setTimeout(() => {
            progress.value += 25;
            incrementProgress();
        }, 500);
    } else {
        progress.value = 100;
        loading.value = false;
    }
} 

// Submit method and get the qrcode
const submit = async () => {
  loading.value = true;
  if (!formRef) {
    ElMessage({
      message: 'Não foi possível enviar a sua inscrição. Tente novamente.',
      type: 'error',
      duration: 5000
    });
    loading.value = false;
    return;
  }
  try {
    await ElMessageBox.confirm(
      'Pretende submeter a sua inscrição?',
      'Confirmação',
      {
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        type: 'warning',
      }
    );
    await formRef.value.validate((valid, fields) => {
      if (valid) {
        let formData = new FormData();
        formData.append('checklist', props.checklist);
        formData.append('registration', registration.value);
        for (let j in questions.value) {
          let question = questions.value[j];
          
          if (question.type_question === 'image') {
            let oldFiles = form.value[question.id].cur_img;
            if (oldFiles.length <= 0) formData.append(`old_files[${question.id}]`, oldFiles);
            for (let i in oldFiles) {
              formData.append(`old_files[${question.id}][]`, oldFiles[i]);
            }
            let files = form.value[question.id].new_img;
            if (files.length <= 0) formData.append(`files[${question.id}]`, files);
            for (let i in files) {
              formData.append(`files[${question.id}][]`, files[i].raw);
            }
          } else {
            let value = form.value[question.id];
            if (form.value[question.id] && form.value[question.id].prefix) {
              value = '+' + form.value[question.id].prefix + ' ' + form.value[question.id].number;
            }
            formData.append(`answer[${question.id}]`, value);
          }
        }

        answersResource.updateAnswers(formData).then(data => {
          if (data) {
            ElMessage({
              message: 'A sua inscrição foi enviada com sucesso',
              type: 'success',
              duration: 5000
            });
            loading.value = false;
            submitted.value = true;
          } else {
            ElMessage({
              message: 'Não foi possível enviar a sua inscrição',
              type: 'error',
              duration: 5000
            });
            loading.value = false;
          }
        }).catch(error => {
            console.log(error);
            ElMessage({
              message: 'Não foi possível enviar a sua inscrição. Tente novamente',
              type: 'error',
              duration: 5000
            });
            loading.value = false;
        });
      } else {
        ElMessage({
          message: 'Devem ser preenchidos todos os campos obrigatórios.',
          type: 'error',
          duration: 5000
        });
        loading.value = false;
      }
    })
  } catch (error) {
    loading.value = false;
    displayMessage('info', 'Operação cancelada');
  }
}

let registration = ref(null);

// Submit edit form
// const editFormSubmit = async () => {
//     loading.value = true;
//   if (!editFormRef) {
//     ElMessage({
//       message: 'Não foi possível registar a sua inscrição. Tente novamente.',
//       type: 'error',
//       duration: 5000
//     })
//     loading.value = false;
//     return;
//   }
//   await editFormRef.value.validate((valid, fields) => {
//     if (valid) {
//       editForm.value.id = props.query;
//       answersResource.editForm(editForm.value).then(data => {
//         if (data && data.questions && data.answers) {
//           ElMessage({
//             message: 'Os dados introduzidos encontram-se corretos',
//             type: 'success',
//             duration: 5000
//           })
//           loading.value = false;

//           questions.value = data.questions;
//           form.value = data.answers;
//           registration.value = data.registration;
//           canEdit.value = true;
//         } else {
//           ElMessage({
//             message: 'Não foi possível obter a sua inscrição',
//             type: 'error',
//             duration: 5000
//           })
//           loading.value = false;

//         }
//       }).catch(error => {
//           console.log(error);
//           if (error && error.request && error.request.status && error.request.status == '403') {
//           } else {
//             ElMessage({
//               message: 'Não foi possível obter a sua inscrição',
//               type: 'error',
//               duration: 5000
//             });
//           }
//           loading.value = false;
//       });
//     } else {
//       loading.value = false;
//       ElMessage({
//         message: 'Devem ser preenchidos todos os campos obrigatórios.',
//         type: 'error',
//         duration: 5000
//       })
//     }
//   })
//   // displayQRCode.value = true;
// }

const generateRules = () => {
  let qt = questions.value;
  for (var j in qt) {
      if (qt[j].mandatory && conditions.value[qt[j].id]) {
          var values = [];
          values.push({
              required: true,
              message: 'Campo Obrigatório',
              trigger: ['blur']
          });
          // if (qt[j].type_question == 'number') {
          //   values.push({ validator: validateNumber, trigger: ['blur', 'change'] });
          //   if (qt[j].num_digits > 0) {
          //     const numDigits = parseInt(qt[j].num_digits, 10);
          //     values.push({ validator: (rule, value, callback) => {
          //       if (value.length > numDigits) {
          //         return callback(new Error(`Este campo não pode conter mais que ${numDigits} caracteres`));
          //       }
          //       else {
          //         return callback();
          //       }
          //     }, trigger: ['blur', 'change'] });
          //   }
          // }
          // if (qt[j].type_question == 'phone') {
          //   values.push({ validator: validatePhone, trigger: ['blur', 'change'] });
          // }
          // if (qt[j].type_question == 'email') {
          //   values.push({type: 'email', message: 'Insira um endereço de email válido', trigger: ['blur', 'change'] });
          // }
          if (qt[j].type_question == 'image') {
            values.push({ validator: validateImage, trigger: ['blur', 'change'] });
          }
          rules.value[qt[j].id] = values;
      }else {
        rules.value[qt[j].id] = [];
      }
  }
  rules.value['conditions'] = [{ validator: (rule, value, callback) => {
      if (!value) {
        return callback(new Error(`Deve ler e aceitar a política de privacidade`));
      }
      else {
        return callback();
      }
    }, trigger: ['blur']}];
}
const checkCondition = () => {
  for (var i in questions.value) {
    let question = questions.value[i];
    let final_value = false;
    if (question.conditions && question.conditions.length <= 0) final_value = true;
    for (var j in question.conditions) {
      if (question.conditions[j].rule == '==') {
        let value = form.value[question.conditions[j].question_id] == question.conditions[j].answer_id;
        if (!question.conditions[j].status) value = !value;
        conditions.value[question.id] = value;
      }
      if (question.conditions[j].rule == '!=') {
        let value = form.value[question.conditions[j].question_id] != question.conditions[j].answer_id;
        if (!question.conditions[j].status) value = !value;
        conditions.value[question.id] = value;
      }
      if (question.conditions[j].rule == '<=') {
        const parsedDate1 = new Date(form.value[question.conditions[j].question_id].replace(/-/g, '/'));
        const parsedDate2 = new Date(question.conditions[j].answer_id.replace(/-/g, '/'));
        let value = form.value[question.conditions[j].question_id] <= question.conditions[j].answer_id;
        if (parsedDate1 instanceof Date && !isNaN(parsedDate1) && parsedDate2 instanceof Date && !isNaN(parsedDate2)) {
          value = parsedDate1 <= parsedDate2;
        }
        if (!question.conditions[j].status) value = !value;
        if (question.conditions[j].concat_condition && question.conditions[j].concat_condition == '&&'){
          value = final_value == true && value == true ? true : false;
        } else {
          value = final_value == true ? true : value;
        }
        final_value = value;
      }
      if (question.conditions[j].rule == '>=') {
        const parsedDate1 = new Date(form.value[question.conditions[j].question_id].replace(/-/g, '/'));
        const parsedDate2 = new Date(question.conditions[j].answer_id.replace(/-/g, '/'));
        let value = form.value[question.conditions[j].question_id] <= question.conditions[j].answer_id;
        if (parsedDate1 instanceof Date && !isNaN(parsedDate1) && parsedDate2 instanceof Date && !isNaN(parsedDate2)) {
          value = parsedDate1 >= parsedDate2;
        }
        if (!question.conditions[j].status) value = !value;
        if (question.conditions[j].concat_condition && question.conditions[j].concat_condition == '&&'){
          value = final_value == true && value == true ? true : false;
        } else {
          value = final_value == true ? true : value;
        }
        final_value = value;
      }
    }
    conditions.value[question.id] = final_value;
    if (form.value[question.id] !== '' && form.value[question.id] !== 'null' && (form.value[question.id] !== 0)) conditions.value[question.id] = true;

    if ((form.value[question.id] === '' || form.value[question.id] === 'null' || (form.value[question.id] === 0)) && question.type_question !== 'textarea' ) conditions.value[question.id] = false;
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

const validateImage = (rule, value, callback) => {
  if (!value || value.length <= 0) {
    return callback(new Error('Campo obrigatório'));
  } else if ((value.cur_img || value.new_img)) {
    if ((value.cur_img.length <= 0 && value.new_img.length <= 0)) {
        return callback(new Error('Campo obrigatório'));
    } else {
        return callback();
    }
  } else {
    return callback();
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

const handleExceed = (files, fileList) => {
  ElMessage({
    message: 'Só pode carregar até três imagens',
    type: 'warning',
    duration: 5000
  });
}

const handleDeleteGallery = (index, question_id) => {
        form.value[question_id].cur_img.splice(index , 1);
}

const openPrivacyDialog = () => {
  privacyDialog.value = true;
}

const closePrivacyDialog = () => {
  privacyDialog.value = false;
}

</script>

<template>
    <el-card v-if="!submitted">
        <div class="logo-container">
            <img class="logoWidth" src="/uploads/viana-festas-logo.png">
        </div>
        <!-- <div v-if="!active">
          <not-available></not-available>
        </div> -->
        <!-- <div v-if="!canEdit">
            <p class="opensansName">Para editar a sua inscrição deve inserir os seguintes dados:</p>
            <el-form :model="editForm" ref="editFormRef" :rules="editRules" label-width="auto" :label-position="labelPosition">
                <el-form-item class="opensansName" label="Código da Inscrição" prop="code">
                    <el-input class="opensansName" v-model="editForm.code" placeholder="Código da inscrição" autofocus />
                </el-form-item>
                <el-form-item class="opensansName" label="Nº CC" prop="identification">
                    <el-input class="opensansName" v-model="editForm.identification" placeholder="Nº CC" autofocus />
                </el-form-item>
                <el-form-item class="opensansName" label="Email" prop="email">
                    <el-input class="opensansName" v-model="editForm.email" placeholder="email" autofocus />
                </el-form-item>
            </el-form>
            <div class="btnSelect">
                <el-button
                    type="primary"
                    size="large" 
                    :loading="loading"
                    @click="editFormSubmit"
                >
                    Confirmar
                </el-button>
            </div>
        </div> -->
        <!-- <div v-else> -->
        <div>
          <div v-if="progress < 100" class="progressCircle">
            <el-progress type="circle" :percentage="progress" color="#C797C5"></el-progress>
            <p class="opensansName">A carregar dados...</p>
          </div>
          <div v-else>
              <div v-if="!displayQRCode">
                <el-form :rules="rules" ref="formRef" class="formContainer" :model="form" label-width="auto" :label-position="labelPosition">
                  <div v-for="(question, idx) in questions" :key="idx" >
                    <el-form-item class="opensansName" v-if="conditions[question.id]" :label="question.description" :prop="question.id.toString() + (question.type_question === 'phone' && conditions[question.id] ? '.number' : '')">
                      <!-- Changed all inputsto display if questions is not empty/null  -->
                      <!-- Text -->
                        <el-input disabled class="opensansName" v-if="(question.type_question === 'text' || question.type_question === 'email' || question.type_question === 'rancho') && conditions[question.id]" v-model="form[question.id]" :placeholder="question.description" :required="question.mandatory" autofocus />
                        <!-- Phone number -->
                        <div class="displayPhoneNumber opensansName" v-if="question.type_question === 'phone' && conditions[question.id]">
                          <el-select disabled class="opensansName" v-model="form[question.id].prefix" filterable clearable placeholder="Selecionar">
                            <el-option
                                class="opensansName" 
                                v-for="code in codes"
                                :key="code.id"
                                :label="code.country  + ' +' + code.code"
                                :value="code.code"
                            />
                          </el-select>
                          <el-input
                            disabled
                            class="opensansName" 
                            v-model="form[question.id].number"
                          />
                        </div>
                        <!-- Number -->
                        <el-input disabled class="opensansName" v-model="form[question.id]" v-if="question.type_question === 'number' && conditions[question.id]" />
                        <!-- Date -->
                        <el-date-picker
                            disabled
                            class="opensansName" 
                            v-if="question.type_question === 'date' && conditions[question.id]"
                            v-model="form[question.id]"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            type="date"
                            placeholder="Selecione um dia"
                        />
                        <!-- Country -->
                        <el-select disabled :class="(question.isTraje ? 'selectWidth' : '') + ' opensansName'" v-if="(question.type_question === 'country' || question.type_question === 'district' || question.type_question === 'concelho' || question.type_question === 'freguesia' || question.type_question === 'select')  && conditions[question.id]" v-model.number="form[question.id]" :filterable="checkFilterable(question)" clearable placeholder="Selecionar">
                          <el-option
                              class="opensansName" 
                              v-for="answer in question.answers"
                              :key="answer.id"
                              :label="answer.description"
                              :value="answer.id"
                          />
                        </el-select>
                        <!-- Checkbox -->
                        <el-checkbox disabled class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && question.multiple" v-model="form[question.id]" label="Option 1" />
                        <el-radio-group disabled class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && !question.multiple" v-model="form[question.id]">
                          <el-radio
                            class="opensansName" 
                            v-for="answer in question.answers"
                            :key="answer.id"
                            :label="answer.id">{{ answer.description }}</el-radio>
                        </el-radio-group>
                        <!-- Image -->
                        
                        <el-upload
                          v-if="question.type_question === 'image'"
                          style="width: 100%"
                          v-model:file-list="form[question.id].new_img"
                          :class="uploadClass"
                          action=""
                          :auto-upload="false"
                          list-type="picture"
                          :limit="3"
                          accept="image/*"
                          multiple
                          @exceed="handleExceed"
                        >
                          <!-- <el-button :disabled="form[question.id].cur_img.length >= 3 || form[question.id].new_img.length >= 3 || (form[question.id].cur_img.length + form[question.id].new_img.length) >= 3" type="primary">Carregar Fotografia(s)</el-button> -->
                          <el-button :disabled="form[question.id].new_img.length >= 3" type="primary">Carregar Fotografia(s)</el-button>
                        </el-upload>
                        <div class="opensansName" v-if="question.type_question === 'image' && form[question.id].cur_img" style="width: 100%">
                            <p v-if="form[question.id].cur_img.length > 0">Imagens carregadas</p>
                            <el-row v-if="form[question.id].cur_img.length > 0" class="image-row">
                                <el-col v-for="(img, idx_img) in form[question.id].cur_img" :key="idx_img" :span="8" class="image-col">
                                    <div class="image-container">
                                        <!-- <el-button type="danger" round :icon="Delete" class="delete-image" circle size="small" @click="handleDeleteGallery(idx_img, question.id)" /> -->
                                        <el-image
                                            style="width: 100px; height: 100px"
                                            :src="img"
                                            :zoom-rate="1.2"
                                            :max-scale="7"
                                            :min-scale="0.2"
                                            :preview-src-list="form[question.id].cur_img"
                                            :initial-index="idx_img"
                                            fit="cover"
                                        />
                                    </div>
                                </el-col>
                            </el-row>
                            <!-- <el-image
                                v-for="(img, idx_img) in form[question.id].cur_img"
                                :key="idx_img"
                                style="width: 100px; height: 100px"
                                :src="img"
                                :zoom-rate="1.2"
                                :max-scale="7"
                                :min-scale="0.2"
                                :preview-src-list="form[question.id].cur_img"
                                :initial-index="idx_img"
                                fit="cover"
                            /> -->
                        </div>
                        <!-- Textarea -->
                        <el-input
                            class="opensansName"
                            v-if="question.type_question === 'textarea'"
                            v-model="form[question.id]"
                            :autosize="{ minRows: 2, maxRows: 4 }"
                            type="textarea"
                            :placeholder="question.description"
                        />
                    </el-form-item>
                  </div>
                  <div>
                    <el-form-item class="opensansName termsConditions" label="" prop="conditions">
                      <el-checkbox class="opensansName" v-model="form['conditions']">
                        <div class="termsConditionsClass">
                          <span class="opensansName">Dou o meu consentimento e autorizo a recolha e tratamento dos meus dados pessoais, tendo por finalidade o objetivo deste formulário assim como declaro que li e aceito <el-link class="primaryOrange" :underline="false" @click="openPrivacyDialog">Política de privacidade</el-link></span>
                        </div>
                      </el-checkbox>
                    </el-form-item>
                  </div>
              </el-form>
              <div class="btnSelect">
                  <el-button
                      type="primary"
                      size="large" 
                      :loading="loading"
                      @click="submit"
                  >
                      Enviar Inscrição
                  </el-button>
              </div>
            </div>
          </div>
        </div>
    </el-card>
    <thanks v-else></thanks>
    <el-dialog
        v-model="privacyDialog"
        title="Política de Privacidade"
        :width="dialogWidth"
        :before-close="closePrivacyDialog"
      >
        <span class="opensansName spanPrivacy">Os dados pessoais que forem recolhidos no âmbito da plataforma, serão tratados com respeito pela legislação de proteção dos dados pessoais, nomeadamente a Lei n.º 67/98, de 26 de Outubro, e a Lei n.º 41/2004, de 18 de Agosto, bem como a partir de 25 de Maio de 2018, o RGPD - Regulamento Geral de Proteção de Dados (EU 2016\\679), sendo que a plataforma em causa pressupõe o conhecimento e aceitação das seguintes condições:<ol type="a"><li>Os membros da plataforma aceitam que o fornecimento dos dados é necessário e obrigatório para efeitos de processamento das inscrições no Desfile de Mordomia, apuramento dos participantes e realização do Desfile da Mordomia. Os dados serão recolhidos e tratados pela entidade promotora, VIANAFESTAS;</li><li> A VIANAFESTAS, enquanto promotora do evento “Romaria de Nossa Senhora d'Agonia”, através da respetiva Comissão de Festas, garante a segurança e confidencialidade do tratamento, garantindo a possibilidade de acesso, retificação e cancelamento dos dados aos participantes que assim o desejem e o comuniquem, através do correio eletrónico, para <a class="primaryOrange opensansName" href="mailto:vianafestas@vianafestas.com">vianafestas@vianafestas.com</a>;</li><li> Os dados de identificação pessoal obtidos poderão ser disponibilizados para o apuramento de responsabilidade civil e criminal, mediante solicitação da autoridade judiciária competente, nos termos da legislação aplicável. </li></ol></span>
        <template #footer>
          <div class="btnSelect">
            <el-button @click="closePrivacyDialog">Fechar</el-button>
          </div>
        </template>
      </el-dialog>
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
    .logo-container {
      text-align: center;
    }
  }
  
  @media only screen and (min-width: 768px) {
    .el-card {
      width: 100%;
      max-width: 50rem;
      padding: 15px 25px;
    }
  }
  
  .forgot-password-link {
      font-size: 12px;
      margin-top: 10px;
  }
  
  .logo-container {
    background: white; 
    text-align: left;
    width: 100%;
    margin: -20px 0 20px;
  }

  .formContainer {
    margin-top: 40px !important;
  }

  .avatar-uploader .avatar {
  width: 178px;
  height: 178px;
  display: block;
}
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

.displayPhoneNumber {
  display: flex !important;
  width: 100% !important;
}

.termsConditions>div>label {
  font-family: 'openSans', sans-serif !important;
  min-height: 60px !important;
}
@media only screen and (max-width: 800px) {
  .termsConditions>div>label {
    min-height: 125px !important;
  }
}

.spanPrivacy {
  line-height: normal !important;
}
</style>

<style>
.termsConditions>.el-form-item__content {
  margin-left: 0px !important;
}
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

.progressCircle {
  text-align: center !important;
}

.desktopUpload ul.el-upload-list {
  display: flex !important;
  line-height: 20px !important;
}

.desktopUpload ul.el-upload-list>li {
  display: block !important;
  text-align: center;
  padding: 20px 10px !important;
  max-width: 50% !important;
}

.desktopUpload ul.el-upload-list>li>img {
  width: 100% !important;
  height: 80% !important;
}

.mobileUpload {
  width: 100% !important;
  min-width: 100% !important;
}

.el-autocomplete-suggestion__list {
  font-family: 'openSans', sans-serif !important;
}
.el-select-dropdown__empty > span {
  font-family: 'openSans', sans-serif !important;
}
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

.el-message-box__title>span {
  font-family: 'openSans', sans-serif !important;
}

.el-message-box__message {
  font-family: 'openSans', sans-serif !important;
}
.selectWidth {
  width: 100% !important;
}
.startMain-container{
  min-height: 100% !important;
}
.startMobile-container{
  min-height: 100% !important;
}
</style>
