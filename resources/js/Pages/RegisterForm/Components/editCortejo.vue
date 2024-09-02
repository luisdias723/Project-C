<script setup>
    import {
        ref,
        onMounted,
        computed,
        watch
    } from 'vue';
    const props = defineProps(['query', 'checklist', 'mobile', 'active']);

    import { ElMessage, ElMessageBox } from 'element-plus';

    import { Plus, Minus, Delete } from '@element-plus/icons-vue';

    import FormResource from '@/api/formulario';

    import Thanks from './tanks.vue';

    import notAvailable from './notAvailable.vue';

    const formResource = new FormResource();
    const countryResource = new FormResource();
    const answersResource = new FormResource();
    const emit = defineEmits(['goBack']);

    let existsMaior = ref({});
    let menorIdade = ref({});

    const labelPosition = computed(() => {
        return props.mobile ? 'top' : 'left';
    });

    const dialogWidth = computed(() => {
        return props.mobile ? '100%' : '50%';
    });

    const uploadClass = computed(() => {
        return props.mobile ? 'mobileUpload opensansName upload-demo' :
        'desktopUpload opensansName upload-demo';
    });

    const checkHidden = computed(() => {
        if (btnSelected.value === 1) {
            return true;
        }
        if (btnSelected.value === 2) {
            if (!formGroup.value[btnSelected.value]) return false
            return formGroup.value[btnSelected.value].length > 1 ? true : false;
        }
        if (btnSelected.value === 3) {
            if (!formGroup.value[btnSelected.value]) return false
            return formGroup.value[btnSelected.value].length > 8 ? true : false;
        }
    });

    let privacyDialog = ref(false);

    // const formRef = ref<FormInstance>();
    const formRef = ref(null);
    const conditionsFormRef = ref(null);
    const participanteRef = ref(null);

    // Variable to pass to qrcodecomponent
    let displayQRCode = ref(false);
    let message = ref(
        "Obrigado por se registar no desfile de mordomia da romaria da Sra d'Agonia. Irá necessitar deste Código QR para realizar a participação."
        );

    // Variable to store the data of steps
    // let steps = ['Dados Pessoais', 'Dados Participação']
    let steps = [];

    let progress = ref(0);

    let countryPhone = ref({});

    // Variable to store the data from form
    let form = ref({});

    let formConditions = ref({
        'conditions': false
    });

    // Variable to store tehe rules
    let rules = ref({});
    let rulesForm = ref({});

    // Variable to display condições de participação
    // TODO: MUDAR AQUI!!
    let displayPdf = ref(false);

    let ConditionsRules = ref({
        'conditions': [{
            validator: (rule, value, callback) => {
                if (!value) {
                    return callback(new Error(`Deve ler e aceitar as condições de participação`));
                } else {
                    return callback();
                }
            },
            trigger: ['blur']
        }]
    });

    let selectedCountry = ref([]);

    let submitted = ref(false);

    let loadingCondition = ref(false);

    const termsConditions =
        '<span class="opensansName">Dou o meu consentimento e autorizo a recolha e tratamento dos meus dados pessoais, tendo por finalidade o objetivo deste formulário e li e aceito tanto a <el-link :underline="false" href="" download="Politica-privacidade.pdf">Política de privacidade</el-link> como a política de participação. ATUALMENTE EM FALTA ESTAS POLÍTICAS!</span>';

    // Variable to add buttons (Individual/Par/Grupo)
    let btnToSelect = ref([
        {
            id: 1,
            'description': 'Individual',
            'active': 0
        },
        {
            id: 2,
            'description': 'Par',
            'active': 0
        },
        {
            id: 3,
            'description': 'Grupo',
            'active': 0
        }
    ]);

    let btnSelected = ref(null);

    let questionsForm = ref([]);

    let question_quadro = ref(null);

    let question_trajes = ref(null);

    let questionTrajes = ref({});

    let currentStep = ref(0);

    let formGroup = ref([]);

    let formGroupData = ref({});
    

    // ----------------------------------   COMPUTED    ----------------------------------

    // ----------------------------------   WATCH    ----------------------------------

    watch(formGroupData, (newValue, oldValue) => {
        checkConditionForm();
        generateRulesForm();
        // sua lógica para lidar com as alterações no form
    }, {
        deep: true
    });

    let existsMenor = ref(false);
    let isMaiorIdade = ref(false);
    let isMenorIdade = ref(false);

    // watch(existsMaior, (newValue, oldValue) => {
    //     let menor = false;
    //     let maior = false;
    //     for (var j in newValue) {
    //         if (!newValue[j]) {
    //             menor = true;
    //         } else {
    //             maior = true;
    //         }
    //     }
    //     existsMenor.value = maior && menor;
    //     // sua lógica para lidar com as alterações no form
    // }, {
    //     deep: true
    // });

    let questions = ref({});
    let loading = ref(false);

    let codes = ref({});

    let conditions = ref({});

    let registration = ref(null);

    onMounted(() => {
        // TODO: MUDAR AQUI!
        getList();
    })

    function getList() {
        progress.value += 5;
        loading.value = true;
        let id = props.query;
        if (!id) {
            ElMessage({
                message: 'Não é possível atualizar a inscrição',
                type: 'error',
                duration: 5000
            });
            goBack();
            return;
        }
        let editForm = {};
        editForm.id = props.query;
        editForm.checklist = props.checklist;
        formResource.getFormCortejo(editForm).then(data => {
            if (data && data.questions && data.answers) {
                questionsForm.value = data.participant_questions;
                questions.value = data.questions;
                form.value = {};
                form.value = data.answers;
                registration.value = data.registration;
                formGroup.value = data.props;
                formGroupData.value = data.participant_answer;
                btnSelected.value = data.btnSelected || 1;
                for (var j in questions.value) {
                    if (questions.value[j].isSpecial){
                        question_quadro.value = questions.value[j].id;
                        selectedValueBoard.value = form.value[question_quadro.value];
                    }
                    if (questions.value[j].isSpecialBoards) question_trajes.value = questions.value[j].id;
                }
                // Forçar o preenchimento dos dados para ver questão do maior e menor de idade. Sabemos que é o id 2
                populateMaiorMenorIdade();
                loading.value = false;
                progress.value += 20;
                incrementProgress();
                getCodes();
                checkCondition();
                generateRules();
                checkConditionForm();
                generateRulesForm();
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
            loading.value = false;
            goBack();
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
        if (btnSelected.value == 3 && !isMaiorIdade.value) {
            displayMessage('Deverá existir uma pessoa com idade superior a 18 anos na inscrição de grupo.', 'error');
            return;
        }
        if ((btnSelected.value == 2 && formGroup.value[btnSelected.value].length <= 0) || (btnSelected.value == 3 && formGroup.value[btnSelected.value].length < 2)) {
            var part = 0;
            if (btnSelected.value == 2) part = 2;
            if (btnSelected.value == 3) part = 3;
            ElMessage({
                type: 'error',
                duration: 5000
            });
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
            loading.value = true;
            if ((btnSelected.value == 2 || btnSelected.value == 3) && formGroup.value[btnSelected.value]) {
                if (!participanteRef) {
                    ElMessage({
                        message: 'Não foi possível submeter a inscrição. Tente novamente.',
                        type: 'error',
                        duration: 5000
                    });
                    loading.value = false;
                    return;
                }
                await participanteRef.value.validate((valid, fields) => {
                    if (valid) {
                        let formData = new FormData();
                        formData.append('checklist', props.checklist);
                        formData.append('registration', registration.value);
                        // formData['answer'] = {};
                        // formData['participants'] = {};
                        for (let j in questions.value) {
                            let question = questions.value[j];

                            let value = form.value[question.id];
                            
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
                            }  else {
                                let value = form.value[question.id];
                                if (form.value[question.id] && form.value[question.id].prefix) {
                                    value = '+' + form.value[question.id].prefix + ' ' + form.value[question.id].number;
                                }
                                formData.append(`answer[${question.id}]`, value);
                            }

                            // formData['answer'][question.id] = value;
                        }

                        // for (var k in formGroup.value[btnSelected.value]) {
                        //     formData['participants'][k] = {};
                        //     for (var m in questionsForm.value) {
                        //         let value = formGroupData.value[k.toString() + questionsForm.value[m].id.toString()];
                        //         if (countryPhone.value[k][questionsForm.value[m].id]) {
                        //             let codeNumb = countryPhone.value[k][questionsForm.value[m].id];
                        //             value = '+' + codeNumb + ' ' + value;
                        //         }
                        //         formData['participants'][k][questionsForm.value[m].id] = value;
                        //     }
                        // }

                        answersResource.editCortejoAnswers(formData).then(data => {
                            if (data) {
                                displayMessage('A sua inscrição foi efetuada com sucesso', 'success');
                                loading.value = false;
                                submitted.value = true;
                            } else {
                                displayMessage('Não foi possível guardar a sua inscrição', 'error');
                                loading.value = false;
                            }
                        }).catch(error => {
                            if (error && error.request && error.request.status && error.request
                                .status == '403') {} else {
                                    displayMessage('Não foi possível guardar a sua inscrição. Tente novamente', 'error');
                            }
                            loading.value = false;
                        });
                    }  else {
                        displayMessage('Devem ser preenchidos todos os campos obrigatórios.', 'error');
                        loading.value = false;
                    }
                });
            }
        } catch (error) {
            console.log(error);
            loading.value = false;
            displayMessage('Operação cancelada', 'info');
        }

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
                        values.push({
                            validator: validateNumberCC,
                            trigger: ['blur', 'change']
                        });
                    } else {
                        values.push({
                            validator: validateNumber,
                            trigger: ['blur', 'change']
                        });
                        if (qt[j].num_digits > 0) {
                            const numDigits = parseInt(qt[j].num_digits, 10);
                            values.push({
                                validator: (rule, value, callback) => {
                                    if (value.length > numDigits) {
                                        return callback(new Error(
                                            `Este campo não pode conter mais que ${numDigits} caracteres`
                                            ));
                                    } else {
                                        return callback();
                                    }
                                },
                                trigger: ['blur', 'change']
                            });
                        }
                    }
                }
                if (qt[j].type_question == 'phone') {
                    values.push({
                        validator: validatePhone,
                        trigger: ['blur', 'change']
                    });
                }
                if (qt[j].type_question == 'email') {
                    values.push({
                        type: 'email',
                        message: 'Insira um endereço de email válido',
                        trigger: ['blur', 'change']
                    });
                }
                if (qt[j].type_question == 'image') {
                    values.push({
                        validator: validateImage,
                        trigger: ['blur', 'change']
                    });
                }
                if (qt[j].type_question == 'date') {
                    values.push({
                        validator: validateDateRange,
                        trigger: ['blur', 'change']
                    });
                }
                rules.value[qt[j].id] = values;
            } else {
                rules.value[qt[j].id] = [];
            }
        }
    }
    const checkCondition = () => {
        for (var i in questions.value) {
            let question = questions.value[i];
            conditions.value[question.id] = true;
            let final_value = false;
            if (question.conditions.length <= 0) final_value = true;

            if (final_value == true && (question.id == 3 || question.id == 4 || question.id == 5 || question.id == 6) && (btnSelected.value == 2 || btnSelected.value == 3)) {
                final_value = false;
            }

            if (form.value[question.id] !== '' && form.value[question.id] !== 'null' && (form.value[question.id] !== 0)) final_value = true;

            if ((form.value[question.id] === '' || form.value[question.id] === 'null' || (form.value[question.id] === 0)) && question.type_question !== 'textarea' ) final_value = false;

            conditions.value[question.id] = final_value;
        }
    }

    let conditionsForm = ref({});

    const generateRulesForm = () => {
        let qt = questionsForm.value;
        for (var k in formGroup.value[btnSelected.value]){
            for (var j in qt) {
                if (qt[j].mandatory && conditionsForm.value[k + qt[j].id]) {
                    var values = [];
                    values.push({
                        required: true,
                        message: 'Campo Obrigatório',
                        trigger: ['change', 'blur']
                    });
                    if (qt[j].type_question == 'number') {
                        if (qt[j].description == 'Nº CC' || qt[j].description == 'Nº CC Responsável') {
                            values.push({
                                validator: validateNumberCC,
                                trigger: ['blur', 'change']
                            });
                        } else {
                            values.push({
                                validator: validateNumber,
                                trigger: ['blur', 'change']
                            });
                            if (qt[j].num_digits > 0) {
                                const numDigits = parseInt(qt[j].num_digits, 10);
                                values.push({
                                    validator: (rule, value, callback) => {
                                        if (value.length > numDigits) {
                                            return callback(new Error(
                                                `Este campo não pode conter mais que ${numDigits} caracteres`
                                                ));
                                        } else {
                                            return callback();
                                        }
                                    },
                                    trigger: ['blur', 'change']
                                });
                            }
                        }
                    }
                    if (qt[j].type_question == 'phone') {
                        values.push({
                            validator: validatePhone,
                            trigger: ['blur', 'change']
                        });
                        // let idx = formGroup.value[btnSelected.value].length > 0 ? formGroup.value[btnSelected.value].length - 1: 0;
                        if (!countryPhone.value[k]) {
                            countryPhone.value[k] = {};
                        }
                        if (!countryPhone.value[k][qt[j].id]) {
                            let prefix = formGroupData.value[k + qt[j].id] && formGroupData.value[k + qt[j].id].prefix ? formGroupData.value[k + qt[j].id].prefix : '';
                            countryPhone.value[k][qt[j].id] = prefix;
                            let number = formGroupData.value[k + qt[j].id] && formGroupData.value[k + qt[j].id].number ? formGroupData.value[k + qt[j].id].number : ''
                            formGroupData.value[k + qt[j].id] = number;
                        }
                    }
                    if (qt[j].type_question == 'email') {
                        values.push({
                            type: 'email',
                            message: 'Insira um endereço de email válido',
                            trigger: ['blur', 'change']
                        });
                    }
                    if (qt[j].type_question == 'image') {
                        values.push({
                            validator: validateImage,
                            trigger: ['blur', 'change']
                        });
                    }
                    if (qt[j].type_question == 'date') {
                        values.push({
                            validator: validateDateRange,
                            trigger: ['blur', 'change']
                        });
                    }
                    rulesForm.value[k + qt[j].id] = values;
                } else {
                    rulesForm.value[k +qt[j].id] = [];
                }
            }
        }
        rulesForm.value['conditions'] = [{
            validator: (rule, value, callback) => {
                if (!value) {
                    return callback(new Error(`Deve ler e aceitar a política de privacidade`));
                } else {
                    return callback();
                }
            },
            trigger: ['blur']
        }];
    }

    const checkConditionForm = () => {
        for (var k in formGroup.value[btnSelected.value]){
            for (var i in questionsForm.value) {
                let question = questionsForm.value[i];
                conditionsForm.value[k + question.id] = true;

                conditionsForm.value[k + question.id] = true;
                let final_value = false;
                if (question.conditions.length <= 0) final_value = true;
                for (var j in question.conditions) {
                    if (question.conditions[j].rule == '==') {
                        let value = formGroupData.value[k + question.conditions[j].question_id] == question.conditions[j].answer_id;
                        if (!question.conditions[j].status) value = !value;
                        final_value = value;
                        if (conditionsForm.value[k + question.id] && final_value == false) {
                            conditionsForm.value[k + question.id] = null;
                        }
                    }
                    if (question.conditions[j].rule == '!=') {
                        let value = formGroupData.value[question.conditions[j].question_id] != question.conditions[j].answer_id;
                        if (!question.conditions[j].status) value = !value;
                        final_value = value;
                    }
                    if (question.conditions[j].rule == '<=') {
                        let value = formGroupData.value[question.conditions[j].question_id] <= question.conditions[j].answer_id;
                        if (!question.conditions[j].status) value = !value;
                        if (question.conditions[j].concat_condition && question.conditions[j].concat_condition ==
                            '&&') {
                            value = final_value == true && value == true ? true : false;
                        } else {
                            value = final_value == true ? true : value;
                        }
                        final_value = value;
                    }
                    if (question.conditions[j].rule == '>=') {
                        let value = formGroupData.value[question.conditions[j].question_id] >= question.conditions[j].answer_id;
                        if (!question.conditions[j].status) value = !value;
                        if (question.conditions[j].concat_condition && question.conditions[j].concat_condition ==
                            '&&') {
                            value = final_value == true && value == true ? true : false;
                        } else {
                            value = final_value == true ? true : value;
                        }
                        final_value = value;
                    }
                }
                // if (question.isSpecialBoards && (formGroupData.value[question_quadro.value] == null || formGroupData.value[question_quadro.value] == '')) final_value = false;
                conditionsForm.value[k + question.id] = final_value;
            }
        }
    }

    const validateNumber = (rule, value, callback) => {
        const numberPattern = /^[0-9]+$/;
        if (!value) {
            return callback(new Error('Campo obrigatório'));
        } else if (!numberPattern.test(value)) {
            return callback(new Error('Este campo deve conter apenas números'));
        } else {
            return callback();
        }
    };

    const validateNumberCC = (rule, value, callback) => {
        const citizenCardPattern = /^[0-9]{8}[0-9][A-Za-z0-9][A-Za-z0-9][A-Za-z0-9]*$/;
        if (!value) {
            return callback(new Error('Campo obrigatório'));
        } else if (!citizenCardPattern.test(value)) {
            return callback(new Error('Número inválido. Deve conter 12 caracteres (Sem espaçamentos)'));
        } else {
            const isLetter = (char) => /[A-Za-z]/.test(char);

            // Verificar se pelo menos um dos caracteres é uma letra
            if (!isLetter(value[9]) && !isLetter(value[10])) {
                return callback(new Error('Número inválido. Deve conter 12 caracteres (Sem espaçamentos)'));
            }

            return callback();
        }
    };

    const validateMaxDigits = (rule, value, callback, max_digits) => {
        if (value.length > max_digits) {
            return callback(new Error(`Este campo não pode conter mais que ${max_digits} caracteres`));
        } else {
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
    const validateDateRange = (rule, value, callback) => {
        if (!value) {
            callback(new Error('Campo obrigatório'));
        } else {
            const date = new Date(value);
            const start = new Date(1924, 5, 30); // 30 de junho de 1924
            const end = new Date(2010, 11, 31); // 31 de dezembro de 2010

            // if (date < start || date > end) {
            //     callback(new Error(`Data não cumpre as Condições de Participação`));
            // } else {
                callback();
            // }
        }
    }

    const checkFilterable = (item) => {
        if (item) {
            if (item.type_question === 'country' || item.type_question === 'district' || item.type_question ===
                'concelho' || item.type_question === 'freguesia' || item.isTraje) {
                return true;
            }
        }
        return false;
    }


    const handleRemove = (uploadFile, uploadFiles) => {}

    const handlePreview = (file) => {}

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
        const results = queryString ?
            data.filter(createFilter(queryString)) :
            data;
        // Chamar função de callback para retornar as sugestões
        cb(results);
    };

    let oldFormGroup = {
        1: [],
        2: [],
        3: []
    };

    // Ter em atenção que só deveria de deixa adicionar um novo quando todos os dados do atual estiverem corretamente preenchidos!
    const changeBtInscricao = async (idx_bt) => {
        let old_id = btnSelected.value;
        btnSelected.value = null;
        for (var j in btnToSelect.value) {
            if (btnToSelect.value[j].active == 1) {
                oldFormGroup[btnToSelect.value[j].id] = formGroup.value[btnToSelect.value[j].id];
            }
            btnToSelect.value[j].active = 0;
        }
        btnToSelect.value[idx_bt].active = 1;
        let new_id = btnToSelect.value[idx_bt].id;
        btnSelected.value = btnToSelect.value[idx_bt].id;
        formGroup.value[btnSelected.value] = oldFormGroup[btnSelected.value];
        if (old_id === 2 && new_id === 3 && oldFormGroup[new_id].length <= 0 && oldFormGroup[old_id].length > 0) {
            formGroup.value[btnSelected.value] = oldFormGroup[old_id];
        }
        if (old_id === 3 && new_id === 2 && oldFormGroup[new_id].length <= 0 && oldFormGroup[old_id].length > 0) {
            formGroup.value[btnSelected.value] = oldFormGroup[old_id].slice(0, 1);
        }
    }

    const changeStep = async() => {
        loading.value = true;
        rules.value['conditions'] = [{
            validator: (rule, value, callback) => {
                if (!value) {
                    return callback(new Error(`Deve ler e aceitar a política de privacidade`));
                } else {
                    return callback();
                }
            },
            trigger: ['blur']
        }];
        currentStep.value = 1;
        loading.value = false;
    }

    const addFormGroup = async() => {
        loading.value = true;
        formResource.participants({'board': selectedValueBoard.value, 'size': formGroup.value[btnSelected.value].length + 1}).then(data => {
            questionsForm.value = data;
            addFormData();
        }).catch(error => {
            console.log(error);
            loading.value = false;
        });
    }

    const addFormData = async() => {
        var form_length = formGroup.value[btnSelected.value].length;
        if (form_length > 0) {
            if (!participanteRef) {
                ElMessage({
                    message: 'Não foi possível adicionar participante. Tente novamente.',
                    type: 'error',
                    duration: 5000
                });
                loading.value = false;
                return;
            }
            await participanteRef.value.validate((valid, fields) => {
                if (valid) {
                    let prop = btnSelected.value.toString() + formGroup.value[btnSelected.value].length.toString();
                    formGroup.value[btnSelected.value].push({
                        name: 'Participante ' + (formGroup.value[btnSelected.value].length + 2),
                        prop: prop
                    });
                    formGroupData[prop] = null;
                    checkConditionForm();
                    generateRulesForm();
                    loading.value = false;
                } else {
                    ElMessage({
                        message: 'Devem ser preenchidos todos os campos obrigatórios dos participantes.',
                        type: 'error',
                        duration: 5000
                    });
                    loading.value = false;
                }
            })
        } else {
            let prop = btnSelected.value.toString() + formGroup.value[btnSelected.value].length.toString();
            formGroup.value[btnSelected.value].push({
                prop: prop
            });
            formGroupData[prop] = null;
            checkConditionForm();
            generateRulesForm();
            loading.value = false;
        }
    }

    let loadingDelete = ref(false);

    const deleteData = (idx) => {
        loadingDelete.value = true;
        const selectedButton = btnSelected.value;

        // Cria um novo objeto para armazenar os dados atualizados
        const newFormGroupData = {};

        // Remove as propriedades cujas chaves começam com o índice especificado e reindexa as restantes
        Object.keys(formGroupData.value).forEach(key => {
            const keyIndex = parseInt(key.charAt(0));
            if (keyIndex > idx) {
            const newKey = (keyIndex - 1).toString() + key.slice(1);
            newFormGroupData[newKey] = formGroupData.value[key];
            } else if (keyIndex !== idx) {
            newFormGroupData[key] = formGroupData.value[key];
            }
        });

        // Atualiza o objeto reativo com os novos dados
        formGroupData.value = newFormGroupData;

        // Remove o elemento do formGroup
        formGroup.value[selectedButton].splice(idx, 1);
        
        const entries = Object.entries(countryPhone.value);
        const updatedEntries = entries.filter(([key]) => key !== idx.toString());
        const reorderedEntries = updatedEntries.map(([key, value], index) => [index, value]);
        countryPhone.value = Object.fromEntries(reorderedEntries);
        populateMaiorMenorIdade();
        loadingDelete.value = false;
    };

    const displayMessage = (message, type) => {
        ElMessage({
            message: message,
            type: type,
            duration: 5000
        });
    }

    let selectedValueBoard = ref(0);

    const getSelectedBoard = (valor) => {
        form.value[question_trajes.value] = null;
        for (var idx_group in formGroup.value[btnSelected.value]){
            formGroupData.value[idx_group + question_trajes.value] = null;
        }
        selectedValueBoard.value = valor;
    }

    const populateMaiorMenorIdade = () => {
        let q_id = 2;
        existsMaior.value = {};
        menorIdade.value = {};
        isMaiorIdade.value = false;
        isMenorIdade.value = false;
        if (form.value[q_id] != '' && form.value[q_id] != 'null' && form.value[q_id] != null && form.value[q_id] != undefined) {
            checkMaiorIdade(form.value[q_id], q_id);
        }
        for (let j in formGroup.value[btnSelected.value]) {
            let q_f_id = j + q_id;
            if (formGroupData.value[q_f_id] != '' && formGroupData.value[q_f_id] != 'null' && formGroupData.value[q_f_id] != null && formGroupData.value[q_f_id] != undefined) {
                checkMaiorIdade(formGroupData.value[q_f_id], q_f_id);
            }
        }
    }

    const checkMaiorIdade = (selectDate, formModel) => {
        if (!existsMaior.value[formModel]) {
            existsMaior.value[formModel] = false;
        }
        if (!menorIdade.value[formModel]) {
            menorIdade.value[formModel] = false;
        }
        const selectedDate = new Date(selectDate);
        const currentYear = new Date().getFullYear();
        const cutoffDate = new Date(currentYear, 5, 30); // 30 de junho do ano corrente

        // Data limite 18 anos atrás
        const maiorIdade = new Date(cutoffDate);
        maiorIdade.setFullYear(cutoffDate.getFullYear() - 18);
        const m_idade = new Date(cutoffDate);
        m_idade.setFullYear(cutoffDate.getFullYear() - 13);
        
        var value = false;
        if (selectedDate <= maiorIdade) {
            existsMaior.value[formModel] = true;
        } else {
            existsMaior.value[formModel] = false;
        }
        for (var j in existsMaior.value) {
            if (existsMaior.value[j]) value = true;
        }

        isMaiorIdade.value = value;

        var val_2 = false;
        if (selectedDate >= m_idade) {
            menorIdade.value[formModel] = true;
        } else {
            menorIdade.value[formModel] = false;
        }
        for (var l in menorIdade.value) {
            if (menorIdade.value[l]) val_2 = true;
        }
        isMenorIdade.value = val_2;

        if (isMaiorIdade.value && isMenorIdade.value) {
            rulesForm.value['menorIdade'] = [{
                validator: (rule, value, callback) => {
                    if (!value) {
                        return callback(new Error(`Deve aceitar a condição de participação.`));
                    } else {
                        return callback();
                    }
                },
                trigger: ['blur']
            }];
        } else {
            rulesForm.value['menorIdade'] = [];
        }

    }

    const optionDisabled = (answer) => {
        if (btnSelected.value == 1) {
            return answer.insc_limit <= answer.total_insc ? true : false;
        }
        if (btnSelected.value == 2) {
            return answer.insc_limit < (answer.total_insc + 2) ? true : false;
        }
        if (btnSelected.value == 3) {
            return answer.insc_limit < (answer.total_insc + 3) ? true : false;
        }
    }
</script>

<template>
    <el-card v-if="!submitted">
        <div class="logo-container">
            <img class="logoWidth" src="/uploads/viana-festas-logo.png">
        </div>
        <div v-if="!active">
            <not-available :cortejo="false"></not-available>
            <div class="btnSelect">
                <el-button type="" size="large" :loading="loadingCondition" @click="goBack">
                    Voltar
                </el-button>
            </div>
        </div>
        <div v-else>
            <div v-if="displayPdf">
                <div class="pdf-container" v-if="!mobile">
                    <embed src="REG_CORTEJO_INDIVIDUAL.pdf" type="application/pdf" width="100%" height="600px" />
                    <!-- <object data="REG_CORTEJO_INDIVIDUAL.pdf" type="application/pdf" width="100%" height="600px"> -->
                    <p>Não consegue visualizar o documento? Pré-visualize o documento <a class="primaryOrange"
                            href="REG_CORTEJO_INDIVIDUAL.pdf" target="_blank">aqui</a></p>
                    <p>Ou faça download <el-link class="primaryOrange" :underline="false"
                            href="REG_CORTEJO_INDIVIDUAL.pdf"
                            download="Condicoes-Participacao-Desfile.pdf">aqui</el-link></p>
                    <!-- </object> -->
                </div>
                <div v-else>
                    <p>Pré-visualize o documento <a class="primaryOrange" href="REG_CORTEJO_INDIVIDUAL.pdf"
                            target="_blank">aqui</a></p>
                    <p>Ou faça download <el-link class="primaryOrange" :underline="false"
                            href="REG_CORTEJO_INDIVIDUAL.pdf"
                            download="Condicoes-Participacao-Desfile.pdf">aqui</el-link></p>

                </div>
                <el-form :rules="ConditionsRules" ref="conditionsFormRef" class="formContainer" :model="formConditions"
                    label-width="auto" :label-position="labelPosition">
                    <el-form-item class="opensansName termsConditions PClass" label="" prop="conditions">
                        <el-checkbox v-model="formConditions['conditions']">
                            <div class="termsConditionsClass">
                                <span class="opensansName">Declaro que li e aceito as condições de participação
                                    mencionadas no documento acima disponibilizado.</span>
                            </div>
                        </el-checkbox>
                    </el-form-item>
                </el-form>
                <div class="btnSelect">
                    <el-button type="" size="large" :loading="loadingCondition" @click="goBack">
                        Voltar
                    </el-button>
                    <el-button type="primary" size="large" :loading="loadingCondition" @click="submitConditions">
                        Continuar
                    </el-button>
                </div>
            </div>
            <div v-else>
                <div v-if="progress < 100" class="progressCircle">
                    <el-progress type="circle" :percentage="progress" color="#C797C5"></el-progress>
                    <p class="opensansName">A carregar dados...</p>
                </div>
                <div v-if="progress >= 100">
                    <div v-if="!displayQRCode">
                        <el-form v-if="btnSelected !== null" :rules="rules" ref="formRef" class="formContainer" :model="form" label-width="auto" :label-position="labelPosition">
                            <el-steps v-if="!checkHidden" :active="currentStep" finish-status="success" class="stepContainer">
                                <el-step title="Inscrição">
                                </el-step>
                                <el-step title="Participantes">
                                </el-step>
                            </el-steps>
                            <div v-if="currentStep == 0 || checkHidden">
                                <div v-for="(question, idx) in questions" :key="idx">
                                    <el-form-item v-if="conditions[question.id]" :label="question.description" :prop="question.id.toString()" class="opensansName">
                                        <!-- Text -->
                                        <el-input class="opensansName"
                                        :disabled="true"
                                            v-if="(question.type_question === 'text' || question.type_question === 'email') && conditions[question.id]"
                                            v-model="form[question.id]" :placeholder="question.description"
                                            :required="question.mandatory" autofocus />
                                        <!-- Phone number -->
                                        <div class="displayPhoneNumber"
                                            v-if="question.type_question === 'phone' && conditions[question.id]">
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
                                        <el-input class="opensansName" v-model="form[question.id]" :disabled="true"
                                            v-if="question.type_question === 'number' && conditions[question.id]" />
                                        <!-- Date -->
                                        <el-date-picker v-if="question.type_question === 'date' && conditions[question.id]" :disabled="true"
                                            class="opensansName" v-model="form[question.id]" format="YYYY-MM-DD"
                                            value-format="YYYY-MM-DD" type="date" placeholder="Selecione um dia" @change="(val) => checkMaiorIdade(val, question.id)" />
                                        <!-- Country -->
                                        <el-select style="width:75%" class="opensansName" :disabled="true"
                                            v-if="(question.type_question === 'country' || question.type_question === 'district' || question.type_question === 'concelho' || question.type_question === 'freguesia' || question.type_question === 'select')  && conditions[question.id] && !question.isSpecialBoards && !question.isSpecial"
                                            v-model="form[question.id]" :filterable="checkFilterable(question)" clearable
                                            placeholder="Selecionar">
                                            <el-option v-for="answer in question.answers" class="opensansName"
                                                :key="answer.id" :label="answer.description"
                                                :value="answer.id" />
                                        </el-select>
                                        <!-- Quadros -->
                                        <el-select style="width:75%" class="opensansName" :disabled="true"
                                            v-if="question.type_question === 'select'  && conditions[question.id] && !question.isSpecialBoards && question.isSpecial"
                                            v-model="form[question.id]" :filterable="checkFilterable(question)" clearable
                                            placeholder="Selecionar" @change="getSelectedBoard">
                                            <el-option v-for="answer in question.answers" class="opensansName"
                                                :key="answer.id" :label="answer.description"
                                                :value="answer.id"
                                                :disabled="optionDisabled(answer)" />
                                        </el-select>
                                        <!-- Trajes when board selected -->
                                        <el-select style="width:75%" class="opensansName" :disabled="true"
                                            v-if="(question.type_question === 'select')  && conditions[question.id] && question.isSpecialBoards"
                                            v-model="form[question.id]" :filterable="checkFilterable(question)" clearable
                                            placeholder="Selecionar">
                                            <el-option v-for="answer in question.answers[form[question_quadro]]" class="opensansName"
                                                :key="answer.id" :label="answer.description"
                                                :value="answer.id" />
                                        </el-select>
                                        <!-- Checkbox -->
                                        <el-checkbox class="opensansName" :disabled="true"
                                            v-if="(question.type_question === 'checkbox')  && conditions[question.id] && question.multiple"
                                            v-model="form[question.id]" label="Option 1" />
                                        <el-radio-group class="opensansName" :disabled="true"
                                            v-if="(question.type_question === 'checkbox')  && conditions[question.id] && !question.multiple"
                                            v-model="form[question.id]">
                                            <el-radio v-for="answer in question.answers" class="opensansName"
                                                :key="answer.id"
                                                :label="answer.id">{{ answer . description }}</el-radio>
                                        </el-radio-group>
                                        <!-- Fazer o autocomplete para o nome dos ranchos -->
                                        <el-autocomplete v-if="question.type_question === 'rancho'" :disabled="true"
                                            v-model="form[question.id]"
                                            :fetch-suggestions="(queryString, cb) => querySearch(queryString, cb, question.answers)"
                                            :trigger-on-focus="false" clearable class="opensansName"
                                            :placeholder="question.description" />
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
                                        </div>
                                        <!-- Textarea -->
                                        <el-input v-if="question.type_question === 'textarea'" class="opensansName"
                                            v-model="form[question.id]" :autosize="{ minRows: 2, maxRows: 4 }"
                                            type="textarea" :placeholder="question.description" />
                                    </el-form-item>
                                </div>
                            </div>
                            <div v-if="formGroup.length > 0 && currentStep == 1 && !checkHidden">
                                <el-form :rules="rulesForm" ref="participanteRef" class="formContainer" :model="formGroupData" label-width="auto" :label-position="labelPosition">
                                    <el-card class="cardParticipante" v-for="(group, idx_group) in formGroup[btnSelected]" :key="idx_group">
                                        <template #header>
                                            <div class="card-header">
                                                <span>Participante {{ idx_group + 2 }}</span>
                                            </div>
                                        </template>
                                        <div v-for="(qt_form, idx_form) in questionsForm" :key="idx_form">
                                            <el-form-item v-if="conditionsForm[idx_group.toString() + qt_form.id]" :label="qt_form.description" :prop="idx_group.toString() + qt_form.id.toString()" class="opensansName" >
                                                    <!-- Text -->
                                                <el-input class="opensansName"
                                                    v-if="(qt_form.type_question === 'text' || qt_form.type_question === 'email')"
                                                    v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :placeholder="qt_form.description"
                                                    :required="qt_form.mandatory" autofocus disabled />

                                                    <!-- Phone number -->
                                                    <div class="displayPhoneNumber"
                                                        v-if="qt_form.type_question === 'phone' && conditionsForm[idx_group.toString() + qt_form.id.toString()]">
                                                        <el-select class="opensansName" v-model="countryPhone[idx_group][qt_form.id]"
                                                            filterable clearable placeholder="Selecionar" disabled>
                                                            <el-option v-for="(code, idx_code) in codes" class="opensansName"
                                                                :key="code.id" :label="code.country + ' +' + code.code"
                                                                :value="code.code" />
                                                        </el-select>
                                                        <el-input class="opensansName" v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" disabled />
                                                    </div>

                                                    <!-- Date -->
                                                    <el-date-picker v-if="qt_form.type_question === 'date' && conditionsForm[idx_group.toString() + qt_form.id.toString()]"
                                                        class="opensansName" v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" format="YYYY-MM-DD"
                                                        value-format="YYYY-MM-DD" type="date" placeholder="Selecione um dia" @change="(val) => checkMaiorIdade(val, idx_group.toString() + qt_form.id.toString())" disabled />
                                                        <!-- Country -->
                                                    <el-select style="width:75%" class="opensansName"
                                                        v-if="(qt_form.type_question === 'country' || qt_form.type_question === 'district' || qt_form.type_question === 'concelho' || qt_form.type_question === 'freguesia' || qt_form.type_question === 'select')  && conditionsForm[idx_group.toString() + qt_form.id.toString()] && !qt_form.isSpecialBoards && !qt_form.isSpecial"
                                                        v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :filterable="checkFilterable(qt_form)" clearable
                                                        placeholder="Selecionar" disabled>
                                                        <el-option v-for="answer in qt_form.answers" class="opensansName"
                                                            :key="answer.id" :label="answer.description"
                                                            :value="answer.id" />
                                                    </el-select>

                                                    <!-- Trajes when board selected -->
                                                    <el-select style="width:75%" class="opensansName"
                                                        v-if="(qt_form.type_question === 'select') && conditionsForm[idx_group.toString() + qt_form.id.toString()] && qt_form.isSpecialBoards"
                                                        v-model="formGroupData[idx_group.toString() + qt_form.id.toString()]" :filterable="checkFilterable(qt_form)" clearable
                                                        placeholder="Selecionar" disabled>
                                                        <el-option v-for="answer_p in qt_form.answers[form[question_quadro]]" class="opensansName"
                                                            :key="answer_p.id" :label="answer_p.description"
                                                            :value="answer_p.id" />
                                                    </el-select>
                                            </el-form-item>
                                        </div>
                                        <!-- <div v-if="btnSelected == 3" class="btnSelect">
                                            <el-button :loading="loadingDelete" type="danger" @click="deleteData(idx_group)"><el-icon class="header-icon"><delete /></el-icon></el-button>
                                        </div> -->
                                    </el-card>
                                    <!-- <div v-if="!checkHidden && currentStep == 1" class="btnSelect" style="margin-top: 10px !important">
                                        <el-button type="primary" :loading="loading" @click="addFormGroup">
                                            <el-icon style="margin-right: 5px !important"><Plus /></el-icon> Adicionar Participante
                                        </el-button>
                                    </div> -->
                                    <el-form-item v-if="isMaiorIdade && isMenorIdade" class="opensansName termsConditions" label="" prop="menorIdade">
                                        <el-checkbox v-model="formGroupData['menorIdade']">
                                            <div class="termsConditionsClass">
                                                <span class="opensansName">O participante com idade igual ou superior a 18 anos aceita ser o responsável pelo(s) participante(s) com idade igual ou inferior a 13 anos</span>
                                            </div>
                                        </el-checkbox>
                                    </el-form-item>
                                    <div v-if="currentStep == 1">
                                        <el-form-item class="opensansName termsConditions" label="" prop="conditions">
                                            <el-checkbox v-model="formGroupData['conditions']">
                                                <div class="termsConditionsClass">
                                                    <span class="opensansName">Dou o meu consentimento e autorizo a recolha e
                                                        tratamento dos meus dados pessoais, tendo por finalidade o objetivo
                                                        deste formulário assim como declaro que li e aceito <el-link
                                                            class="primaryOrange" :underline="false"
                                                            @click="openPrivacyDialog">Política de privacidade</el-link></span>
                                                </div>
                                            </el-checkbox>
                                        </el-form-item>
                                    </div>
                                </el-form>
                            </div>
                            <!-- <div v-if="!checkHidden && currentStep == 1" class="btnSelect" style="margin-top: 10px !important">
                                <el-button type="primary" :loading="loading" @click="addFormGroup">
                                    <el-icon style="margin-right: 5px !important"><Plus /></el-icon> Adicionar Participante
                                </el-button>
                            </div>
                            <div v-if="checkHidden || currentStep == 1">
                                <el-form-item v-if="isMaiorIdade && isMenorIdade" class="opensansName termsConditions" label="" prop="menorIdade">
                                    <el-checkbox v-model="form['menorIdade']">
                                        <div class="termsConditionsClass">
                                            <span class="opensansName">O participante com idade superior a 18 anos aceita ser o responsável pelo(s) participante(s) com idade igual ou menor a 13 anos</span>
                                        </div>
                                    </el-checkbox>
                                </el-form-item>
                                <el-form-item class="opensansName termsConditions" label="" prop="conditions">
                                    <el-checkbox v-model="form['conditions']">
                                        <div class="termsConditionsClass">
                                            <span class="opensansName">Dou o meu consentimento e autorizo a recolha e
                                                tratamento dos meus dados pessoais, tendo por finalidade o objetivo
                                                deste formulário assim como declaro que li e aceito <el-link
                                                    class="primaryOrange" :underline="false"
                                                    @click="openPrivacyDialog">Política de privacidade</el-link></span>
                                        </div>
                                    </el-checkbox>
                                </el-form-item>
                            </div> -->
                        </el-form>
                        <div class="btnSelect">
                            <el-button v-if="currentStep == 0" type="" size="large" :loading="loading" @click="goBack">
                                Voltar
                            </el-button>
                            <el-button v-if="btnSelected !== null && (!checkHidden && currentStep == 0)" type="primary" size="large" :loading="loading" @click="changeStep">
                                Seguinte
                            </el-button>
                            <el-button v-if="btnSelected !== null && (!checkHidden && currentStep == 1)" size="large" :loading="loading" @click="currentStep = 0">
                                Anterior
                            </el-button>
                            <el-button v-if="btnSelected !== null && (checkHidden || currentStep == 1)" type="primary" size="large" :loading="loading" @click="submit">
                                Inscrever
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
            <el-dialog v-model="privacyDialog" title="Política de Privacidade" :width="dialogWidth"
                :before-close="closePrivacyDialog">
                <span class="opensansName spanPrivacy">Os dados pessoais que forem recolhidos no âmbito da plataforma,
                    serão tratados com respeito pela legislação de proteção dos dados pessoais, nomeadamente a Lei n.º
                    67/98, de 26 de Outubro, e a Lei n.º 41/2004, de 18 de Agosto, bem como a partir de 25 de Maio de
                    2018, o RGPD - Regulamento Geral de Proteção de Dados (EU 2016\\679), sendo que a plataforma em
                    causa pressupõe o conhecimento e aceitação das seguintes condições:<ol type="a">
                        <li>Os membros da plataforma aceitam que o fornecimento dos dados é necessário e obrigatório
                            para efeitos de processamento das inscrições no Desfile de Mordomia, apuramento dos
                            participantes e realização do Desfile da Mordomia. Os dados serão recolhidos e tratados pela
                            entidade promotora, VIANAFESTAS;</li>
                        <li> A VIANAFESTAS, enquanto promotora do evento “Romaria de Nossa Senhora d'Agonia”, através da
                            respetiva Comissão de Festas, garante a segurança e confidencialidade do tratamento,
                            garantindo a possibilidade de acesso, retificação e cancelamento dos dados aos participantes
                            que assim o desejem e o comuniquem, através do correio eletrónico, para <a
                                class="primaryOrange opensansName"
                                href="mailto:vianafestas@vianafestas.com">vianafestas@vianafestas.com</a>;</li>
                        <li> Os dados de identificação pessoal obtidos poderão ser disponibilizados para o apuramento de
                            responsabilidade civil e criminal, mediante solicitação da autoridade judiciária competente,
                            nos termos da legislação aplicável. </li>
                    </ol></span>
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
    .register-container {
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

    .register-container {
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

    .stepContainer {
        max-width: 85% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        margin-top: auto !important;
        margin-bottom: 20px !important;
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

    .el-select-dropdown__empty>span {
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

    .startMain-container {
        min-height: 100% !important;
    }

    .startMobile-container {
        min-height: 100% !important;
    }
    .cardParticipante {
        width: 100% !important;
        max-width: 100% !important;
        margin-bottom: 15px !important;
        padding: 0px !important;
    }

    .btnSelect.el-button--primary {
        color: white !important;
    }

    .btnSelect.el-button--primary:focus {
        color: white !important;
    }
</style>
