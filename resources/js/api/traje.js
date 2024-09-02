import request from '@/utils/request';
import Resource from '@/api/resource';

class TrajeResource extends Resource {
  constructor() {
    super('trajes');
  }

  changeVisibility(data) {
    return request({
      url: '/' + this.uri + '/visibility',
      method: 'post',
      data: data,
    });
  }

}

export { TrajeResource as default };
