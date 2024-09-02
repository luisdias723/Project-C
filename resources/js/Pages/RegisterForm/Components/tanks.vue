<script setup>
import { ref, onMounted, computed, watch } from 'vue';
</script>

<template>
    <el-card>
        <div class="logo-container">
            <img class="logoWidth" src="/uploads/viana-festas-logo.png">
        </div>
        <div>
            <h3>Inscrição submetida</h3>
            <p class="opensansName">Muito obrigado pela sua inscrição.</p>
            <p class="opensansName">A sua inscrição irá agora ser analisada pela nossa equipa.</p>
            <p class="opensansName">Caso necessite de retificações iremos entrar em contacto consigo o mais breve possível.</p>
            <p class="opensansName">Após aprovada a sua inscrição irá receber no seu email o comprovativo da sua inscrição e deverá fazer-se acompanhar do mesmo, impresso ou em formato digital, assim como do seu documento de identificação.</p>
            <p class="opensansName"><b>Relembramos que:</b></p>
            <ul class="opensansName">
              <li class="opensansName"><b>Não é permitido o uso de unhas pintadas ou de gel, curtas ou pontiagudas, nem de maquilhagem</b></li>
              <li class="opensansName"><b>As tatuagens têm de ser devidamente encobertas</b></li>
              <li class="opensansName"><b>Os piercings têm de ser removidos e/ou devidamente cobertos.</b></li>
            </ul>
        </div>
    </el-card>
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
}
.startMain-container{
  min-height: 100% !important;
}
.startMobile-container{
  min-height: 100% !important;
}
</style>