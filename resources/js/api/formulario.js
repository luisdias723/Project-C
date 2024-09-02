import request from '@/utils/request';
import Resource from '@/api/resource';

class FormularioResource extends Resource {
  constructor() {
    super('formularios');
  }

  changeVisibility(data) {
    return request({
      url: '/' + this.uri + '/visibility',
      method: 'post',
      data: data,
    });
  }

  getForm(id) {
    return request({
      url: '/' + this.uri + '/getForm/' + id,
      method: 'get',
    });
  }

  participants(data) {
    return request({
      url: '/' + this.uri + '/participants',
      method: 'get',
      params: data
    });
  }

  getCount() {
    return request({
      url: '/' + this.uri + '/getCount',
      method: 'get',
    });
  }

  getActive() {
    return request({
      url: '/' + this.uri + '/getActive',
      method: 'get',
    });
  }

  saveConditions(data) {
    return request({
      url: '/' + this.uri + '/conditions',
      method: 'post',
      data: data,
    });
  }

  getCountries(query) {
    return request({
      url: '/codigos/countries',
      method: 'get',
      params: query,
    });
  }


  getFormCortejo(query) {
    return request({
      url: '/' + this.uri + '/getCortejo',
      method: 'get',
      params: query,
    });
  }

  storeAnswers(data) {
    return request({
      url: '/' + this.uri + '/answers',
      method: 'post',
      data: data,
    });
  }

  storeCortejoAnswers(data) {
    return request({
      url: '/' + this.uri + '/cortejo/answers',
      method: 'post',
      data: data,
    });
  }

  editCortejoAnswers(data) {
    return request({
      url: '/' + this.uri + '/edit/answers',
      method: 'post',
      data: data,
    });
  }

  updateAnswers(data) {
    return request({
      url: '/' + this.uri + '/uAnswers',
      method: 'post',
      data: data,
    });
  }

  editForm(data) {
    return request({
      url: '/' + this.uri + '/editForm',
      method: 'post',
      data: data,
    });
  }

}

export { FormularioResource as default };
