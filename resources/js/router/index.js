import { createRouter, createWebHashHistory  } from 'vue-router';

// /**
//  * Layzloading will create many files and slow on compiling, so best not to use lazyloading on devlopment.
//  * The syntax is lazyloading, but we convert to proper require() with babel-plugin-syntax-dynamic-import
//  * @see https://doc.laravue.dev/guide/advanced/lazy-loading.html
//  */


// /* Layout */
import Layout from '@/Layouts/AppLayout';

// /* Router for modules */
// import adminRoutes from './modules/admin';
// import errorRoutes from './modules/error';

// /**
//  * Sub-menu only appear when children.length>=1
//  * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
//  **/

// /**
// * hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
// * alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
// *                                if not set alwaysShow, only more than one route under the children
// *                                it will becomes nested mode, otherwise not show the root menu
// * redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
// * name:'router-name'             the name is used by <keep-alive> (must set!!!)
// * meta : {
//     roles: ['admin', 'editor']   Visible for these roles only
//     permissions: ['view menu zip', 'manage user'] Visible for these permissions only
//     title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
//     icon: 'svg-name'             the icon show in the sidebar
//     noCache: true                if true, the page will no be cached(default is false)
//     breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
//     affix: true                  if true, the tag will affix in the tags-view
//   }
// **/

