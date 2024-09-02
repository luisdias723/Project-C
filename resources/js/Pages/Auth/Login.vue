<template>
    <div class="login-container">
      <el-card class="cardBackground">
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
          {{ status }}
        </div>
        
        <div class="logo-container">
          <img class="logoImage" src="/uploads/viana-festas-logo.png">
        </div>
  
        <el-form ref="loginForm" :model="form" :label-position="$filters.isMobile() ? 'top' : 'left' " label-width="120px">
          <el-form-item label="Email">
            <el-input v-model="form.email" type="email" placeholder="email" required autofocus />
          </el-form-item>
  
          <el-form-item label="Palavra-passe">
            <el-input
              v-model="form.password"
              type="password"
              :show-password="true"
              placeholder="password"
              required
              autofocus
              @keyup.enter="handleLogin()"
            />
          </el-form-item>
  
          <el-form-item label="Lembrar">
            <el-checkbox v-model="form.remember" label="Sim" name="remember" />
          </el-form-item>
  
          <div class="alignBtn">
            <el-button
              v-if="!logged"
              type="primary"
              :loading="logging"
              :disabled="form.processing"
              @click="handleLogin()"
            >
              Autenticar
            </el-button>
            <el-button v-else type="success">
              <el-icon><Check /></el-icon> Autenticado
            </el-button>
            <!-- <el-link href="#/recuperar/password" class="forgot-password-link">
              Esqueceu a palavra-passe?
            </el-link> -->
          </div>
        </el-form>
      </el-card>
    </div>
  </template>
  
  <script>
  import { defineComponent } from 'vue';
  import { Check } from '@element-plus/icons-vue';
  export default defineComponent({
    name: 'LoginPage',
    components: { Check },
    props: {
      canResetPassword: Boolean,
      status: String
    },
    data() {
      return {
        logging: false,
        logged: true,
        form: {
          email: '',
          password: '',
          remember: false
        },
      };
    },
    watch: {
      $route: {
        handler: function(route) {
          this.redirect = route.query && route.query.redirect;
        },
        immediate: true,
      },
    },
    created(){
      this.logged = false;
    },
    methods: {
      handleLogin() {
        this.logging = true;
        this.$refs.loginForm.validate(valid => {
          if (valid) {
            axios.get('/sanctum/csrf-cookie').then(() => {
              this.$store.dispatch('user/login', this.form)
                .then(() => {
                  this.$store.dispatch('settings/getInfo');
                  this.$router.push({ path: (this.redirect || '/backoffice') });
                  this.logging = false;
                  this.logged = true;
                })
                .catch(() => {
                  this.logging = false;
                });
            });
          } else {
            console.log('error submit!!');
            return false;
          }
        });
      },
    }
  });
  </script>
  
  <style scoped>
  .logoImage {
    width: 100% !important;
  }
  .login-container{
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    background: #7bafde;
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
    text-align: center;
    width: 100%;
    margin: -20px 0 20px;
  }
  .alignBtn {
    text-align: center !important;
  }
  .cardBackground {
    background: #FFDA91 !important;
  }
  </style>