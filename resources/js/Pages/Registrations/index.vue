<script setup>
import { ref, onMounted } from 'vue';
import { CircleCloseFilled, WarningFilled, SuccessFilled, EditPen, Delete, View } from '@element-plus/icons-vue';

import { useRouter } from 'vue-router';

import Resource from '@/api/resource';
import RegistrationResource from '@/api/registration';

const emit = defineEmits(['set-header-color']);

const registrationsResource = new Resource('registrations');
const regiResource = new RegistrationResource();

let loading = ref(false);
let title = ref('');
let registrationData = ref([]);
let formData = ref([]);
let dialogVisible = ref(false);
let query = ref({
    total: 0,
    page: 1,
    keyword: '',
    status: null,
    limit: 25
});

let rejected = ref(0);
let ongoing = ref(0);
let accepted = ref(0);

let loading_opc = false;

let options = ref([]);

let trajes = ref([]);

let countries = ref([]);

let freguesias = ref([]);

const router = useRouter();

onMounted(() => {
    emit('set-header-color', '#7AB68E');
    getList();
    getCounters();
    getStatus();
    getFilters();
    document.addEventListener('visibilitychange', handleVisibilityChange);
})

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    getList();
  }
}

const getList = () => {
    loading.value = true;
    query.value.form_id = 1;
    registrationsResource.list(query.value).then(response => {
        if (response.data) registrationData.value = response.data;
        if (response.meta && response.meta.total) query.value.total = response.meta.total;
        loading.value = false;
    }).catch(error => {
        loading.value = false;
        console.log(error);
    });
}

const getCounters = () => {
    regiResource.getCounter({form: 1}).then(response => {
        if (response) {
            accepted.value = response.accepted;
            ongoing.value = response.ongoing;
            rejected.value = response.rejected;
        }
    }).catch(error => {
        console.log(error);
    });
}

const getStatus = () => {
    regiResource.getStatus().then(response => {
        if (response.data && response.data.length > 0) {
            options.value = response.data;
            query.value.status = response.status;
        }
    }).catch(error => {
        console.log(error);
    });
}

const getFilters = () => {
    regiResource.getFilters().then(response => {
        if (response) {
            trajes.value = response.trajes;
            freguesias.value = response.freguesias;
            countries.value = response.paises;
        }
    }).catch(error => {
        console.log(error);
    });
}

const getTagType = (status_id) => {
    switch (status_id) {
        case 3:
          return 'success';
        case 2:
          return 'danger';
        default:
          return 'warning';
    }
}

const goToDetails = (id) => {
    // router.push(`desfile/detalhes/${id}`);
    const route = router.resolve({ path: `desfile/detalhes/${id}` });
    // Abra a URL em uma nova aba
    window.open(route.href, '_blank');
};

const goToTrajes = () => {
    // router.push(`desfile/detalhes/${id}`);
    const route = router.resolve({ path: `desfile/trajes` });
    // Abra a URL em uma nova aba
    window.open(route.href, '_blank');
};

const deleteRegistration = (valueToUpdate) => {
    loading_opc = true;
    valueToUpdate.active = !valueToUpdate.active;
    registrationsResource.update(valueToUpdate.id, valueToUpdate).then(response => {
        loading_opc = false;
        if (response) {
          ElMessage({
            message: 'Inscrição removida com sucesso',
            type: 'success',
            duration: 5000
          })
          getList()
        } else {
          ElMessage({
            message: 'Não foi possível remover a inscrição',
            type: 'error',
            duration: 5000
          })
        }
    }).catch(error => {
        loading_opc = false;
        console.log(error);
    });
};

const handleRowClick = (row) => {
    const route = router.resolve({ path: `desfile/detalhes/${row.id}` });
    // Abra a URL em uma nova aba
    window.open(route.href, '_blank');
}

</script>

