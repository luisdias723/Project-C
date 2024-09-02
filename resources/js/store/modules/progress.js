const state = {
  progress: 0,
};

const mutations = {
  SET_PROGRESS: (state, progress) => {
    state.progress = progress;
  },
};

const actions = {
  setProgress({ commit }, progress) {
    commit('SET_PROGRESS', progress);
  },
};

export default {
  state,
  mutations,
  actions,
};