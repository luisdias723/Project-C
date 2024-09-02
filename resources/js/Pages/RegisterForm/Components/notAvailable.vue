<script setup>
const props = defineProps(['cortejo']);
</script>

<template>
        <div>
            <h1>Inscrições</h1>
            <h2 class="opensansName">De momento as inscrições para este evento encontram-se encerradas.</h2>
            <!-- <p class="opensansName">Qualquer dúvida entre em contacto connosco, através de <el-link class="opensansName primaryOrange" href="mailto:mordomia@vianafestas.com" target="_blank">mordomia@vianafestas.com</el-link></p> -->
            <p class="opensansName" v-if="!cortejo">Qualquer dúvida relacionado com o Desfile da Mordomia entre em contacto connosco, através de <el-link class="opensansName primaryOrange" href="mailto:mordomia@vianafestas.com" target="_blank">mordomia@vianafestas.com</el-link></p>
            <p class="opensansName" v-else>Qualquer dúvida relacionado com o Cortejo Etnográfico entre em contacto connosco, através de <el-link class="opensansName primaryOrange" href="mailto:cortejo@vianafestas.com" target="_blank">cortejo@vianafestas.com</el-link></p>
            <h2 class="opensansName">Obrigado. Boa Romaria.</h2>
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