<template>
    <!-- Card to display all questions -->
    <el-card>
        <el-row>
            <el-col :span="8">
                <el-card>
                    <el-row>
                        <el-col :span="12">
                            <CircleCloseFilled class="iconSize rejectedIcon"/>
                        </el-col>
                        <el-col :span="12" class="cardText">
                            <h3>Rejeitadas</h3>
                            <vue3-autocounter ref='counter' :startAmount='0' :endAmount="rejected" :duration='3' separator=',' :autoinit='true'/>
                        </el-col>
                    </el-row>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card>
                    <el-row>
                        <el-col :span="12">
                            <WarningFilled class="iconSize processingIcon"/>
                        </el-col>
                        <el-col :span="12" class="cardText">
                            <h3>Pendentes</h3>
                            <vue3-autocounter ref='counter' :startAmount='0' :endAmount="ongoing" :duration='3' separator=',' :autoinit='true'/>
                        </el-col>
                    </el-row>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card>
                    <el-row>
                        <el-col :span="12">
                            <SuccessFilled class="iconSize acceptedIcon"/>
                        </el-col>
                        <el-col :span="12" class="cardText">
                            <h3>Aceites</h3>
                            <vue3-autocounter ref='counter' :startAmount='0' :endAmount='accepted' :duration='3' separator=',' :autoinit='true'/>
                        </el-col>
                    </el-row>
                </el-card>
            </el-col>
        </el-row>
        <el-row>
            <!-- <el-col :span="0">
            </el-col> -->
            <el-col :span="24" class="filterData">
                <el-button class="primaryRo addFilterMargin" v-loading="loading_opc" type="primary" @click="goToTrajes">
                    <el-icon class="el-icon--left"><View /></el-icon>
                    Inscrições por traje
                </el-button>
                <el-select
                    v-model="query.country"
                    placeholder="País"
                    style="width: 200px;"
                    filterable
                    @change="getList"
                    >
                    <el-option
                        :key="0"
                        label="Todos"
                        :value="0"
                    />
                    <el-option
                        v-for="item in countries"
                        :key="item.id"
                        :label="item.description"
                        :value="item.id"
                    />
                </el-select>
                <el-select
                    v-model="query.freguesia"
                    placeholder="Freguesia"
                    style="width: 200px; margin-left: 10px"
                    filterable
                    @change="getList"
                    >
                    <el-option
                        :key="0"
                        label="Todas"
                        :value="0"
                    />
                    <el-option
                        v-for="item in freguesias"
                        :key="item.id"
                        :label="item.description"
                        :value="item.id"
                    />
                </el-select>
                <el-select
                    v-model="query.traje"
                    placeholder="Traje"
                    style="width: 200px; margin-left: 10px"
                    filterable
                    @change="getList"
                    >
                    <el-option
                        :key="0"
                        label="Todos"
                        :value="0"
                    />
                    <el-option
                        v-for="item in trajes"
                        :key="item.id"
                        :label="item.description"
                        :value="item.id"
                    />
                </el-select>
                <el-select
                    v-model="query.status"
                    placeholder="Selecionar"
                    style="width: 200px; margin-left: 10px"
                    filterable
                    @change="getList"
                    >
                    <el-option
                        :key="0"
                        label="Todos"
                        :value="0"
                    />
                    <el-option
                        v-for="item in options"
                        :key="item.id"
                        :label="item.description"
                        :value="item.id"
                    />
                </el-select>
                <el-input style="width: 200px; margin-left: 10px" v-model="query.keyword" placeholder="Palavra-chave (enter)" @keyup.enter="getList" />
            </el-col>
        </el-row>
        <el-table v-loading="loading" :data="registrationData" style="width: 100%" @row-click="handleRowClick" class="registrationTable">
            <el-table-column prop="code" label="Código" width="180" />
            <el-table-column prop="name" label="Participante">
                <template #default="scope">
                    <span class="opensansName">
                        {{ scope.row.name }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="status_des" label="Estado">
                <template #default="scope">
                    <el-tag class="opensansName" :type="getTagType(scope.row.status_id)">
                    {{ scope.row.status_des }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="updated_at" label="Dt Registo">
                <template #default="scope">
                    <span class="opensansName">
                        {{ scope.row.updated_at }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column label="Ações" width="360">
                <template #default="scope">
                    <el-button class="primaryOrange" v-loading="loading_opc" type="primary" size="small" @click="goToDetails(scope.row.id)">
                        <el-icon class="el-icon--left"><EditPen /></el-icon>
                        Editar
                    </el-button>
                    <!-- <el-button v-loading="loading_opc" type="danger" size="small" @click="deleteRegistration(scope.row)">
                        <el-icon class="el-icon--left"><Delete /></el-icon>
                        Apagar
                    </el-button> -->
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
            v-if="query.total > query.limit"
            v-model:current-page="query.page"
            v-model:page-size="query.limit"
            :page-sizes="[10, 25, 50]"
            layout="sizes, prev, pager, next"
            :total="query.total"
            :background="true"
            @size-change="getList"
            @current-change="getList"
        />
    </el-card>
    <!-- Dialog to add or edit Checklists -->
    <!-- <el-dialog
        v-model="dialogVisible"
        :title="title"
        :before-close="handleCloseModal"
    >
    <el-form :model="checklist" label-width="auto" style="max-width: 600px">
        <el-form-item label="Nome da checklist">
            <el-input v-model="checklist.name" />
        </el-form-item>
        <el-divider content-position="left">Questões</el-divider>
        <el-form-item label="Perguntas">
            <div v-for="(question, index) in checklist.questions" :key="index">
                <el-input v-model="question.name" />
            </div>
            <el-button @click="addQuestion">Adicionar questão</el-button>
        </el-form-item>

    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleCloseModal">Cancel</el-button>
        <el-button type="primary" @click="handleCloseModal">
          Confirm
        </el-button>
      </div>
    </template>
  </el-dialog> -->
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
    margin-right: 10px !important;
  }
</style>
<style>
.registrationTable .el-table__row:hover {
  cursor: pointer !important;
}
</style>