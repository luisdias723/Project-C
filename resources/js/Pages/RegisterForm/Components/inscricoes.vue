<script setup>
import { ref, onMounted, computed, watch } from 'vue';
const props = defineProps(['checklist', 'mobile', 'register', 'active']);

import { ElMessage } from 'element-plus';

import FormResource  from '@/api/formulario';

import Thanks from './tanks.vue';

import notAvailable from './notAvailable.vue';

const formResource = new FormResource();
const countryResource = new FormResource();
const answersResource = new FormResource();
const emit = defineEmits(['goBack']);

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
const conditionsFormRef = ref(null);

// Variable to pass to qrcodecomponent
let displayQRCode = ref(false);
let message = ref("Obrigado por se registar no desfile de mordomia da romaria da Sra d'Agonia. Irá necessitar deste Código QR para realizar a participação.");

// Variable to store the data of steps
// let steps = ['Dados Pessoais', 'Dados Participação']
let steps = [];

let progress = ref(0);

// Variable to store the data from form
let form = ref({});

let formConditions = ref({'conditions': false});

// Variable to store tehe rules
let rules = ref({});

// Variable to display condições de participação
let displayPdf = ref(true);

let ConditionsRules = ref({
  'conditions': [{ validator: (rule, value, callback) => {
      if (!value) {
        return callback(new Error(`Deve ler e aceitar as condições de participação`));
      }
      else {
        return callback();
      }
    }, trigger: ['blur']}]
});

let selectedCountry = ref([]);

let submitted = ref(false);

let loadingCondition = ref(false);

const termsConditions = '<span class="opensansName">Dou o meu consentimento e autorizo a recolha e tratamento dos meus dados pessoais, tendo por finalidade o objetivo deste formulário e li e aceito tanto a <el-link :underline="false" href="" download="Politica-privacidade.pdf">Política de privacidade</el-link> como a política de participação. ATUALMENTE EM FALTA ESTAS POLÍTICAS!</span>';

const disabledDate = (time) => {
    const start = new Date(1924, 6, 31).getTime(); // 30 de junho de 1924
    const end = new Date(2010, 11, 31).getTime(); // 31 de dezembro de 2010
    return time.getTime() < start || time.getTime() > end;
  };

// ----------------------------------   COMPUTED    ----------------------------------

// ----------------------------------   WATCH    ----------------------------------
watch(form, (newValue, oldValue) => {
    checkCondition();
    generateRules();
    // sua lógica para lidar com as alterações no form
}, { deep: true });

let questions = ref ({});
let loading = ref(false);

let codes = ref({});

let conditions = ref({});

onMounted(() => {
    // getList();
})

function getList() {
    progress.value += 5;
    loading.value = true;
    const id = props.checklist;
    if (!id) {
      ElMessage({
        message: 'Não é possível realizar a inscrição',
        type: 'error',
        duration: 5000
      });
      goBack();
    }
    formResource.getForm(id).then(data => {
      questions.value = data;
      for (var j in questions.value) {
        form.value[questions.value[j].id] = null;
        if (questions.value[j].type_question == 'image') form.value[questions.value[j].id] = [];
        if (questions.value[j].type_question == 'checkbox' && questions.value[j].multiple) form.value[questions.value[j].id] = false;
      }
      progress.value += 20;
      incrementProgress();
      getCodes();
    }).catch(error => {
        console.log(error);
        loading.value = false;
    });
}

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

// Methods/Functions
const goBack = () => {
    emit('goBack');
}

