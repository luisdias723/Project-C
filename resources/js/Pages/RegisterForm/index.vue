<script setup>
import { ref, onMounted, onBeforeMount } from 'vue';

import InscricoesComponent from './Components/inscricoes.vue';
import EditInscricoesComponent from './Components/editInscricoes.vue';
import EditCortejoComponent from './Components/editCortejo.vue';
import CortejoComponent from './Components/cortejo.vue';
import FaqsComponent from './Components/faqs.vue';

import { isMobile } from '@/filters/index';

import { useRoute } from 'vue-router';

import FormResource  from '@/api/formulario';

const formResource = new FormResource();

let loading = ref(false);
let displayInscricoes = ref(false);
let displayEditInscricoes = ref(false);
let displayEditCortejo = ref(false);
let displayCortejo = ref(false);
let checklist = ref(null)
let faqs = ref(false);
let mobile = ref(false);

const route = useRoute();

let query = ref(null);

let canRegister = ref(false);

let active = ref({});

let loadingActive = ref(false);

onBeforeMount(() => {
  getActive()
  // displayInscricoesMethod()
  let route_query = route.query;
  if (route_query && route_query.id) {
    query.value = route_query.id;
    displayEditInscricoesMethod();
  }
  if (route_query && route_query.cortejo) {
    query.value = route_query.cortejo;
    displayEditCortejoMethod();
  }
  getDesfileCount();
});


// Remover este método daqui
onMounted(() => {
    // O isMobile retorna se o dispositivo é mobile ou não para aplicar o css
    mobile.value = isMobile();
})

const getDesfileCount = () => {
  formResource.getCount().then(data => {
      if (data) {
        canRegister.value = data;
      }
  }).catch(error => {
      console.log(error);
  });
}

const getActive = () => {
  loadingActive.value = true;
  formResource.getActive().then(data => {
    if (data) {
      loadingActive.value = false;
      active.value = data;
    }
  }).catch(error => {
      loadingActive.value = false;
      console.log(error);
  });
}

function goBack() {
  console.log('A fazer goback');
  displayInscricoes.value = false;
  displayCortejo.value = false;
  displayEditInscricoes.value = false;
  displayEditCortejo.value = false;
  checklist.value = null;
  faqs.value = false;
}

const displayInscricoesMethod = () => {
  displayInscricoes.value = true;
  displayCortejo.value = false;
  displayEditInscricoes.value = false;
  displayEditCortejo.value = false;
  checklist.value = 1;
}

const displayEditInscricoesMethod = () => {
  displayEditInscricoes.value = true;
  displayInscricoes.value = false;
  displayCortejo.value = false;
  displayEditCortejo.value = false;
  checklist.value = 1;
}

const displayEditCortejoMethod = () => {
  displayEditInscricoes.value = false;
  displayInscricoes.value = false;
  displayCortejo.value = false;
  displayEditCortejo.value = true;
  checklist.value = 2;
}

const displayCortejoMethod = () => {
  displayInscricoes.value = false;
  displayCortejo.value = true;
  displayEditInscricoes.value = false;
  displayEditCortejo.value = false;
  checklist.value = 2;

}

</script>

