<template>
    <div class="login-container">
        <el-row style="background: #EEF1F4; height: 100vh;">
      <el-col :md="24" >
        <el-card>
            <h1 class="title">IUP- Política de privacidade</h1>
           <el-scrollbar v-html="content" height="500px">      
          </el-scrollbar> 
        </el-card>
      </el-col>
    </el-row>
       
        <el-scrollbar v-html="content" height="500px">      
          </el-scrollbar> 
    </div>
</template>

<script>


    export default {
        name: 'PrivacyPolicy',
  data() {
    return  {
      content : 'A carregar',
    };
  },
  created(){
     this.getSettings();
  },
  methods: {
    async getSettings(){
      await this.$store.dispatch('settings/getInfo');
      this.content = this.$store.getters.getSettingByName('privacy_policy');
      
    },
  }
};
</script>

<style scoped>
    .login-container{
    display: flex;
    justify-content: center;

    background: #acb0b5;
}
h1 {
    text-align: center;
    font-size: 30px;
}

.el-row{
  width: 50%;
  margin: auto;

}

.terms{
  margin-bottom: 2px;
  margin-left: 3px;
}

</style>