export const constantRoutes = [
  {
    path: '/redirect',
    component: Layout,
    hidden: true,
    children: [
      {
        path: '/redirect/:path*',
        component: () => import('@/views/redirect/index.vue'),
      },
    ],
  },
  // {
  //   path: '',
  //   component: Layout,
  //   redirect: 'dashboard',
  //   children: [
  //     {
  //       path: 'dashboard',
  //       component: () => import('@/Pages/dashboard/index.vue'),
  //       name: 'Dashboard',
  //       meta: { title: 'Dashboard', icon: 'fa-solid fa-chart-line', noCache: false },
  //     },
  //   ],
  // },
  
  // This will replace the dashboard redirect to inscrições redirect
  // {
  //   path: '/backoffice',
  //   component: Layout,
  //   redirect: '/backoffice/inscricoes',
  //   meta: { title: 'Backoffice', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //   children: [
  //     {
  //       path: '/backoffice/inscricoes',
  //       component: Layout,
  //       meta: { title: 'Inscrições', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //       children: [
  //         {
  //           path: 'inscricoes',
  //           component: () => import('@/Pages/Registrations/index.vue'),
  //           name: 'Inscrições',
  //           meta: { title: 'Inscrições', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //         },
  //         {
  //           path: 'inscricoes/detalhes/:id(\\d+)',
  //           component: () => import('@/Pages/Registrations/edit.vue'),
  //           name: 'Detalhes Inscrição',
  //           meta: { title: 'Report Details', icon: 'fa-solid fa-file-chart-column', noCache: false, roles: ['admin', 'gestor'] },
  //           hidden: true,
  //         },
  //       ],
  //     },
  //     {
  //       path: '/backoffice/formularios',
  //       component: Layout,
  //       meta: { title: 'Formulários', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //       children: [
  //         {
  //           path: 'formularios',
  //           component: () => import('@/Pages/Formularios/index.vue'),
  //           name: 'Formulários',
  //           meta: { title: 'Formulários', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //         },
  //         {
  //             path: 'questoes',
  //             component: () => import('@/Pages/Formularios/Questions/index.vue'),
  //             name: 'Questões',
  //             meta: { title: 'Questões', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //         },
  //       ],
  //     },
  //     {
  //       path: '/backoffice/quadros',
  //       component: Layout,
  //       meta: { title: 'Quadros', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //       children: [
  //         {
  //           path: 'quadros',
  //           component: () => import('@/Pages/Quadros/index.vue'),
  //           name: 'Quadros',
  //           meta: { title: 'Quadros', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //         },
  //         {
  //             path: 'trajes',
  //             component: () => import('@/Pages/Quadros/trajes.vue'),
  //             name: 'Trajes',
  //             meta: { title: 'Trajes', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
  //         },
  //       ],
  //     },
  //   ],
  // },
  {
    path: '/backoffice',
    redirect: '/backoffice/desfile',
    component: Layout,
    meta: { title: 'Inscrições', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
    children: [
      {
        path: 'desfile',
        component: () => import('@/Pages/Registrations/index.vue'),
        name: 'Desfile',
        meta: { title: 'Desfile', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
      {
        path: 'desfile/detalhes/:id(\\d+)',
        component: () => import('@/Pages/Registrations/editInsc.vue'),
        name: 'Detalhes Mordomia',
        meta: { title: 'Detalhes da Mordomia', icon: 'fa-solid fa-file-chart-column', noCache: false, roles: ['admin', 'gestor'] },
        hidden: true
      },
      {
        path: 'desfile/trajes',
        component: () => import('@/Pages/Registrations/countTrajes.vue'),
        name: 'Inscrições por traje',
        meta: { title: 'Inscrições por traje', icon: 'fa-solid fa-file-chart-column', noCache: false, roles: ['admin', 'gestor'] },
        hidden: true,
      },
      {
        path: 'cortejo',
        component: () => import('@/Pages/Registrations/cortejo/index.vue'),
        name: 'Cortejo',
        meta: { title: 'Cortejo', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
      {
        path: 'cortejo/detalhes/:id(\\d+)',
        component: () => import('@/Pages/Registrations/cortejo/edit.vue'),
        name: 'Detalhes Cortejo',
        meta: { title: 'Detalhes da Cortejo', icon: 'fa-solid fa-file-chart-column', noCache: false, roles: ['admin', 'gestor'] },
        hidden: true,
      },
      // {
      //   path: 'cortejo/trajes',
      //   component: () => import('@/Pages/Registrations/countTrajes.vue'),
      //   name: 'Inscrições por traje',
      //   meta: { title: 'Inscrições por traje', icon: 'fa-solid fa-file-chart-column', noCache: false, roles: ['admin', 'gestor'] },
      //   hidden: true,
      // },
    ],
  },
  {
    path: '/backoffice/formularios',
    component: Layout,
    meta: { title: 'Formulários', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
    children: [
      {
        path: 'formularios',
        component: () => import('@/Pages/Formularios/index.vue'),
        name: 'Formulários',
        meta: { title: 'Formulários', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
      {
          path: 'questoes',
          component: () => import('@/Pages/Formularios/Questions/index.vue'),
          name: 'Questões',
          meta: { title: 'Questões', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
    ],
  },
  {
    path: '/backoffice/quadros',
    component: Layout,
    meta: { title: 'Quadros', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
    children: [
      {
        path: 'quadros',
        component: () => import('@/Pages/Quadros/index.vue'),
        name: 'Quadros',
        meta: { title: 'Quadros', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
      {
          path: 'trajes',
          component: () => import('@/Pages/Quadros/trajes.vue'),
          name: 'Trajes',
          meta: { title: 'Trajes', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      },
    ],
  },
  {
    path: '/login',
    component: () => import('@/Pages/Auth/Login.vue'),
    hidden: true,
  },
  {
    path: '/registar',
    component: () => import('@/Pages/RegisterForm/index.vue'),
    hidden: true,
  },
  {
    path: '/inscrever',
    component: () => import('@/Pages/RegisterForm/index.vue'),
    hidden: true,
  },
  {
    path: '/recuperar/password',
    component: () => import('@/Pages/Auth/ForgotPassword.vue'),
    hidden: true,
  },
  {
    path: '/password',
    component: () => import('@/Pages/Auth/RecoverPassword.vue'),

    hidden: true,
  },
  {
    path: '/registar',
    component: () => import('@/Pages/Auth/Register.vue'),
    hidden: true,
  },
  {
    path: '/auth-redirect',
    component: () => import('@/Pages/Auth/AuthRedirect'),
    hidden: true,
  },
  {
    path: '/404',
    redirect: { name: 'Page404' },
    component: () => import('@/views/error-page/404'),
    hidden: true,
  },
  {
    path: '/401',
    component: () => import('@/views/error-page/401'),
    hidden: true,
  },
];

export const asyncRoutes = [
  // ...adminRoutes,
  // errorRoutes,
  // { path: '/:catchAll(.*)', redirect: '/404', hidden: true },
];

const router = createRouter({
  // 4. Provide the history implementation to use. We are using the hash history for simplicity here.
  base: '',
  scrollBehavior: () => ({ y: 0 }),
  history: createWebHashHistory(),
  routes: constantRoutes, // short for `routes: routes`
});



// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter({
    // 4. Provide the history implementation to use. We are using the hash history for simplicity here.
    base: '',
    scrollBehavior: () => ({ y: 0 }),
    history: createWebHashHistory(),
    routes: constantRoutes, // short for `routes: routes`
  });
  router.matcher = newRouter.matcher; // reset router
}

export default router;