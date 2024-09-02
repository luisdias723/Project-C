<template>
  <aside aria-label="Sidebar">
    <hamburger v-if="smallScreen()" id="hamburger-container" :is-active="sidebar.opened" class="hamburger-container" @toggleClick="toggleSideBar" />
    <!-- Primary Navigation Menu -->
    <div class="drawer-bg">
      <!-- Logo -->
      <div class="logo-container">
        <el-link href="/">
          <!-- <img v-if="isCollapse" src="/uploads/logo-iup.png" lazy>
          <img v-else src="/uploads/logo-iup.png" lazy> -->
          <el-image src="/uploads/viana-festas-logo.png" />
        </el-link>
      </div>
      <!-- Navigation Links -->
      <el-scrollbar wrap-class="scrollbar-wrapper">
        <el-menu
          :show-timeout="200"
          :default-active="$route.path"
          :collapse="isCollapse"
          mode="vertical"
          background-color="#FFFFFF"
          text-color="#304156"
          active-text-color="#409EFF"
        >
          <sidebar-item v-for="route in rota"  :key="route.path" :item="route" :base-path="route.path" :collapse="isCollapse" />
         
        </el-menu>
 
      </el-scrollbar>

    </div>
    
    
  </aside>
</template>


<script>
import { defineComponent } from 'vue';
import { mapGetters } from 'vuex';
import SidebarItem from './SidebarItem.vue';
import Hamburger from '../Hamburguer';


export default defineComponent({
  name: 'SidebarModule',
  components: {
    SidebarItem,
    Hamburger,
  }, 

  data(){

    return{
    aux:'',
    manualRoutes:[],
    normalRoutes:[],
    rota:'',
    // this.$store.state.permission.routes
   }
  },

  created(){
       this.getRoutes();
       this.enter();
  },
  watch:{
       $route(to, from){
       var test= window.location.href;
          var test2= test.includes('userManual');
         
   
     // console.log(this.manualRoutes[0].isManual)


     if(test2){
    // console.log('ola' + this.manualRoutes[0].isManual);
      this.finalArray(this.manualRoutes)
     }
     else{
      this.finalArray(this.normalRoutes)
     }


       }
       


  },

  computed: {
    ...mapGetters([
      'sidebar',
      'permissionRoutes',
    ]),
    routes() {
      
      
return this.$store.state.permission.routes;


    },
    isCollapse() {
      return !this.sidebar.opened;
    },
  },
  methods: {

    enter(){
      var test= window.location.href;
          var test2= test.includes('userManual');

     if(test2){
    // console.log('ola' + this.manualRoutes[0].isManual);
      this.finalArray(this.manualRoutes)
     }
     else{
      this.finalArray(this.normalRoutes)
     }

    

    },
     getRoutes(){

      this.aux=this.$store.state.permission.routes
     

     this.aux.forEach(this.pushArrays)



//console.log(this.$store.state.permission.routes);

     },

     pushArrays(item, index){
      if(item.isManual){
        this.manualRoutes.push(item)
      }
      else{
        this.normalRoutes.push(item)
      }

     },

     finalArray(array){
      this.rota= array;

      return this.rota;


     },
    toggleSideBar() {
      this.$store.dispatch('app/toggleSideBar');
    },
    handleFocusOut() {
      this.$store.dispatch('app/toggleSideBar');
    },
    smallScreen(){
      if( screen.width <= 1000 ) {
        return true;
      }
      else {
        return false;
      }
    }
  }
});
</script>

<style lang="scss">

    .openSidebar .logo-container {
        padding: 20px;
        text-align: center
    }

    .hideSidebar {

        .logo-container {
            padding: 20px 0px;
            text-align: center;

            .el-image {
                width: 40px;
                height: auto;
            }
        }
    }

    aside{
        background: white;
        height: 400px;

        ul{
            list-style: none;
            padding: 0;
        }
    }

    .drawer-bg {
      position: absolute;
        top: 0;
        width: 100%;
        height: calc(100% - 210px);
        z-index: 999;
    }

    
</style>