<template>
  <el-popover
    placement="bottom-start"
    :width="400"
    trigger="click"
    popper-style="padding: 0px !important"
    @before-enter="showNotifications"
    @before-leave="showNotifications"
  >
    <template #reference>
      <el-badge class="notification-badge item" :hidden="totalNotifications <= 0" :value="totalNotifications" :max="10">
        <el-icon :class="'notifications-bell ' + (clicked ? 'is-active' : '') ">
          <BellFilled />
        </el-icon>
      </el-badge>
    </template>
    <el-card class="box-card">
      <template #header>
        <div class="card-header notifications-header">
          <h3>Notificações</h3>
          <div class="filterNotifications">
            <el-button :text="activeButton !== 'Todas' ? true : false" :type="activeButton === 'Todas' ? 'primary' : ''" round size="small" @click="filterNotifications('all')">
              Todas
            </el-button>
            <el-button :text="activeButton !== 'NLidas' ? true : false" :type="activeButton === 'NLidas' ? 'primary' : ''" round size="small" @click="filterNotifications('unseen')">
              Não lidas
            </el-button>
            <el-button :text="activeButton !== 'Lidas' ? true : false" :type="activeButton === 'Lidas' ? 'primary' : ''" round size="small" @click="filterNotifications('seen')">
              Lidas
            </el-button>
          </div>
        </div>
      </template>
      <div v-if="(notifications.length > 0 && !loadingNotifications) || settings.completed === 0" v-infinite-scroll="loadMoreNotifications" :infinite-scroll-disabled="disableScroll">
        <!-- <div v-if="settings.completed === 0" class="notification-item missing-setting"> -->
          <!-- <div>
            <el-icon><WarningFilled /></el-icon>
          </div>
          <div>
            <a :href="settings.url" target="_blank">{{ settings.description }}</a>
          </div>
        </div> -->

        <div v-if="loadingFilter" style="margin-top: 10px">
          <el-skeleton :rows="5" animated />
        </div>
            
        <el-scrollbar height="350px">
          <div v-for="(notification, idx) in notifications" :key="idx" shadow="always">
            <!-- <div :class="'notification-item ' + (!notification.seen ? 'unread' : '')">
              <a :href="notification.url" target="_blank">{{ notification.description }}</a>
              <a :href="notification.url" target="_blank"><small>{{ notification.created_at }}</small></a>
            </div> -->
            <div :class="'anotherclass notification-item ' + (!notification.seen ? 'unread' : '')">
              <el-row :gutter="20">
                <el-col :span="22">
                  <div class="notificationText">
                    <a :href="notification.url" target="_blank" @click="setReadOrUnread(idx, notification)">{{ notification.description }}</a>
                    <a :href="notification.url" target="_blank" @click="setReadOrUnread(idx, notification)"><small>{{ notification.created_at }}</small></a>
                  </div>
                </el-col>
                <el-col class="alignButton" :span="2">
                  <el-tooltip class="box-item" effect="dark" placement="bottom-end">
                    <template #content>
                      <span>{{ notification.seen ? 'Marcar como não lida' : 'Marcar como lida' }}</span>
                    </template>
                    <el-button :class="'unreadButton ' + (notification.seen ? 'setBackground' : '')" type="warning" circle @click="setReadOrUnread(idx, notification)" />
                  </el-tooltip>
                </el-col>
              </el-row>
            </div>
          </div>
        </el-scrollbar>
      </div>
      <div v-if="notifications.length <= 0 && !loadingNotifications && settings.completed === 1" class="noNotifications">
        <span>Sem notificações</span>
      </div>
    </el-card>
  </el-popover>
</template>

<script>

import NotificationResource from '@/api/notifications';
import SettingsResource from '@/api/settings';

import { BellFilled, WarningFilled } from '@element-plus/icons-vue';

const notificationResource = new NotificationResource();
const settingsResource = new SettingsResource();

