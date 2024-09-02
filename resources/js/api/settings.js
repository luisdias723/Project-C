import request from '@/utils/request';
import Resource from '@/api/resource';

class SettingsResource extends Resource {
  constructor() {
    super('settings');
  }

  updateForm(data) {
    return request({
      url: '/' + this.uri + '/update/settings',
      method: 'post',
      data: data
    });
  }

  updateSetting(data) {
    return request({
      url: '/' + this.uri + '/update/setting',
      method: 'post',
      data: data
    });
  }

  checkSettingsCompleted() {
    return request({
      url: '/' + this.uri + '/check/completed',
      method: 'get'
    });
  }

  validateZoomCredentials(data) {
    return request({
      url: '/' + this.uri + '/validate/zoom',
      method: 'post',
      data: data,
    });
  }

}

export { SettingsResource as default };

export function getSettings() {
  return request({
    url: '/settings/get',
    method: 'get',
  });
}