// Submit method and get the qrcode
const submit = async () => {
  loading.value = true;
  if (!formRef) {
    ElMessage({
      message: 'Não foi possível registar a sua inscrição. Tente novamente.',
      type: 'error',
      duration: 5000
    });
    loading.value = false;
    return;
  }
  await formRef.value.validate((valid, fields) => {
    if (valid) {
      let formData = new FormData();
      formData.append('checklist', props.checklist)
      for (let j in questions.value) {
        let question = questions.value[j];
        
        if (question.type_question === 'image') {
          let files = form.value[question.id];
          for (let i in files) {
            formData.append(`files[${question.id}][]`, files[i].raw);
          }
        } else {
          let value = form.value[question.id];
          if (selectedCountry.value[question.id]) {
            let codeNumb = codes.value[selectedCountry.value[question.id]] ? codes.value[selectedCountry.value[question.id]].code : '';
            value = '+' + codeNumb + ' ' + value;
          }
          formData.append(`answer[${question.id}]`, value);
        }
      }
      answersResource.storeAnswers(formData).then(data => {
        if (data) {
          ElMessage({
            message: 'A sua inscrição foi efetuada com sucesso',
            type: 'success',
            duration: 5000
          })
          loading.value = false;
          submitted.value = true;
        } else {
          ElMessage({
            message: 'Não foi possível guardar a sua inscrição',
            type: 'error',
            duration: 5000
          })
          loading.value = false;
        }
      }).catch(error => {
        if (error && error.request && error.request.status && error.request.status == '403') {
        } else {
          ElMessage({
            message: 'Não foi possível guardar a sua inscrição. Tente novamente',
            type: 'error',
            duration: 5000
          });
        }
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
}

// Submit conditions to keep going on registration
const submitConditions = async () => {
  loadingCondition.value = true;
  if (!conditionsFormRef) {
    ElMessage({
      message: 'Não foi possível avançar com a sua inscrição. Tente novamente.',
      type: 'error',
      duration: 5000
    });
    loadingCondition.value = false;
    return;
  }
  await conditionsFormRef.value.validate((valid, fields) => {
    if (valid) {
      displayPdf.value = false;
      loadingCondition.value = false;
      getList();
    } else {
      ElMessage({
        message: 'Deve ler as condições de participação e indicar que as leu devidamente.',
        type: 'error',
        duration: 5000
      });
      loadingCondition.value = false;
    }
  })
}

const generateRules = () => {
  let qt = questions.value;
  for (var j in qt) {
      if (qt[j].mandatory && conditions.value[qt[j].id]) {
          var values = [];
          values.push({
              required: true,
              message: 'Campo Obrigatório',
              trigger: ['change', 'blur']
          });
          if (qt[j].type_question == 'number') {
            if (qt[j].description == 'Nº CC' || qt[j].description == 'Nº CC Responsável') {
              values.push({ validator: validateNumberCC, trigger: ['blur', 'change'] });
            } else {
              values.push({ validator: validateNumber, trigger: ['blur', 'change'] });
              if (qt[j].num_digits > 0) {
                const numDigits = parseInt(qt[j].num_digits, 10);
                values.push({ validator: (rule, value, callback) => {
                  if (value.length > numDigits) {
                    return callback(new Error(`Este campo não pode conter mais que ${numDigits} caracteres`));
                  }
                  else {
                    return callback();
                  }
                }, trigger: ['blur', 'change'] });
              }
            }
          }
          if (qt[j].type_question == 'phone') {
            values.push({ validator: validatePhone, trigger: ['blur', 'change'] });
          }
          if (qt[j].type_question == 'email') {
            values.push({type: 'email', message: 'Insira um endereço de email válido', trigger: ['blur', 'change'] });
          }
          if (qt[j].type_question == 'image') {
            values.push({ validator: validateImage, trigger: ['blur', 'change'] });
          }
          if (qt[j].type_question == 'date') {
            values.push({ validator: validateDateRange, trigger: ['blur', 'change'] });
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
    conditions.value[question.id] = true;
    let final_value = false;
    if (question.conditions.length <= 0) final_value = true;
    for (var j in question.conditions) {
      if (question.conditions[j].rule == '==') {
        let value = form.value[question.conditions[j].question_id] == question.conditions[j].answer_id;
        if (!question.conditions[j].status) value = !value;
        final_value = value;
        if (conditions.value[question.id] && final_value == false) {
          form.value[question.id] = null;
        }
      }
      if (question.conditions[j].rule == '!=') {
        let value = form.value[question.conditions[j].question_id] != question.conditions[j].answer_id;
        if (!question.conditions[j].status) value = !value;
        final_value = value;
      }
      if (question.conditions[j].rule == '<=') {
        let value = form.value[question.conditions[j].question_id] <= question.conditions[j].answer_id;
        if (!question.conditions[j].status) value = !value;
        if (question.conditions[j].concat_condition && question.conditions[j].concat_condition == '&&'){
          value = final_value == true && value == true ? true : false;
        } else {
          value = final_value == true ? true : value;
        }
        final_value = value;
      }
      if (question.conditions[j].rule == '>=') {
        let value = form.value[question.conditions[j].question_id] >= question.conditions[j].answer_id;
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

const validateImage = (rule, value, callback) => {
  if (!value || value.length <= 0) {
    return callback(new Error('Campo obrigatório'));
  } else {
    return callback();
  }
}

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


const handleRemove = (uploadFile, uploadFiles) => {
}

const handlePreview = (file) => {
}

const handleExceed = (files, fileList) => {
  ElMessage({
    message: 'Só pode carregar até três imagens',
    type: 'warning',
    duration: 5000
  });
}

const openPrivacyDialog = () => {
  privacyDialog.value = true;
}

const closePrivacyDialog = () => {
  privacyDialog.value = false;
}

// Função para criar o filtro
const createFilter = (queryString) => {
  return (item) => {
    return item.value.toLowerCase().includes(queryString.toLowerCase());
  };
};

// Função para buscar sugestões
const querySearch = (queryString, cb, data) => {
  const results = queryString
    ? data.filter(createFilter(queryString))
    : data;
  // Chamar função de callback para retornar as sugestões
  cb(results);
};
</script>

<template>
    <el-card v-if="!submitted">
      <div class="logo-container">
          <img class="logoWidth" src="/uploads/viana-festas-logo.png">
      </div>
      <div v-if="!active">
        <not-available :cortejo="false"></not-available>
        <div class="btnSelect">
          <el-button
              type=""
              size="large" 
              :loading="loadingCondition"
              @click="goBack"
          >
              Voltar
          </el-button>
        </div>
      </div>
      <div v-else>

          <div v-if="displayPdf">
            <div class="pdf-container" v-if="!mobile">
              <embed src="REG_DESFILE_MORDOMIA.pdf" type="application/pdf" width="100%" height="600px" />
              <!-- <object data="REG_DESFILE_MORDOMIA.pdf" type="application/pdf" width="100%" height="600px"> -->
                <p>Não consegue visualizar o documento? Pré-visualize o documento <a class="primaryOrange" href="REG_DESFILE_MORDOMIA.pdf" target="_blank">aqui</a></p>
                <p>Ou faça download <el-link class="primaryOrange" :underline="false" href="REG_DESFILE_MORDOMIA.pdf" download="Condicoes-Participacao-Desfile.pdf">aqui</el-link></p>
              <!-- </object> -->
            </div>
            <div v-else>
              <p>Pré-visualize o documento <a class="primaryOrange" href="REG_DESFILE_MORDOMIA.pdf" target="_blank">aqui</a></p>
              <p>Ou faça download <el-link class="primaryOrange" :underline="false" href="REG_DESFILE_MORDOMIA.pdf" download="Condicoes-Participacao-Desfile.pdf">aqui</el-link></p>
                
            </div>
            <el-form :rules="ConditionsRules" ref="conditionsFormRef" class="formContainer" :model="formConditions" label-width="auto" :label-position="labelPosition">
              <el-form-item class="opensansName termsConditions PClass" label="" prop="conditions">
                <el-checkbox v-model="formConditions['conditions']">
                  <div class="termsConditionsClass">
                    <span class="opensansName">Declaro que li e aceito as condições de participação mencionadas no documento acima disponibilizado.</span>
                  </div>
                </el-checkbox>
              </el-form-item>
            </el-form>
            <div class="btnSelect">
              <el-button
                  type=""
                  size="large" 
                  :loading="loadingCondition"
                  @click="goBack"
              >
                  Voltar
              </el-button>
              <el-button
                  type="primary"
                  size="large" 
                  :loading="loadingCondition"
                  @click="submitConditions"
              >
                  Continuar
              </el-button>
            </div>
          </div>
          <div v-else>
            <div v-if="progress < 100" class="progressCircle">
              <el-progress type="circle" :percentage="progress" color="#C797C5"></el-progress>
              <p class="opensansName">A carregar dados...</p>
            </div>
            <div v-if="progress >= 100 && register">
                <!-- <FormComponent v-if="!displayQRCode" :steps="steps" :questions="questions" :conditions="conditions" @submit="submit" @goBack="goBack"></FormComponent>
                <QRCodeComponent v-else :message="message"></QRCodeComponent> -->
                <div v-if="!displayQRCode">
                  <el-form :rules="rules" ref="formRef" class="formContainer" :model="form" label-width="auto" :label-position="labelPosition">
                    <div v-for="(question, idx) in questions" :key="idx">
                      <el-form-item v-if="conditions[question.id]" :label="question.description" :prop="question.id.toString()" class="opensansName">
                          <!-- Text -->
                          <el-input class="opensansName" v-if="(question.type_question === 'text' || question.type_question === 'email') && conditions[question.id]" v-model="form[question.id]" :placeholder="question.description" :required="question.mandatory" autofocus />
                          <!-- Phone number -->
                          <div class="displayPhoneNumber" v-if="question.type_question === 'phone' && conditions[question.id]">
                            <el-select class="opensansName" v-model="selectedCountry[question.id]" filterable clearable placeholder="Selecionar">
                              <el-option
                                  v-for="(code, idx_code) in codes"
                                  class="opensansName"
                                  :key="code.id"
                                  :label="code.country + ' +' + code.code"
                                  :value="idx_code"
                              />
                            </el-select>
                            <el-input
                              class="opensansName"
                              v-model="form[question.id]"
                            />
                          </div>
                          <!-- Number -->
                          <el-input class="opensansName" v-model="form[question.id]" v-if="question.type_question === 'number' && conditions[question.id]" />
                          <!-- Date -->
                          <el-date-picker
                              v-if="question.type_question === 'date' && conditions[question.id]"
                              class="opensansName"
                              v-model="form[question.id]"
                              format="YYYY-MM-DD"
                              value-format="YYYY-MM-DD"
                              type="date"
                              placeholder="Selecione um dia"
                              :disabled-date="disabledDate"
                          />
                          <!-- Country -->
                          <el-select style="width:75%" class="opensansName" v-if="(question.type_question === 'country' || question.type_question === 'district' || question.type_question === 'concelho' || question.type_question === 'freguesia' || question.type_question === 'select')  && conditions[question.id]" v-model="form[question.id]" :filterable="checkFilterable(question)" clearable placeholder="Selecionar">
                            <el-option
                                v-for="answer in question.answers"
                                class="opensansName"
                                :key="answer.id"
                                :label="answer.description"
                                :value="answer.id"
                            />
                          </el-select>
                          <!-- Checkbox -->
                          <el-checkbox class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && question.multiple" v-model="form[question.id]" label="Option 1" />
                          <el-radio-group class="opensansName" v-if="(question.type_question === 'checkbox')  && conditions[question.id] && !question.multiple" v-model="form[question.id]">
                            <el-radio
                              v-for="answer in question.answers"
                              class="opensansName"
                              :key="answer.id"
                              :label="answer.id">{{ answer.description }}</el-radio>
                          </el-radio-group>
                          <!-- Fazer o autocomplete para o nome dos ranchos -->
                          <el-autocomplete
                            v-if="question.type_question === 'rancho'"
                            v-model="form[question.id]"
                            :fetch-suggestions="(queryString, cb) => querySearch(queryString, cb, question.answers)"
                            :trigger-on-focus="false"
                            clearable
                            class="opensansName"
                            :placeholder="question.description"
                          />
                          <!-- Image -->
                          <el-upload
                            v-if="question.type_question === 'image'"
                            v-model:file-list="form[question.id]"
                            :class="uploadClass"
                            action=""
                            :auto-upload="false"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            list-type="picture"
                            :limit="3"
                            accept="image/*"
                            multiple
                            @exceed="handleExceed"
                          >
                            <el-button class="opensansName" :disabled="form[question.id].length >= 3" type="primary">Carregar Fotografia(s)</el-button>
                          </el-upload>
                          <!-- Textarea -->
                          <el-input
                              v-if="question.type_question === 'textarea'"
                              class="opensansName"
                              v-model="form[question.id]"
                              :autosize="{ minRows: 2, maxRows: 4 }"
                              type="textarea"
                              :placeholder="question.description"
                          />
                      </el-form-item>
                    </div>
                    <div>
                      <el-form-item class="opensansName termsConditions" label="" prop="conditions">
                        <el-checkbox v-model="form['conditions']">
                          <div class="termsConditionsClass">
                            <span class="opensansName">Dou o meu consentimento e autorizo a recolha e tratamento dos meus dados pessoais, tendo por finalidade o objetivo deste formulário assim como declaro que li e aceito <el-link class="primaryOrange" :underline="false" @click="openPrivacyDialog">Política de privacidade</el-link></span>
                          </div>
                        </el-checkbox>
                      </el-form-item>
                    </div>
                  </el-form>
                  <div class="btnSelect">
                    <el-button
                        type=""
                        size="large" 
                        :loading="loading"
                        @click="goBack"
                    >
                        Voltar
                    </el-button>
                    <el-button
                        type="primary"
                        size="large" 
                        :loading="loading"
                        @click="submit"
                    >
                        Inscrever
                    </el-button>
                  </div>
                </div>
              </div>
            <div v-if="progress >= 100 && !register">
              <h2 class="opensansName">De momento as inscrições para este evento encontram-se encerradas.</h2>
                  <p class="opensansName">De acordo com Artigo 1.3 do Capítulo 3 das Condições de Participação, após serem recebidas um total de 900 inscrições, a organização dá por encerradas as inscrições para o Desfile da Mordomia.</p>
                  <h2 class="opensansName">Obrigado pela compreensão. Boa Romaria.</h2>
            </div>
          </div>
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
      </div>
    </el-card>
    <thanks v-else></thanks>
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
    text-align: left;
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
.progressCircle {
  text-align: center !important;
}
.opensansName>label {
  font-family: 'openSans', sans-serif !important;
}
.termsConditions>div>label {
  font-family: 'openSans', sans-serif !important;
  min-height: 60px !important;
}
@media only screen and (max-width: 800px) {
  .termsConditions.PClass>div>label {
    min-height: 70px !important;
  }
  
  .termsConditions>div>label {
    min-height: 125px !important;
  }
}

.spanPrivacy {
  line-height: normal !important;
}
</style>

<style>
.desktopUpload ul.el-upload-list {
  display: flex !important;
  line-height: 20px !important;
}

.desktopUpload ul.el-upload-list>li {
  display: block !important;
  text-align: center;
  padding: 20px 10px !important;
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

.termsConditions>.el-form-item__content {
  margin-left: 0px !important;
}
.pdf-container {
  width: 100%;
  height: 100%;
}
.startMain-container{
  min-height: 100% !important;
}
.startMobile-container{
  min-height: 100% !important;
}
</style>
