import request from '@/utils/request';
import Resource from '@/api/resource';

class RegistrationResource extends Resource {
  constructor() {
    super('registration');
  }

  getStatus(resource) {
    return request({
      url: '/' + this.uri + '/status',
      method: 'get',
      params: resource,
    });
  }

  getFilters(resource) {
    return request({
      url: '/' + this.uri + '/filters',
      method: 'get',
      params: resource,
    });
  }

  getTrajeCounter(resource) {
    return request({
      url: '/' + this.uri + '/trajeCounter',
      method: 'get',
      params: resource,
    });
  }

  getEstatistica(resource) {
    return request({
      url: '/' + this.uri + '/estatistica',
      method: 'get',
      params: resource,
    });
  }

  getCounter(resource) {
    return request({
      url: '/' + this.uri + '/counter',
      method: 'get',
      params: resource,
    });
  }

  getTemplates(resource) {
    return request({
      url: '/' + this.uri + '/templates',
      method: 'get',
      params: resource,
    });
  }

  getHistory(resource) {
    return request({
      url: '/' + this.uri + '/history',
      method: 'get',
      params: resource,
    });
  }

  updateStatus(resource) {
    return request({
      url: '/' + this.uri + '/upStatus',
      method: 'post',
      data: resource,
    });
  }

  updateCortejoStatus(resource) {
    return request({
      url: '/' + this.uri + '/upCortejoStatus',
      method: 'post',
      data: resource,
    });
  }

  updateAnswer(resource) {
    return request({
      url: '/' + this.uri + '/upAnswer',
      method: 'post',
      data: resource,
    });
  }

  editAnswer(resource) {
    return request({
      url: '/' + this.uri + '/eAnswer',
      method: 'post',
      data: resource,
    });
  }

  editCortejoAnswer(resource) {
    return request({
      url: '/' + this.uri + '/eCortejoAnswer',
      method: 'post',
      data: resource,
    });
  }

  saveObs(resource) {
    return request({
      url: '/' + this.uri + '/saveObs',
      method: 'post',
      data: resource,
    });
  }

  getQRCode(resource) {
    return request({
      url: '/' + this.uri + '/validateQR',
      method: 'post',
      data: resource,
    });
  }

  saveQRCodeCortejo(resource) {
    return request({
      url: '/' + this.uri + '/saveQRCortejo',
      method: 'post',
      data: resource,
    });
  }

}

export { RegistrationResource as default };
