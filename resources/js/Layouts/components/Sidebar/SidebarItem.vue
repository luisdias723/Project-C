<template>
  <div v-if="!item.hidden&&item.children" class="menu-wrapper">
    <template v-if="hasOneShowingChild(item.children,item) && (!onlyOneChild.children||onlyOneChild.noShowingChildren) && !item.alwaysShow">
      <app-link :to="resolvePath(onlyOneChild.path)">
        <!-- single -->
        <el-menu-item :index="resolvePath(onlyOneChild.path)" :class="{'submenu-title-noDropdown':!isNest}">
          <!-- <item v-if="onlyOneChild.meta" :title="onlyOneChild.meta.title" /> -->          
          <!-- <item v-if="onlyOneChild.meta" :icon="onlyOneChild.meta.icon||item.meta.icon" :title="onlyOneChild.meta.title" :collapse="collapse" /> -->
          <!-- <span>{{ onlyOneChild.meta.title }}</span> -->
          <!-- <i :class="onlyOneChild.meta.icon" /> -->
          <template #title>
            {{ generateTitle(onlyOneChild.meta.title) }}
          </template>
        </el-menu-item>
      </app-link>
    </template>

    <!-- group -->
    <el-sub-menu v-else :index="resolvePath(item.path)">
      <template #title>
        <item v-if="item.meta" :title="generateTitle(item.meta.title)" :collapse="collapse" />
      </template>

      <el-menu-item-group>
        <template v-for="child in visibleChildren">
          <sidebar-item
            v-if="child.children && child.children.length > 0"
            :key="child.path"
            :is-nest="true"
            :item="child"
            :base-path="resolvePath(child.path)"
            class="nest-menu"
          />
          <app-link v-else :key="child.name" :to="resolvePath(child.path)">
            <el-menu-item :index="resolvePath(child.path)">
              <!-- <i :class="child.meta.icon" /> -->
              <template #title>
                {{ generateTitle(child.meta.title) }}
              </template>
            </el-menu-item>
          </app-link>
        </template>
      </el-menu-item-group>
    </el-sub-menu>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import path from 'path';
import { isExternal } from '@/utils/validate.js';
import Item from './Item.vue';
import AppLink from './Link.vue';
import { generateTitle } from '@/utils/i18n.js';

export default defineComponent({
  name: 'SidebarItem',
  components: { Item, AppLink },
  props: {
    // route object
    item: {
      type: Object,
      required: true,
    },
    isNest: {
      type: Boolean,
      default: false,
    },
    collapse: {
      type: Boolean,
      default: false,
    },
    basePath: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      onlyOneChild: null,
    };
  },
  computed: {
    visibleChildren() {
      return this.item.children.filter(item => !item.hidden);
    },
  },
  methods: {
    hasOneShowingChild(children, parent) {
      const showingChildren = children.filter(item => {
        if (item.hidden) {
          return false;
        } else {
          // Temp set(will be used if only has one showing child)
          this.onlyOneChild = item;
          return true;
        }
      });

      // When there is only one child router, the child router is displayed by default
      if (showingChildren.length === 1) {
        return true;
      }

      // Show parent if there are no child router to display
      if (showingChildren.length === 0) {
        this.onlyOneChild = { ... parent, path: '', noShowingChildren: true };
        return true;
      }

      return false;
    },
    resolvePath(routePath) {
      if (this.isExternalLink(routePath)) {
        
        return routePath;
      }
      return path.resolve(this.basePath, routePath);
    },
    isExternalLink(routePath) {
      return isExternal(routePath);
    },
    generateTitle,
  },
});
</script>

<style lang="css" scoped>
.el-menu--inline span{
  font-size: 0.9em;
}

.el-menu-item svg{
  margin-right: 11px;
}

.el-menu-item-group__title {
  display: none;
}

.is-active .menu-dot{
  background: #cca13c;
}
</style>

<style>
.menu-dot {
  width: 21px;
  background: #304156;
  height: 10px;
  border-radius: 20px;
  margin-right: 10px;
}
.submenu-title-noDropdown{
  text-transform: uppercase !important;
  letter-spacing: 2px !important;
}
.el-sub-menu__title>span {
  text-transform: uppercase !important;
  letter-spacing: 2px !important;
}
</style>
