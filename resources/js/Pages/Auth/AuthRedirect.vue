<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'AuthRedirect',
  created() {
    const hash = window.location.search.slice(1);
    if (window.localStorage) {
      window.localStorage.setItem('x-admin-oauth-code', hash);
      window.close();
    }
  },
  render: function(h) {
    return h(); // avoid warning message
  },
});
</script>
