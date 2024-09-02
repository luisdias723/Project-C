/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/Layouts/AppLayout';

const adminRoutes = [
      // {
      //       path: '/formularios',
      //       component: Layout,
      //       redirect: 'formularios',
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
      //         {
      //             path: 'tipo_questoes',
      //             component: () => import('@/Pages/Formularios/Questions/tipo_questoes.vue'),
      //             name: 'Tipo de Questões',
      //             meta: { title: 'Questões', icon: 'fa-solid fa-chart-line', roles: ['admin', 'gestor'], noCache: false },
      //         },
      //       ],
      //     },
//   {
//     path: '/utilizadores',
//     component: Layout,
//     redirect: '/utilizadores',
//     name: 'Utilizadores',
//     alwaysShow: true,
//     meta: {
//       title: 'Utilizadores',
//       icon: 'fa-solid fa-user',
//       roles: ['admin', 'gestor']
//     },
//     children: [
//       {
//         path: '/administrador/utilizadores/perfil/:id(\\d+)',
//         component: () => import('@/Pages/users/profile'),
//         name: 'Perfil',
//         meta: { title: 'Perfil', icon: 'fa-solid fa-person', noCache: false, roles: ['admin', 'gestor'] },
//         hidden: true,
//       },
//       {
//         path: 'utilizadores',
//         component: () => import('@/Pages/users/list'),
//         name: 'Utilizadores',
//         meta: { title: 'managers', icon: 'fa-solid fa-user',  roles: ['admin'] },
//       },
//       {
//         path: '/clientes/list',
//         component: () => import('@/Pages/clients/List'),
//         name: 'Clientes',
//         meta: { title: 'Clients', icon: 'fa-solid fa-person', noCache: false, roles: ['admin', 'gestor'] },
//       },
//       {
//         path: '/clientes/perfil/:id(\\d+)',
//         component: () => import('@/Pages/clients/Profile'),
//         name: 'Perfil de Cliente',
//         meta: { title: 'Perfil', icon: 'fa-solid fa-person', noCache: false, roles: ['admin', 'gestor'] },
//         hidden: true,
//       },
//       {
//         path: '/coaches/lista',
//         component: () => import('@/Pages/doctors/List'),
//         name: 'Coaches',
//         meta: { title: 'doctors', icon: 'fa-solid fa-user-doctor', noCache: false, roles: ['admin', 'gestor'] },
//       },
//       {
//         path: '/coaches/perfil/:id(\\d+)',
//         component: () => import('@/Pages/doctors/Profile'),
//         name: 'Perfil de Coach',
//         meta: { title: 'Perfil', icon: 'fa-solid fa-user-doctor-hair', noCache: false, roles: ['admin', 'gestor', 'terapeuta'] },
//         hidden: true,
//       },
//     ]
//   },
//   {
//     path: '/administrador',
//     component: Layout,
//     redirect: '/administrador/users',
//     name: 'Sistema',
//     alwaysShow: true,
//     meta: {
//       title: 'System',
//       icon: 'fa-solid fa-gear',
//       roles: ['admin', 'gestor']
//     },
//     children: [
      /** User managements */
      // {
      //   path: 'users/edit/:id(\\d+)',
      //   component: () => import('@/Pages/Profile/Show'),
      //   name: 'UserProfile',
      //   meta: { title: 'userProfile', noCache: true, permissions: ['manage user'] },
      //   hidden: true,
      // },
      // {
      //   path: '/multimedia',
      //   component: () => import('@/Pages/multimedia/listMultimedia'),
      //   name: 'Multimedia',
      //   meta: { title: 'multimedia', icon: 'fa-solid fa-file', noCache: false, roles: ['admin', 'gestor', 'terapeuta'] },
      // },
      // {
      //   path: '/formulario/registo',
      //   component: () => import('@/Pages/RegisterForm/index.vue'),
      //   name: 'Formulário de registo',
      //   meta: { title: 'register_form', icon: 'fa-solid fa-calendar', noCache: false, roles: ['admin', 'gestor'] },
      // },
      // {
      //   path: '/formulario/avaliacao',
      //   component: () => import('@/Pages/RatingForm/index.vue'),
      //   name: 'Formulário de avaliação',
      //   meta: { title: 'rating_form', icon: 'fa-solid fa-star', noCache: false, roles: ['admin', 'gestor'] },
      // },
      // {
      //   path: '/configuracoes',
      //   component: () => import('@/Pages/config/settings.vue'),
      //   name: 'Configurações',
      //   meta: { title: 'Settings', icon: 'fa-solid fa-gear', noCache: false, roles: ['admin', 'gestor'] },
      // },
      // {
      //   path: 'perfil',
      //   component: () => import('@/views/users/SelfProfile'),
      //   name: 'Perfil',
      //   meta: { title: 'Perfil', icon: 'fa-solid fa-user', noCache: true },
      // },
      // /** Role and permission */
      // {
      //   path: 'roles',
      //   component: () => import('@/views/role-permission/List'),
      //   name: 'RoleList',
      //   meta: { title: 'rolePermission', icon: 'fa-solid fa-briefcase', permissions: ['manage permission'] },
      // },
      // {
      //   path: 'settings',
      //   component: () => import('@/views/admin-settings/index'),
      //   name: 'Definições',
      //   meta: { title: 'Definições', icon: 'fa-solid fa-screwdriver-wrench', permissions: ['manage permission'] },
      // },
//     ],
//   }
];

export default adminRoutes;
