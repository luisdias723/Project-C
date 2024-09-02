import request from '@/utils/request';
import Resource from '@/api/resource';

class UserResource extends Resource {
  constructor() {
    super('users');
  }

  register(data) {
    return request({
      url: '/' + this.uri + '/register',
      method: 'post',
      data: data,
    });
  }

  getResetLink(data) {
    return request({
      url: '/' + this.uri + '/getReset',
      method: 'post',
      data: data,
    });
  }

  getAllUsers() {
    return request({
      url: '/' + this.uri + '/get/all',
      method: 'get',
    });
  }

  updateActive(data) {
    return request({
      url: '/' + this.uri + '/update/active',
      method: 'post',
      data: data,
    });
  }

  updatePassword(data) {
    return request({
      url: '/' + this.uri + '/reset/password',
      method: 'post',
      data: data,
    });
  }

  changeAvatar(data) {
    return request({
      url: 'users/changeAvatar',
      method: 'post',
      data: data,
    });
  }

  getTotalClients() {
    return request({
      url: 'users/get/total',
      method: 'get',
    });
  }

  coachAndCoachee(data) {
    return request({
      url: 'users/set/coachAndCoachee',
      method: 'post',
      data: data,
    });
  }

}

export { UserResource as default };