export default {
  name: 'NotificationBadge',
  components: {
    BellFilled,
    WarningFilled,
  },
  props: {
  },
  data() {
    return {
      clicked: false,
      totalNotifications: 0,
      activeButton: 'Todas',
      notifications: [],
      loadingNotifications: false,
      loadingOldNotifications: false,
      settings: {
        url: '',
        seen: 1,
        description: '',
        completed: 0,
      },
      query: {
        page: 1,
        limit: 10
      },
      disableScroll: false,
      loadingFilter: false,
    };
  },
  created(){
    this.getNotifications();
    this.getTotalNotifications();
    // this.checkSettings();
  },
  updated() {
    this.getNotifications();
    this.getTotalNotifications();
    // this.checkSettings();
  },
  methods: {
        
    // setNotificationsAsRead() {
    //   this.query.page = 1;
    //   this.clicked = false;
    //   notificationResource.setAllRead().then(response => {
    //     if (response > 0) {
    //       this.totalNotifications = 0;
    //     }
    //   }).catch(error => {
    //     console.log(error);
    //   });
    // },
    showNotifications() {
      this.clicked = (this.clicked === false) ? true : false;
      console.log(this.settings);
    },
    filterNotifications(filter) {
      this.notifications = [];
      this.loadingFilter = true;
      this.query.page = 1;
      this.loadingNotifications = true;
      var activeButtonObject = { 'all': 'Todas', 'unseen': 'NLidas', 'seen': 'Lidas' };
      var filterObject = { 'all': '', 'unseen': 0, 'seen': 1 };
      this.activeButton = activeButtonObject[filter];
      this.query.seen = filterObject[filter];
      notificationResource.list(this.query).then( response => {
        this.loadingFilter = false;
        this.notifications = response.data;
        // this.totalNotifications = response.meta.total;
        this.loadingNotifications = false;
      }
      ).catch(error => {
        console.log(error);
        this.loadingFilter = false;
        this.loadingNotifications = false;
      });
      this.getTotalNotifications();
    },
    async loadMoreNotifications() {
      this.disableScroll = true;
      if ((this.query.limit * this.query.page) === this.notifications.length ) {
        this.loadingOldNotifications = true;
        this.query.page += 1;
        // get more notifications and concat with existing
        const { data } = await notificationResource.list(this.query);
        if (data) {
          this.notifications = [...this.notifications, ...data];
          this.disableScroll = this.query.limit < data.length;
          this.loadingOldNotifications = false;
        }
        this.loadingOldNotifications = false;
      }
    },
    // async checkSettings() {
    //   const response = await settingsResource.checkSettingsCompleted();
    //   this.settings = response;
    //   this.$store.dispatch('settings/getInfo');
    // },
    getNotifications() {
      this.loadingNotifications = true;
      notificationResource.list(this.query).then( response => {
        this.notifications = response.data;
        this.totalNotifications = response.total;
        this.loadingNotifications = false;
      }
      ).catch(error => {
        console.log(error);
        this.loadingNotifications = false;
      });
    },
    getTotalNotifications() {
      notificationResource.getTotalUnseen().then( response => {
        this.totalNotifications = response;
      }
      ).catch(error => {
        console.log(error);
      });
    },
    setReadOrUnread(index, notification) {
      notificationResource.setReadOrUnread(notification).then(response => {
        if (response && response.data) {
          this.notifications[index] = response.data;
          this.totalNotifications = response.data.seen === 0 ? this.totalNotifications + 1 : this.totalNotifications - 1;
        }
      }).catch(error => {
        console.log(error);
      });
    },
  }
};
</script>
<style scoped>
.unreadButton{
  padding: 0px !important;
  height: 10px !important;
  width: 10px !important;
  float: right !important;
}
.anotherclass{
  display: block !important;
}
.notificationText {
  display: grid !important;
}
.alignButton {
  position: absolute !important;
  right: 5px !important;
  top: 30% !important;
}
.setBackground {
  background: white !important;
}
</style>