<template>
    <div :class="[ mobile ? 'startMobile-container' : 'startMain-container' ]">
      <el-card v-if="!displayInscricoes && !displayCortejo && !displayEditInscricoes && !faqs && !displayEditCortejo">
        <h1 class="mainTitle">Inscrições Romaria d'Agonia</h1>    
        <div class="logo-container">
          <img class="logoWidth" src="/uploads/viana-festas-logo.png">
        </div>
        <div class="mainBody">
          <p class="opensansName">O Desfile da Mordomia decorrerá no dia <b>16 de Agosto de 2024</b>.</p>
          <p class="opensansName">O Cortejo Etnográfico decorrerá no dia <b>17 de Agosto de 2024</b>.</p>
          <p class="opensansName">Caso possua alguma dúvida poderá consultar as <el-button
            type=""
            class="primaryOrange opensansName faqs"
            size="large" 
            link
            @click="faqs = true"
          >
            Perguntas Frequentes
          </el-button></p>
        </div>
        <div class="eventSelect">
          <h2>Qual o evento pretendido?</h2>
        </div>
        <div :class="[ mobile ? 'btnSelectMobile' : 'btnSelect' ]">
          <div :class="[ mobile ? 'button-wrapper' : 'button-wrapper marginLeft' ]">
            <el-button
              class="eventBtn"
              :type="active[1] ? 'primary' : 'danger'"
              size="large" 
              :loading="loading || loadingActive"
              @click="displayInscricoesMethod"
            >
              <div class="button-content">
                <span>Desfile da Mordomia</span>
              </div>
            </el-button>
            <div class="button-date opensansName">5 de Julho a 21 de Julho</div>
          </div>
          <div :class="[ mobile ? 'button-wrapper' : 'button-wrapper marginLeft' ]">
            <el-button
              class="eventBtn"
              :type="active[2] ? 'primary' : 'danger'"
              size="large" 
              :loading="loading || loadingActive"
              @click="displayCortejoMethod"
            >
              <div class="button-content">
                <span>Cortejo Etnográfico</span>
              </div>
            </el-button>
            <div class="button-date opensansName">22 de Julho a 5 de Agosto</div>
          </div>
        </div>
      </el-card>
      <InscricoesComponent v-if="displayInscricoes && !displayCortejo && !displayEditInscricoes && !displayEditCortejo" :checklist="checklist" @goBack="goBack" :mobile="mobile" :register="canRegister" :active="active[1]"></InscricoesComponent>
      <EditInscricoesComponent v-if="!displayInscricoes && !displayCortejo && displayEditInscricoes && !displayEditCortejo" :register="canRegister" :query="query" :checklist="checklist"  @goBack="goBack" :mobile="mobile" :active="active[1]"></EditInscricoesComponent>
      <CortejoComponent v-if="!displayEditInscricoes && displayCortejo && !displayInscricoes && !displayEditCortejo" :checklist="checklist" @goBack="goBack" :mobile="mobile" :active="active[2]"></CortejoComponent>
      <EditCortejoComponent v-if="!displayInscricoes && !displayCortejo && !displayEditInscricoes && displayEditCortejo" :register="canRegister" :query="query" :checklist="checklist"  @goBack="goBack" :mobile="mobile" :active="active[2]"></EditCortejoComponent>
      <FaqsComponent v-if="faqs" @goBack="goBack" :mobile="mobile"></FaqsComponent>
      <!-- <FormComponent v-else @goBack="goBack"></FormComponent> -->
    </div>
    <footer class="footerContainer">
      <div class="containerFooter">
        <p class="opensansName footerFont">© 2024 Viana Festas. Todos os direitos reservados. Omelette made by <a class="hovo-copyright primaryOrange" href="https://www.hovo.pt" target="_blank">HOVO!</a></p>
      </div>
    </footer>
    <div>
    </div>
</template>

<style scoped>
  .startMain-container{
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100%;
    background: rgb(238, 239, 240);
    background: linear-gradient(180deg, #ebe6e4 0%, #faf4f2 100%);
    padding-bottom: 10px;
    min-height: 95% !important;
  }

  .startMobile-container{
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100%;
    background: rgb(238, 239, 240);
    background: linear-gradient(180deg, #ebe6e4 0%, #faf4f2 100%);
    padding-bottom: 10px;
    min-height: 95% !important;
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
      max-width: 50rem;
      padding: 15px 25px;
    }
  }
  
  .logo-container {
    background: white; 
    text-align: center;
    width: 100%;
    margin: -20px 0 20px;
  }

  .eventSelect{
    text-align: center !important;
  }

  /* .btnSelect{
    margin-bottom: 10px !important;
    text-align: center !important;
  }
  .btnSelect > button {
    width: 200px !important;
  }

  .btnSelectMobile{
    display: block;
    margin-bottom: 10px !important;
    text-align: center !important;
  }
  .btnSelectMobile > button {
    width: 200px !important;
    margin-left: 0px !important;
    margin-bottom: 10px !important;
  } */

  .mainBody {
    max-width: 90% !important;
    text-align: center !important;
    margin: auto !important;
  }

  .faqs {
    font-weight: bold !important;
  }

  .footerFont {
    font-size: 12px !important;
  }
</style>
<style>
.btnSelect, .btnSelectMobile {
  margin-bottom: 10px !important;
  text-align: center !important;
}

.btnSelect > .button-wrapper, .btnSelectMobile > .button-wrapper {
  display: inline-block;
  margin-bottom: 10px !important;
  text-align: center !important;
}

.btnSelectMobile > .button-wrapper {
  display: block;
}

.btnSelect > .button-wrapper > el-button, .btnSelectMobile > .button-wrapper > el-button {
  width: 200px !important;
}

.button-date {
  margin-top: 5px;
  font-size: 14px;
  color: #555;
}

.button-wrapper.marginLeft {
  margin-left: 30px !important;
}

.eventBtn {
  min-width: 220px !important;
  font-size: 18px !important;
}
</style>