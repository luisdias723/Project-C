import request from '@/utils/request';
import Resource from '@/api/resource';

class QuestionResource extends Resource {
  constructor() {
    super('questions');
  }

  changeVisibility(data) {
    return request({
      url: '/' + this.uri + '/visibility',
      method: 'post',
      data: data,
    });
  }

}

export { QuestionResource as default };
