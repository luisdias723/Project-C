import request from '@/utils/request';
import Resource from '@/api/resource';

class QuadroResource extends Resource {
  constructor() {
    super('quadros');
  }

  changeVisibility(data) {
    return request({
      url: '/' + this.uri + '/visibility',
      method: 'post',
      data: data,
    });
  }

}

export { QuadroResource as default };
