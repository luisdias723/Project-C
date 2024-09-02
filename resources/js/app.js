import { createApp, Vue } from 'vue';
import Vue3Autocounter from 'vue3-autocounter';
import i18n from '../lang';

import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import App from './Pages/App';
import router from './router';
import store from './store';
import moment from 'moment';

import * as filters from './filters'; // global filters

import '@/permission';
import '@fortawesome/fontawesome-free/css/all.css';
import '@fortawesome/fontawesome-free/js/all.js';
// import { getToken } from '@/utils/auth'; // get token from cookie
import 'element-plus/theme-chalk/display.css';

import pt from 'element-plus/es/locale/lang/pt';


require('./bootstrap'); // permission control


const app = createApp(App)
  .use(store)
  .use(ElementPlus, { locale: pt })
  .use(router)
  .use(i18n)
  
  // register global utility filters.
  app.config.globalProperties.$filters = filters;
  
  // momentjs library
  app.config.globalProperties.$moment = moment;
  
  
  app.component('vue3-autocounter', Vue3Autocounter);

// register global utility filters.
// app.config.globalProperties.$filters = filters;
app.mount('#app');
