<template>
  <div class="errPage-container">
    <el-button icon="arrow-left" class="pan-back-btn" @click="back">
      {{ $t('common.back') }}
    </el-button>
    <el-row>
      <el-col :span="12">
        <h1 class="text-jumbo text-ginormous">
          Oops!
        </h1>
        <h2>Não possui permissões para entrar nesta página</h2>
        <h6>Se não se encontra satisfeito, contacte o administrador</h6>
        <ul class="list-unstyled">
          <li>Ou pode voltar para:</li>
          <li class="link-type">
            <router-link to="/dashboard">
              {{ $t('route.dashboard') }}
            </router-link>
          </li>
        </ul>
      </el-col>
      <el-col :span="12">
        <img :src="errGif" width="313" height="428" alt="401 Sem permissão">
      </el-col>
    </el-row>
  </div>
</template>

<script>
// import errGif from '@/assets/401_images/401.gif';
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'Page401',
  data() {
    return {
    //   errGif: errGif + '?' + +new Date(),
      errGif: '',
    };
  },
  methods: {
    back() {
      if (this.$route.query.noGoBack) {
        this.$router.push({ path: '/dashboard' });
      } else {
        this.$router.go(-1);
      }
    },
  },
});
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.pic-404__parent{
  width: 70%;
}
  .errPage-container {
    width: 800px;
    max-width: 100%;
    margin: 100px auto;
    .pan-back-btn {
      background: #008489;
      color: #fff;
      border: none!important;
    }
    .pan-gif {
      margin: 0 auto;
      display: block;
    }
    .pan-img {
      display: block;
      margin: 0 auto;
      width: 100%;
    }
    .text-jumbo {
      font-size: 60px;
      font-weight: 700;
      color: #484848;
    }
    .list-unstyled {
      font-size: 14px;
      li {
        padding-bottom: 5px;
      }
      a {
        color: #008489;
        text-decoration: none;
        &:hover {
          text-decoration: underline;
        }
      }
    }
  }
</style>
