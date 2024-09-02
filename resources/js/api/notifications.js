import request from '@/utils/request';
import Resource from '@/api/resource';

class NotificationResource extends Resource {
  constructor() {
    super('notifications');
  }

  setAllRead(data) {
    return request({
      url: '/' + this.uri + '/set/seen',
      method: 'post',
      data: data,
    });
  }

  getTotalUnseen() {
    return request({
      url: '/' + this.uri + '/get/totalUnseen',
      method: 'get',
    });
  }

  setReadOrUnread(data) {
    return request({
      url: '/' + this.uri + '/set/readorunread',
      method: 'post',
      data: data,
    });
  }


}

export { NotificationResource as default };
