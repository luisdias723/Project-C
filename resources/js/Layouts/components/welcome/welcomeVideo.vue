<template>
  <div>
    <el-dialog v-if="url" v-model="dialogVisible" title="Mensagem de boas vindas" :before-close="handleClose" :width="$filters.isMobile() ? '95%' : '50%'">
      <div class="dialog-content">
        <iframe
          width="100%"
          height="400px"
          :src="'https://www.youtube.com/embed/' + url"
          title="YouTube video player"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        />
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-checkbox v-model="hide_video" label="Não mostrar mais" size="large" />
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>

export default {
  name: 'WelcomeVideo',
  props: {
    url: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      dialogVisible: true,
      hide_video: false,
    };
  },
  methods: {
    handleClose(){
      const expire = new Date();
      expire.setTime(expire.getTime() + (9999*24*60*60*1000));

      document.cookie = 'welcome_video=true;' + expire + ';path=/';
      this.dialogVisible = false;
    },
  },  
};
</script>

<style scoped>

.welcome-header {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.el-dialog__body{
  padding-top: 0px;
}
</style>