import { getSettings } from '@/api/settings';

const state = {
  settings: [],
};

const mutations = {
  SET_SETTINGS: (state, settings) => {
    state.settings = settings;
  },
};

const actions = {
  // get user info
  getInfo({ commit }) {
    return new Promise((resolve, reject) => {
      // getSettings()
      //   .then(response => {
      //     const { data } = response;
      //     if (!data) {
      //       reject('Verification failed, please Login again.');
      //     }

      //     // settings must be a non-empty array
      //     // if (!settings || settings.length <= 0) {
      //     //   reject('getInfo: roles must be a non-null array!');
      //     // }
      //     commit('SET_SETTINGS', data);
      //     resolve(data);
      //   })
      //   .catch(error => {
      //     reject(error);
      //   });
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
