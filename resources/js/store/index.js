import getters from './getters';
import { createStore } from 'vuex';
import camelCase from 'camelcase';

// // https://webpack.js.org/guides/dependency-management/#requirecontext
const modulesFiles = require.context('./modules', false, /\.js$/);

// // you do not need `import app from './modules/app'`
// // it will auto require all vuex module from modules file
const modules = modulesFiles.keys().reduce((modules, modulePath) => {
//   // set './app.js' => 'app'
  const moduleName = camelCase(modulePath.replace(/^\.\/(.*)\.\w+$/, '$1'));
  const value = modulesFiles(modulePath);
  modules[moduleName] = value.default;
  return modules;
}, {});

export default createStore({
  modules: modules,
  getters: getters,
});
