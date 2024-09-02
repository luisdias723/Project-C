<script setup>
import { ref, onMounted } from 'vue';
import { User } from '@element-plus/icons-vue';

import RegistrationResource from '@/api/registration';

const regiResource = new RegistrationResource();
let trajes = ref({});

let loading_opc = ref(false);

onMounted(() => {
    getList()
})

const getList = () => {
  regiResource.getTrajeCounter().then(response => {
      if (response && Object.keys(response).length > 0) trajes.value = response;
  }).catch(error => {
      console.log(error);
  });
}

const getClass = (idx) => {
    return {
    iconSize: true,
    acceptedIcon: idx % 2 !== 0, // true se idx é ímpar, false se par
    processingIcon: idx % 2 === 0 // true se idx é par, false se ímpar
    };
};

const getPdf = () => {
  loading_opc.value = true;
  regiResource.getEstatistica().then(response => {
      console.log(response);
      // if (response && Object.keys(response).length > 0) trajes.value = response;
  }).catch(error => {
      console.log(error);
  });
}

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <el-row>
          <el-col :span="24">
            <el-button class="primaryRo addFilterMargin" v-loading="loading_opc" type="primary" @click="getPdf">
              <el-icon class="el-icon--left"><View /></el-icon>
              Exportar Estatística
            </el-button>
          </el-col>
        </el-row>
        <el-row :gutter="5">
            <el-col v-for="(traje, idx_traje) in trajes" :key="idx_traje" style="margin-bottom: 5px" :span="8">
                <el-card>
                    <el-row>
                        <el-col :span="12">
                            <User :class="getClass(idx_traje)"/>
                        </el-col>
                        <el-col :span="12" class="cardText">
                            <h3>{{ traje.description }}</h3>
                            <vue3-autocounter ref='counter' :startAmount='0' :endAmount="traje.count" :duration='3' separator=',' :autoinit='true'/>
                        </el-col>
                    </el-row>
                </el-card>
            </el-col>
        </el-row>
    </el-card>
</template>

<style scoped>
  .iconSize {
    width: 100px;
  }
  .rejectedIcon {
    color: #F4858E
  }
  .processingIcon {
    color: #FFDA91
  }
  .acceptedIcon {
    color: #7AB68E
  }
  .filterData {
    width: 100% !important;
    margin: 20px 0px !important;
    text-align: right !important;
  }
  .opensansName.el-tag__content {
    color: #FFDA91 !important;
  }
  .addFilterMargin {
    margin: 20px 0px !important;
  }
</style>