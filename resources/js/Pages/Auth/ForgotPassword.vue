<template>
    <div class="login-container">
        <el-card>

            <h3>Defina uma nova senha</h3>
            <p class="instructions text-muted">Insira o seu endereço de email e nós enviaremos instruções para definir uma nova palavra-passe.</p>
            <el-form ref="forgotPassword" :rules="formRules" :model="form" label-width="80px">

                <el-form-item label="Email" prop="email">
                    <el-input v-model="form.email" type="email" placeholder="email" required autofocus></el-input>
                </el-form-item>

                <div class="flex items-center justify-end mt-4 alignContent">
                    <el-button type="primary" class="log-in" :loading="loading" :disabled="form.processing" @click="handleSubmit()">Enviar instruções</el-button>
                    <el-link href="#/login">Iniciar sessão</el-link>
                </div>
            </el-form>
        </el-card>
    </div>
</template>

<script>
    import { defineComponent } from 'vue';
    import UserResource from '@/api/users';

    const userResource = new UserResource();

    export default defineComponent({
        components: {
        },

        props: {
            status: String
        },

        data() {
            return {
                loading: false,
                form: {
                    email: ''
                },
                formRules: {
                    email: [{ type: 'email', message: 'Por favor introduza um email correcto', trigger: ['blur'] },
                                { required: true, message: 'Por favor introduza um email correcto', trigger: ['blur'] },
                            ],
                }
            }
        },

        methods: {
            handleSubmit() {
                this.loading = true;
                this.$refs['forgotPassword'].validate(async(valid) => {
                    if (valid) {
                        userResource.getResetLink({ email: this.form.email }).then(result => {
                            ElMessage({
                                message: `Instruções enviadas para ` + this.form.email,
                                type: "success",
                                duration: 5 * 1000,
                            });
                        }).catch(error => {
                            console.log(error);
                        });
                        this.loading = false;
                    } else {
                        ElMessage({
                            message: `Deve preencher todos os campos obrigatórios`,
                            type: "error",
                            duration: 5 * 1000,
                        });
                        this.loading = false;
                    }
                });
            }
        }
    })
</script>
<style scoped>
.login-container{
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    background: #EEF1F4;
}
h1 {
    text-align: center;
}

h3 {
    text-align: center;
}

.log-in {
    width: 100%;
}

.el-card {
    width: 100%;
    max-width: 28rem;
}
.instructions {
    margin-bottom: 20px;
    font-size: 14px;
    text-align: center;
}

.alignContent{
    text-align: center !important;
    margin: 10px 0px;
}
.alignContent>a{
    margin: 10px 0px !important;
}

</style>