const getters = {
  addRoutes(state) { return state.permission.addRoutes; },
  device(state) { return state.app.device; },
  name(state)  {return state.user.name; },
  sidebar(state) { return state.app.sidebar; },
  userId(state) { return state.user.id; },
  permissions(state) { return state.user.permissions; },
  permissionRoutes(state) { return state.permission.routes; },
  roles(state) { return state.user.roles; },
  token(state) { return state.user.token; },
  registered(state) { return state.user.register_completed; },
  avatar(state) { return state.user.avatar; },
  countries(state) { return state.countries; },
  settings(state) { return state.settings; },
  progress(state) { return state.progress.progress; },
  getSettingByName: (state) => (name) => { return state.settings.settings[name]; },
  testModeEnabled: (state) => () => { return state.settings.settings.test_mode; },
};
export default getters;
