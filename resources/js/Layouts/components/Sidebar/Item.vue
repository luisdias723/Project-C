<script>
import { h, defineComponent } from 'vue';
export default defineComponent({
  name: 'MenuItem',
  functional: true,
  props: {
    icon: {
      type: String,
      default: '',
    },
    title: {
      type: String,
      default: '',
    },
    collapse: {
      type: Boolean,
      default: false,
    },
  },
  render() {
    // const { icon, title } = context.props;
    // const { title } = context.props;
    // const vnodes = h('span');
    // if (icon) {
    //   vnodes.push('<svg-icon icon-class={(icon)}/>');
    // }
    // <span slot='title'>" + this.title + "</span>
    if (this.title && !this.collapse) {
      if (this.icon) {
        return h('div', { style: 'display: contents' }, [h('i', { class: this.icon, style: 'margin-right: 10px' }), h('span', { slot: 'title'}, this.title)]);
      }else {
        return h('span', { slot: 'title'}, this.title);
      }
    } else {
      if (this.icon) {
        return h('div', { style: 'display: contents' }, [h('i', { class: this.icon, style: 'margin: auto' })]);
      }else {
        return h('span', { slot: 'title'});
      }
    }
    // return vnodes;
  },
});
</script>
