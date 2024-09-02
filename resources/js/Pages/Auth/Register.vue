<template>
    <div class="login-container">
      <el-card class="register-card">
        <h1>IUP.pt</h1>
  
        <el-form ref="loginForm" :rules="formRules" :model="form" label-width="160px" label-position="top">
          <el-form-item label="Nome" prop="name">
            <el-input v-model="form.name" />
          </el-form-item>
  
          <el-form-item label="Email" prop="email">
            <el-input v-model="form.email" />
          </el-form-item>
  
          <el-form-item label="Telefone" prop="phone">
            <el-input v-model="form.phone" />
          </el-form-item>
  
          <el-form-item label="Password" prop="password">
            <el-input v-model="form.password" show-password />
          </el-form-item>
  
          <el-form-item label="Confirmar password" prop="confirmarPassword">
            <el-input v-model="form.confirmPassword" show-password />
          </el-form-item>
  
          <el-form-item label="Termos" prop="termos-condicoes">
            <div class="flex items-center">
              <el-checkbox v-model="form.terms" name="terms" />
              <span>
                Aceito os <el-link target="_blank" type="primary" href="#/termos">Termos de serviço</el-link> e a <el-link target="_blank" href="#/privacidade">Política de privacidade</el-link>
              </span>
            </div>
          </el-form-item>
  
          <div class="alignBtns">
            <el-button type="primary" class="log-in" :disabled="form.processing" @click="submit()">
              Registar
            </el-button>
            <el-link class="alignBtnsMargin" href="#/login">
              Já registado?
            </el-link>
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
    name: 'RegistrationModule',
    data() {
      var validateConfirmPassword = (rule, value, callback) => {
        if (this.form.confirmPassword !== this.form.password) {
          callback(new Error('As palavras-passe não correspondem!'));
        } else {
          callback();
        }
      };
      var validatePasswordPattern = (rule, value, callback) => {
        if (!value.match(/((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,64})/g)) {
          callback(new Error('A palavra-passe deve conter pelo menos 8 caracteres, incluindo uma maiúscula e um caractere especial.'));
        } else {
          callback();
        }
      };
      return {
        form: {
          name: '',
          email: '',
          phone: null,
          active: 1,
          type: 'cliente',
          role: 'cliente',
          password: '',
          password_confirmation: '',
          terms: false,
          processing: true,
        },
        formRules: {
          name: [{ required: true, message: 'Campo obrigatório', trigger: 'blur' }],
          email: [{ type: 'email', message: 'Por favor introduza um email correcto', trigger: ['blur'] },
            { required: true, message: 'Por favor introduza um email correcto', trigger: ['blur'] },
          ],
          password: [
            { required: true, message: 'Password é obrigatório', trigger: 'blur' },
            { min: 6, message: 'Password demasiado curta. Min: 8', trigger: 'blur' },
            { max: 25, message: 'Password demasiado longa', trigger: 'blur' },
            { validator: validatePasswordPattern, trigger: ['blur', 'change'] }
          ],
          confirmPassword: [{ validator: validateConfirmPassword, trigger: ['blur', 'change'] }],
          phone: [{ required: true, message: 'Numero de telefone é obrigatório', trigger: 'blur' }],
        },
      };
    },
    watch: {
      form: {
        handler(val){
          if (this.form.terms === true) {
            this.form.processing = false;
          } else {
            this.form.processing = true;
          }
        },
        deep: true
      }
    },
    methods: {
      submit() {
        this.$refs['loginForm'].validate(async(valid) => {
          if (valid) {
            userResource.register(this.form).then(result => {
              const loginForm = {};
              loginForm.email = this.form.email;
              loginForm.password = this.form.password;
              this.$store.dispatch('user/login', loginForm)
                .then(() => {
                  this.$router.push('/');
                })
                .catch(error => {
                  console.log(error);
                });
            }).catch(error => {
              console.log(error);
            });
          } else {
            console.log('not valid');
          }
        });
      }
    }
  });
  </script>
  <style>
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
  
  .log-in {
      width: 100%;
  }
  
  .register-card {
      width: 100%;
      max-width: 40rem;
  }
  
  .flex.items-center>span{
      margin-left: 10px;
  }
  
  .alignBtns{
      text-align: center;
  }
  
  .alignBtnsMargin{
      margin-top: 15px !important;
  }
  
  #app{
      width: 100% !important;
  }
  
  </style>