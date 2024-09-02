<template>
    <div class="login-container">
      <el-card>
        <h1>{{ $t('app.title') }}</h1>

        <h5>Definir nova palavra-passe</h5>
        <p style="font-size: 14px; text-align: center;">Utilize o mesmo endereço de email que usou no registo</p>

        <el-form label-position="left" ref="resetPassword" :rules="formRules" :model="form" label-width="160px">
          <el-form-item label="Email" prop="email">
            <el-input v-model="form.email" type="email" placeholder="email" required autofocus></el-input>
          </el-form-item>

          <el-form-item label="Password" prop="password">
            <el-input v-model="form.password" type="password" placeholder="Password" required autofocus></el-input>
          </el-form-item>

          <el-form-item label="Confirmar Password" prop="confirm_password">
            <el-input v-model="form.confirm_password" type="password" placeholder="Password" required autofocus></el-input>
          </el-form-item>

          <div class="alignContent">
            <el-button type="primary" class="log-in" :disabled="form.processing" :loading="loading" @click="handleSubmit()">Definir nova Password</el-button>
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
      props: { status: String},
      data() {
        var validateConfirmPassword = (rule, value, callback) => {
          if (this.form.confirm_password !== this.form.password) {
              callback(new Error('As palavras-passe não correspondem!'));
          } else {
              callback();
          }
        };
        return {
          loading: false,
          form: {
            email: '',
            phone: '',
            password: '',
            confirm_password: ''
          },
          user_token: null,
          formRules: {
            email: [{ type: 'email', message: 'Por favor introduza um email correcto', trigger: ['blur'] },
                    { required: true, message: 'Por favor introduza um email correcto', trigger: ['blur'] },
                  ],
            password: [{ required: true, message: 'Campo obrigatório', trigger: 'blur' }],
            confirm_password: [{ required: true, validator: validateConfirmPassword, trigger: ['change', 'blur'] }],
          }
        }
      },
      mounted() {
          const token = this.$route.query && this.$route.query.id;
          this.user_token = token;
      },
    methods: {
        handleSubmit() {
          this.$refs['resetPassword'].validate(async(valid) => {
            if (valid) {
              console.log('teste');
              this.form.token = this.user_token;
              this.loading = true;
              userResource.updatePassword(this.form).then(result => {
                
                if (result) {
                  ElMessage({
                    message: `Palavra-passe alterada com sucesso`,
                    type: "success",
                    duration: 5 * 1000,
                  });

                  this.loading = false;

                  const loginForm = {};
                  loginForm.email = this.form.email;
                  loginForm.password = this.form.password;
                  // redirect
                  this.$store.dispatch('user/login', loginForm)
                    .then(() => {
                        this.$router.push('/');
                    })
                    .catch(error => {
                        console.log(error);
                    });
                } else {
                  ElMessage({
                    message: `Não foi possível alterar a palavra-passe para o utilizador selecionado`,
                    type: "error",
                    duration: 5 * 1000,
                  });
                  this.loading = false;
                }
              }).catch(error => {
                console.log(error);
              });
            } else {
              ElMessage({
                message: `Deve preencher todos os campos obrigatórios`,
                type: "error",
                duration: 5 * 1000,
              });
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

h5 {
    text-align: center;
}

.log-in {
    width: 100%;
}

.el-card {
    width: 100%;
    max-width: 35rem;
}

.alignContent{
    text-align: center !important;
}
.alignContent>a{
    margin-bottom: 10px !important;
}

</style>