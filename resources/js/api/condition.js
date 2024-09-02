import request from '@/utils/request';
import Resource from '@/api/resource';

class ConditionResource extends Resource {
  constructor() {
    super('conditions');
  }

  getConditions(data) {
    return request({
      url: '/' + this.uri + '/getAll',
      method: 'post',
      data: data,
    });
  }

}

export { ConditionResource as default };
