<script setup>
import { ref, onMounted } from 'vue';

const faqs = ref([]);

let activeNames = ref([]);

const emit = defineEmits(['goBack']);

onMounted(() => {
    faqs.value = [{
        'title': 'Condições de Participação',
        'description': 'Para descarregar as condições de participação do Desfile da Mordomia, <a class="primaryOrange opensansName" download href="REG_DESFILE_MORDOMIA.pdf">clique aqui</a><br></br>Para descarregar as condições de participação do Cortejo Etnográfico, <a class="primaryOrange opensansName" download href="REG_CORTEJO_INDIVIDUAL.pdf">clique aqui</a>'
 
    },
    {
        'title': 'Avaliação do Traje',
        'description': 'Todos os trajes serão avaliados individualmente pela Comissão Organizadora destacada para o efeito de modo a garantir a indumentária correta e adequada ao Desfile da Mordomia.'
    },
    {
        'title': 'Inscrições',
        'description': 'As inscrições serão realizadas online, a partir do formulário presente neste website, à exceção das inscrições para os carros das crianças do Cortejo Histórico e Etnográfico.'
    },
    {
        'title': 'Horários no dia do Desfile da Mordomia',
        'description': 'Entre as 14:00 e 15:00 - entrada mediante apresentação de ficha de inscrição nos jardins do Antigo Governo Civil<br></br>15:00 - Organização do Desfile.<br></br>16:00 - Saída do Desfile.'
    },
    {
        'title': 'Esclarecimento de Dúvidas',
        'description': 'Todas as dúvidas deverão ser colocadas por escrito e enviadas por correio eletrónico.<br></br>Caso sejam relacionadas com o Desfile da Mordomia deverão ser enviadas para o endereço <a class="primaryOrange opensansName" href="mailto:mordomia@vianafestas.com">mordomia@vianafestas.com</a><br></br>Caso sejam relacionadas com o Cortejo Etnográfico deverão ser enviadas para o endereço <a class="primaryOrange opensansName" href="mailto:cortejo@vianafestas.com">cortejo@vianafestas.com</a>'
    },
    {
        'title': 'Política de Privacidade',
        'description': 'Os dados pessoais que forem recolhidos no âmbito da plataforma, serão tratados com respeito pela legislação de proteção dos dados pessoais, nomeadamente a Lei n.º 67/98, de 26 de Outubro, e a Lei n.º 41/2004, de 18 de Agosto, bem como a partir de 25 de Maio de 2018, o RGPD - Regulamento Geral de Proteção de Dados (EU 2016\\679), sendo que a plataforma em causa pressupõe o conhecimento e aceitação das seguintes condições:<ol type="a"><li>Os membros da plataforma aceitam que o fornecimento dos dados é necessário e obrigatório para efeitos de processamento das inscrições no Desfile de Mordomia, apuramento dos participantes e realização do Desfile da Mordomia. Os dados serão recolhidos e tratados pela entidade promotora, VIANAFESTAS;</li><li> A VIANAFESTAS, enquanto promotora do evento “Romaria de Nossa Senhora d\'Agonia”, através da respetiva Comissão de Festas, garante a segurança e confidencialidade do tratamento, garantindo a possibilidade de acesso, retificação e cancelamento dos dados aos participantes que assim o desejem e o comuniquem, através do correio eletrónico, para <a class="primaryOrange opensansName" href="mailto:vianafestas@vianafestas.com">vianafestas@vianafestas.com</a>;</li><li> Os dados de identificação pessoal obtidos poderão ser disponibilizados para o apuramento de responsabilidade civil e criminal, mediante solicitação da autoridade judiciária competente, nos termos da legislação aplicável. </li></ol>'
    }
]
})

// Methods/Functions
const goBack = () => {
    emit('goBack');
}
</script>

<template>
  <el-card>
    <div class="logo-container">
      <img class="logoWidth" src="/uploads/viana-festas-logo.png">
    </div>
    <h1>Perguntas Frequentes</h1>
    <el-collapse v-model="activeNames">
      <el-collapse-item class="collapseClass" v-for="(faq, index) in faqs" :key="index" :name="index">
          <template #title>
              <h2>{{ faq.title }}</h2>
          </template>
          <div class="descriptionClass opensansName" v-html="faq.description">
          </div>
      </el-collapse-item>
    </el-collapse>
    <div class="btnSelect">
      <el-button
          type=""
          size="large"
          @click="goBack"
      >
          Voltar
      </el-button>
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
    margin-top: 20px !important;
    text-align: center;
}

.descriptionClass {
    max-width: 650px;
    width: 100%;
    overflow-x: auto;
    padding: 10px;
    box-sizing: border-box;
}
</style>
<style>
.collapseClass>button {
  height: 100% !important;
}
.startMain-container{
  min-height: 100% !important;
}
.startMobile-container{
  min-height: 100% !important;
}
</style>